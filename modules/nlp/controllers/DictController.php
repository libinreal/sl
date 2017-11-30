<?php

namespace app\modules\nlp\controllers;
use yii\web\Response;
use app\models\nlp\DictUploadExcelForm;
use yii\web\UploadedFile;
use yii\helpers\ArrayHelper;
use PHPExcel_IOFactory;
use PHPExcel;

use app\models\sl\SlTaskScheduleCrontab;

use Yii;

/**
 * Default controller for the `nlp` module
 */
class DictController extends \yii\web\Controller
{
    /**
     * 词库管理
     * @return string
     */
    public function actionIndex()
    {
    	if(Yii::$app->request->isGet)
    	{
	    	$dictList = Yii::$app->db->createCommand("SHOW TABLES LIKE 'nlp_dict%'" )->queryAll();//检查数据存放表是否存在
	    	$dictList = (array)$dictList;

            $dictTemp = $dictList;
            $dictList = [];
            foreach ($dictTemp as $t) 
            {
                $tn = (array_values($t))[0];
                if(strpos($tn, 'nlp_dict_tag_') !== false)//remove tag table from the query result
                {
                    continue;
                }
                $dictList[] = $tn;
            }
            

	       	return $this->render('index', [ 'dictList' => $dictList
	       		]);
	    }
	    else if(Yii::$app->request->isPost)
	    {
	    	Yii::$app->response->format = Response::FORMAT_JSON;
	    	$post = Yii::$app->request->post();

            if(empty($post['dic_name']))
            {
                return  [
                    'code'=> '-1',
                    'msg'=> '请选择词库名',
                    'data'=> []
                ];
            }

            $whereStr = ' where 1=1 ';
            if(!empty($post['word']))
            {
                $whereStr .= ' AND l.word like \'%' . trim($post['word']) . '%\'';
            }

            if(!empty($post['tag']))
            {
                $whereStr .= ' AND t.tag like \'%' . trim($post['tag']) . '%\'';
            }

            if(!empty($post['weight_s']))
            {
                $whereStr .= ' AND l.weight >= ' . (float)$post['weight_s'];
            }

            if(!empty($post['weight_e']))
            {
                $whereStr .= ' AND l.weight <= ' . (float)$post['weight_e'];
            }

            $totals = Yii::$app->db->createCommand(
                        'SELECT COUNT(\'l.*\') FROM '. $post['dic_name'] . ' l ' .
                        ' LEFT JOIN '. preg_replace('/nlp_dict_/', 'nlp_dict_tag_', $post['dic_name']) . ' t ON l.tag_id = t.id ' . $whereStr
                        )->queryScalar();

            if($totals === false)
            {
                return  [
                    'code'=> '-3',
                    'msg'=> 'table not exists',
                    'data'=> []
                ];
            }

            $pageNo = isset($post['pageNo']) ? $post['pageNo'] : 1;
            $pageSize = isset($post['pageSize']) ? $post['pageSize'] : 10;
            $offset = ($pageNo - 1) * $pageSize;
            $data = Yii::$app->db->createCommand('SELECT l.id, l.word, l.weight, t.tag, l.synonym_ids FROM '. $post['dic_name'] .' l ' .
                            ' LEFT JOIN '. preg_replace('/nlp_dict_/', 'nlp_dict_tag_', $post['dic_name']) . ' t ON l.tag_id = t.id ' . $whereStr . ' ORDER BY id LIMIT ' . $offset . ', '. $pageSize)->queryAll();

            //query synonyms with synonym_ids in dict table
            $needQuery = false;
            $synonymSql = 'SELECT id, word FROM ' . $post['dic_name'] . ' WHERE id IN (';

            foreach ($data as $d) 
            {
                if(!trim($d['synonym_ids']) )
                {
                    continue;
                }

                $synonymSql .= '' . $d['synonym_ids'] . ',';
                $needQuery = true;
            }

            $synonymRet = [];
            if($needQuery)
            {
                $synonymSql = substr($synonymSql, 0, -1) . ')';
                $synonymRet = Yii::$app->db->createCommand($synonymSql)->queryAll();
            }

            $synonymIdWord = [];
            foreach ($synonymRet as $s)
            {
                $synonymIdWord[$s['id']] = $s['word'];
            }

            foreach ($data as &$d) 
            {
                $d['synonyms'] = '';
                if(!trim($d['synonym_ids']) )
                {
                    continue;
                }

                $idArr = explode(',', $d['synonym_ids']);
                foreach ($idArr as $id)
                {
                    if(isset($synonymIdWord))
                    {
                        $d['synonyms'] .= $synonymIdWord[$id] . ',';
                    }
                }

                if($d['synonyms'])
                    $d['synonyms'] = substr($d['synonyms'], 0, -1);
            }
            unset($d);

	    	return  [
                'code'=>'0',
                'msg'=>'ok',
                'data'=>[ 'total' => $totals, 'rows' => $data]
            ];
	    }
    }

    /**
     * 保存词库到数据库
     *
     */
    public function actionSaveDict()
    {
        if(Yii::$app->request->isPost)
        {   
            Yii::$app->response->format = Response::FORMAT_JSON;
            // ************************************************ save  ***************************************
            $post = Yii::$app->request->post();

            //save excel with UploadedFile
            $excelForm = new DictUploadExcelForm();
            $excelForm->excel = UploadedFile::getInstanceByName('excel');
            if (!$excelForm->upload()) {
                return [
                    'code'=>'-1',
                    'msg'=>$excelForm->getErrors(),
                    'data'=>''
                ];
            }

            $objReader = PHPExcel_IOFactory::createReader('Excel2007');#上传文件只支持xlsx格式
            $objReader->setReadDataOnly(true);
            $objPHPExcel = $objReader->load($excelForm->saveName);

            $dictFields = array();
            $tagFields = array();

            //two kinds table prefixs
            $dictTablePrefix = 'nlp_dict_';
            $tagTablePrefix = 'nlp_dict_tag_';

            $dictFields = ['word', 'weight', 'tag', 'synonym', 'delete'];
            $tagFields = ['tag', 'parent', 'delete'];

            $sheetCt = $objPHPExcel->getSheetCount();
            $rowCt = 0;
            $columnCt = 0;

            // ************************************************ setup 1  :insert & delete ***************************************
            //insert excel data into dict and tag table
            for ($wi = 0;$wi < $sheetCt;$wi++) 
            {
                $worksheet = $objPHPExcel->getSheet($wi);
                $sheetTitle = $worksheet->getTitle();
                $sheetTitleInfo = explode('-', $sheetTitle);

                //dict table & tag table
                $dictTable = $dictTablePrefix . $sheetTitleInfo[0];
                $tagTable = $tagTablePrefix . $sheetTitleInfo[0];

                $rowCt = $worksheet->getHighestRow();
                $columnCt = $worksheet->getHighestColumn();

                //default table name is `nlp_dict_xxx`, otherwise is `nlp_dict_tag_xxx`
                $isTag = false;
                $isDict = false;
                
                $sheetFields = [];
                $fieldColumnMap = [];

                $insertDictSql = 'INSERT INTO ' . $dictTable . ' (word, weight) VALUES';
                $insertTagSql = 'INSERT INTO ' . $tagTable . ' (tag) VALUES';

                $deleteDictSql = 'DELETE FROM ' . $dictTable . ' WHERE word IN(';
                $deleteTagSql = 'DELETE FROM ' . $tagTable . ' WHERE tag IN(';

                $needInsert = false;
                $needDelete = false;

                for ($ri = 1;$ri <= $rowCt;$ri++)
                {
                    for ($ci = 'A';$ci <= $columnCt;$ci++)
                    {
                        if( $ri == 1 )//get field name in first row ( $ri == '1')
                        {
                            $sheetFields[] = $worksheet->getCell($ci.'1')->getValue();
                            // var_dump($ci.'1', $worksheet->getCell($ci.'1')->getValue());
                        }
                    }

                    if($ri == 1)//decide which table to insert
                    {
                        $dR = array_diff( $dictFields, $sheetFields );
                        $tR = array_diff( $tagFields, $sheetFields );

                        if(count($dR) == 0 )
                        {
                            $isDict = true;
                            $isTag = false;
                            foreach ($dictFields as $f) 
                            {
                                $fieldColumnMap[$f] = chr(ord('A') + array_search($f, $sheetFields));//[ 'word' =>'A', 'weight'=>'B']
                            }
                        }
                        else if(count($tR) == 0)
                        {
                            $isDict = false;
                            $isTag = true;
                            foreach ($tagFields as $f) 
                            {
                                $fieldColumnMap[$f] = chr(ord('A') + array_search($f, $sheetFields));//[ 'word' =>'A', 'weight'=>'B']
                            }
                        }
                        else//error sheet fields
                        {
                            break;   
                        }
                    }
                    else//spell insert sql statments
                    {
                        if( $isDict )
                        {
                            $wordV = (string)$worksheet->getCell($fieldColumnMap['word'].$ri)->getValue();
                            $weightV = (float)$worksheet->getCell($fieldColumnMap['weight'].$ri)->getValue();
                            $synonymV = (string)$worksheet->getCell($fieldColumnMap['synonym'].$ri)->getValue();

                            $deleteV = (string)$worksheet->getCell($fieldColumnMap['delete'].$ri)->getValue();

                            //check empty
                            if(!$wordV)                            
                            {
                                continue;
                            }

                            $synonymInfo = explode(',', $synonymV);

                            if($deleteV == 'y')
                            {
                                $deleteDictSql .= '\'' . $wordV . '\',' ;

                                foreach ($synonymInfo as $wordV) 
                                {
                                    if(!trim($wordV))
                                        continue;
                                    $needDelete = true;//delete flag
                                    $deleteDictSql .= '\'' . $wordV . '\',' ;
                                }
                            }
                            else
                            {
                                $tagV = (string)$worksheet->getCell($fieldColumnMap['tag'].$ri)->getValue();//use dict field `tag` to insert into `tag` table

                                if($tagV)
                                {
                                    $insertTagSql .= '(\'' . $tagV . '\'),' ;
                                }

                                $insertDictSql .= '(\'' . $wordV . '\',' . $weightV . '),' ;
                                
                                //insert synonym words

                                foreach ($synonymInfo as $wordV) 
                                {
                                    if(!trim($wordV))
                                        continue;
                                    $needInsert = true;//insert flag
                                    $insertDictSql .= '(\'' . $wordV . '\',' . $weightV . '),' ;
                                }
                            }
                        }
                        else
                        {
                            $tagV = (string)$worksheet->getCell($fieldColumnMap['tag'].$ri)->getValue();
                            $deleteV = (string)$worksheet->getCell($fieldColumnMap['delete'].$ri)->getValue();

                            //check empty
                            if(!$tagV)                            
                            {
                                continue;
                            }

                            if($deleteV == 'y')
                            {
                                $needDelete = true;//delete flag
                                $deleteTagSql .= '\'' . $tagV . '\',' ;
                            }
                            else
                            {
                                $needInsert = true;//insert flag
                                $insertTagSql .= '(\'' . $tagV . '\'),' ;
                            }
                        }
                    }
                }

                //excute insert sql
                //make sure table exists
                if( $isDict )
                {
                    $e = Yii::$app->db->createCommand("SHOW TABLES LIKE '${dictTable}'" )->queryOne();
                    if(!$e)
                    {
                        $dictTableCreate = Yii::$app->db->createCommand(
                            "CREATE TABLE `". $dictTable ."` (" . 
                              "`id` int(10) unsigned NOT NULL AUTO_INCREMENT," .
                              "`word` char(30) NOT NULL DEFAULT ''," .
                              "`weight` float(24,10) unsigned NOT NULL DEFAULT '0.0000000000'," .
                              "`tag_id` int(10) unsigned DEFAULT '0'," .
                              "`prime_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '近义词代表词id'," .
                              "`synonym_ids` text NOT NULL COMMENT '近义词id集合'," .
                              "PRIMARY KEY (`id`)," .
                              "UNIQUE KEY `nlp_dict_" . $sheetTitleInfo[0] ."_word` (`word`)" .
                            ") ENGINE=InnoDB DEFAULT CHARSET=utf8 DEFAULT COLLATE=utf8_bin COMMENT='".$sheetTitleInfo[1]."分词词库';"
                        )->execute();//创建词库表

                        if($dictTableCreate === false)
                        {
                            return [
                                'code'=>'-9',
                                'msg'=>'dict table can not be created,insert failed',
                                'data'=>''
                            ];
                        }
                    }

                    $e = Yii::$app->db->createCommand("SHOW TABLES LIKE '${tagTable}'" )->queryOne();   
                    if(!$e)
                    {

                        $tagTableCreate = Yii::$app->db->createCommand(
                            "CREATE TABLE `". $tagTable ."` (" . 
                              "`id` int(11) unsigned NOT NULL AUTO_INCREMENT," .
                              "`tag` char(30) NOT NULL DEFAULT '' COMMENT '标签'," .
                              "`pid` int(11) unsigned NOT NULL DEFAULT '0'," .
                              "PRIMARY KEY (`id`)," .
                              "UNIQUE KEY `tag_".$sheetTitleInfo[0]."_tag` (`tag`)" .
                            ") ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='".$sheetTitleInfo[1]."分词词性';"
                        )->execute();//创建词性表

                        if($tagTableCreate === false)
                        {
                            // var_dump($e, $tagTableCreate);
                            return [
                                'code'=>'-9',
                                'msg'=>'tag table can not be created,insert failed',
                                'data'=>''
                            ];
                        }
                    }
                    
                    if($needInsert)
                    {
                        $addResult = Yii::$app->db->createCommand(substr($insertDictSql, 0, -1) . ' ON DUPLICATE KEY UPDATE  `weight` = VALUES(`weight`);')->execute();
                        $addTagResult = Yii::$app->db->createCommand(substr($insertTagSql, 0, -1) . ' ON DUPLICATE KEY UPDATE  `tag` = VALUES(`tag`);')->execute();
                        // echo substr($insertDictSql, 0, -1) . ' ON DUPLICATE KEY UPDATE  `weight` = VALUES(`weight`)';exit;
                        if($addResult === false || $addTagResult === false)
                        {
                            return [
                                    'code'=>'-3',
                                    'msg'=>'dict table insert data error',
                                    'data'=>''
                                ];
                        }
                    }
                    if($needDelete)
                    {
                        $deleteResult = Yii::$app->db->createCommand(substr($deleteDictSql, 0, -1) . ');')->execute();
                        // echo substr($insertDictSql, 0, -1) . ' ON DUPLICATE KEY UPDATE  `weight` = VALUES(`weight`)';exit;
                        if($deleteResult === false)
                        {
                            return [
                                    'code'=>'-3',
                                    'msg'=>'dict table delete data error',
                                    'data'=>''
                                ];
                        }
                    }
                }
                else if($isTag)
                {
                    $e = Yii::$app->db->createCommand("SHOW TABLES LIKE '${tagTable}'" )->queryOne();   
                    if(!$e)
                    {

                        $tagTableCreate = Yii::$app->db->createCommand(
                            "CREATE TABLE `". $tagTable ."` (" . 
                              "`id` int(11) unsigned NOT NULL AUTO_INCREMENT," .
                              "`tag` char(30) NOT NULL DEFAULT '' COMMENT '标签'," .
                              "`pid` int(11) unsigned NOT NULL DEFAULT '0'," .
                              "PRIMARY KEY (`id`)," .
                              "UNIQUE KEY `tag_".$sheetTitleInfo[0]."_tag` (`tag`)" .
                            ") ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='".$sheetTitleInfo[1]."分词词性';"
                        )->execute();//创建词性表

                        if($tagTableCreate === false)
                        {
                            // var_dump($e, $tagTableCreate);
                            return [
                                'code'=>'-9',
                                'msg'=>'tag table can not be created,insert failed',
                                'data'=>''
                            ];
                        }
                    }

                    if($needInsert)
                    {
                        $addResult = Yii::$app->db->createCommand(substr($insertTagSql, 0, -1) . ' ON DUPLICATE KEY UPDATE  `tag` = VALUES(`tag`);')->execute();

                        if($addResult === false)
                        {
                            return [
                                    'code'=>'-3',
                                    'msg'=>'tag table insert data error',
                                    'data'=>''
                                ];
                        }

                    }
                    if($needDelete)
                    {
                        $deleteResult = Yii::$app->db->createCommand(substr($deleteTagSql, 0, -1) . ');')->execute();

                        if($deleteResult === false)
                        {
                            return [
                                    'code'=>'-3',
                                    'msg'=>'tag table delete data error',
                                    'data'=>''
                                ];
                        }
                        
                    }
                    // test
                    // return['code'=>'37','msg'=>$insertTagSql. '   '.$deleteTagSql, 'data'=>''];
                }
            }
            // ************************************************ setup 2  update relation ***************************************
            //insert excel data into dict and tag table
            for ($wi = 0;$wi < $sheetCt;$wi++) 
            {
                $worksheet = $objPHPExcel->getSheet($wi);
                $sheetTitle = $worksheet->getTitle();
                $sheetTitleInfo = explode('-', $sheetTitle);

                //dict table & tag table
                $dictTable = $dictTablePrefix . $sheetTitleInfo[0];
                $tagTable = $tagTablePrefix . $sheetTitleInfo[0];

                $rowCt = $worksheet->getHighestRow();
                $columnCt = $worksheet->getHighestColumn();

                //default table name is `nlp_dict_xxx`, otherwise is `nlp_dict_tag_xxx`
                $isTag = false;
                $isDict = false;
                
                $sheetFields = [];
                $fieldColumnMap = [];

                $needUpdate = false;

                $idDictSql = 'SELECT id,word FROM ' . $dictTable;
                $idTagSql = 'SELECT id,tag FROM ' . $tagTable;

                for ($ri = 1;$ri <= $rowCt;$ri++)
                {
                    for ($ci = 'A';$ci <= $columnCt;$ci++)
                    {
                        if( $ri == 1 )//get field name in first row ( $ri == '1')
                        {
                            $sheetFields[] = $worksheet->getCell($ci.'1')->getValue();
                        }
                    }

                    if($ri == 1)//decide which table to select
                    {
                        $dR = array_diff( $dictFields, $sheetFields );
                        $tR = array_diff( $tagFields, $sheetFields );

                        if(count($dR) == 0 )
                        {
                            $isDict = true;
                            $isTag = false;
                            foreach ($dictFields as $f) 
                            {
                                $fieldColumnMap[$f] = chr(ord('A') + array_search($f, $sheetFields));//[ 'word' =>'A', 'weight'=>'B']
                            }
                        }
                        else if(count($tR) == 0)
                        {
                            $isDict = false;
                            $isTag = true;
                            foreach ($tagFields as $f) 
                            {
                                $fieldColumnMap[$f] = chr(ord('A') + array_search($f, $sheetFields));//[ 'word' =>'A', 'weight'=>'B']
                            }
                        }
                       
                    }
                    else
                    {
                        break;
                    }
                }

                //excute select sql; update `prime_id`, `synonym_ids` in `dict` table , `parent_id` in `tag` table
                //make sure table exists
                if( $isDict )
                {
                    $dictIdWord = Yii::$app->db->createCommand($idDictSql)->queryAll();
                    if(!$dictIdWord)
                    {
                        return [
                            'code'=>'-11',
                            'msg'=>'dict table query failed',
                            'data'=>''
                        ];
                    }
                    //['id' => 'word']
                    $newDictIdWord = [];
                    foreach ($dictIdWord as $d) 
                    {
                        $newDictIdWord[$d['id']] = $d['word'];
                    }
                    $dictIdWord = $newDictIdWord;

                    $synonymSql = 'INSERT INTO ' . $dictTable . ' (id, prime_id, synonym_ids) VALUES ';#update synonym_ids
                    for ($ri = 2;$ri <= $rowCt;$ri++)
                    {
                        for ($ci = 'A';$ci <= $columnCt;$ci++)
                        {
                                
                            $wordV = (string)$worksheet->getCell($fieldColumnMap['word'].$ri)->getValue();
                            $synonymV = (string)$worksheet->getCell($fieldColumnMap['synonym'].$ri)->getValue();
                            $deleteV = (string)$worksheet->getCell($fieldColumnMap['delete'].$ri)->getValue();
                            
                            $primeId = array_search($wordV, $dictIdWord);

                            //check empty
                            if(!$wordV || $deleteV == 'y')
                            {
                                continue;
                            }

                            if($synonymV)
                            {
                                $synonymInfo = explode(',', $synonymV);

                                $synonymIds = [ $primeId ];//prime_id self
                                foreach ($synonymInfo as $s) 
                                {
                                    if(!trim($s))
                                        continue;
                                    $synonymIds[] = array_search($s, $dictIdWord);
                                    /*if(!array_search($s, $dictIdWord))
                                        {
                                            var_dump($s);
                                            exit;
                                        }*/
                                }

                                $synonymIdsVal = implode(',', $synonymIds);
                                $synonymSql .= '(' . $primeId . ', ' . $primeId . ', \'' . $synonymIdsVal . '\'),';

                                foreach ($synonymIds as $s) 
                                {
                                    $synonymSql .= '(' . $s . ', ' . $primeId . ', \'' . $synonymIdsVal . '\'),';
                                }
                            }
                            else
                            {
                                $synonymSql .= '(' . $primeId . ', ' . $primeId . ', \'' . $primeId . '\'),';#synonym_ids为空字符串
                            }
                            $needUpdate = true;

                        }
                    }

                    if($needUpdate)
                    {
                        $synonymRet = Yii::$app->db->createCommand(substr($synonymSql, 0, -1) . ' ON DUPLICATE KEY UPDATE  `prime_id` = VALUES(`prime_id`), `synonym_ids` = VALUES(`synonym_ids`);')->execute();
                        if($synonymRet === false)
                        {
                            return [
                                'code'=>'-17',
                                'msg'=>'synonym_ids of dict table update failed',
                                'data'=>''
                            ];
                        }
                    }

                }
                else if($isTag)
                {
                    $tagIdWord = Yii::$app->db->createCommand($idTagSql)->queryAll();   
                    if(!$tagIdWord)
                    {
                        return [
                            'code'=>'-11',
                            'msg'=>'tag table query failed',
                            'data'=>''
                        ];
                    }
                    //['id' => 'tag']
                    $newTagIdWord = [];
                    foreach ($tagIdWord as $t) 
                    {
                        $newTagIdWord[$t['id']] = $t['tag'];
                    }
                    $tagIdWord = $newTagIdWord;

                    $parentTagSql = 'INSERT INTO ' . $tagTable . ' (id, pid) VALUES'; #update pid in `tag` table

                    for ($ri = 2;$ri <= $rowCt;$ri++)
                    {
                        for ($ci = 'A';$ci <= $columnCt;$ci++)
                        {

                            $tagV = (string)$worksheet->getCell($fieldColumnMap['tag'].$ri)->getValue();
                            $parentV = (string)$worksheet->getCell($fieldColumnMap['parent'].$ri)->getValue();
                            $deleteV = (string)$worksheet->getCell($fieldColumnMap['delete'].$ri)->getValue();
                            
                            //check empty
                            if(!$tagV || $deleteV == 'y')
                            {
                                continue;
                            }

                            if($parentV)
                            {
                                $tagId = array_search($tagV, $tagIdWord);
                                $parentId = array_search($parentV, $tagIdWord);
                                
                                if($parentId)
                                {
                                    $needUpdate = true;
                                    $parentTagSql .= '(' . $tagId . ',' . $parentId .'),';
                                }
                            }
                        }
                    }

                    if($needUpdate)
                    {
                        $tagRet = Yii::$app->db->createCommand(substr($parentTagSql, 0, -1) . ' ON DUPLICATE KEY UPDATE `pid` = VALUES(`pid`);')->execute();
                        if($tagRet === false)
                        {
                            return [
                                'code'=>'-17',
                                'msg'=>'pid of tag table update failed',
                                'data'=>''
                            ];
                        }
                    }

                }
            }

            // ************************************************ setup 3  dict.tag_id ***************************************
            for ($wi = 0;$wi < $sheetCt;$wi++) 
            {
                $worksheet = $objPHPExcel->getSheet($wi);
                $sheetTitle = $worksheet->getTitle();
                $sheetTitleInfo = explode('-', $sheetTitle);

                //dict table & tag table
                $dictTable = $dictTablePrefix . $sheetTitleInfo[0];
                $tagTable = $tagTablePrefix . $sheetTitleInfo[0];

                $rowCt = $worksheet->getHighestRow();
                $columnCt = $worksheet->getHighestColumn();

                //default table name is `nlp_dict_xxx`, otherwise is `nlp_dict_tag_xxx`
                $isTag = false;
                $isDict = false;
                
                $sheetFields = [];
                $fieldColumnMap = [];

                $needUpdate = false;

                for ($ri = 1;$ri <= $rowCt;$ri++)
                {
                    for ($ci = 'A';$ci <= $columnCt;$ci++)
                    {
                        if( $ri == 1 )//get field name in first row ( $ri == '1')
                        {
                            $sheetFields[] = $worksheet->getCell($ci.'1')->getValue();
                        }
                    }

                    if($ri == 1)//decide which table to insert
                    {
                        $dR = array_diff( $dictFields, $sheetFields );
                        $tR = array_diff( $tagFields, $sheetFields );

                        if(count($dR) == 0 )
                        {
                            $isDict = true;
                            $isTag = false;
                            foreach ($dictFields as $f) 
                            {
                                $fieldColumnMap[$f] = chr(ord('A') + array_search($f, $sheetFields));//[ 'word' =>'A', 'weight'=>'B']
                            }

                            $tagIdWord = Yii::$app->db->createCommand('SELECT id, tag FROM ' . $tagTable)->queryAll();
                            if(!$tagIdWord)
                            {
                                return [
                                    'code'=>'-11',
                                    'msg'=>'tag table query failed',
                                    'data'=>''
                                ];
                            }
                            //['id' => 'tag']
                            $newTagIdWord = [];
                            foreach ($tagIdWord as $t) 
                            {
                                $newTagIdWord[$t['id']] = $t['tag'];
                            }
                            $tagIdWord = $newTagIdWord;

                            $tagIdSql = 'INSERT INTO ' . $dictTable . ' (word, tag_id) VALUES';//update tag_id in dict table
                        }
                        else if(count($tR) == 0)//skip tag sheet
                        {
                            $isDict = false;
                            $isTag = true;
                        }
                    }
                    else//spell insert sql statments
                    {
                        if( $isDict )
                        {
                            $wordV = (string)$worksheet->getCell($fieldColumnMap['word'].$ri)->getValue();
                            $synonymV = (string)$worksheet->getCell($fieldColumnMap['synonym'].$ri)->getValue();
                            $deleteV = (string)$worksheet->getCell($fieldColumnMap['delete'].$ri)->getValue();

                            //check empty
                            if(!$wordV || $deleteV == 'y')                            
                            {
                                continue;
                            }

                            $tagV = (string)$worksheet->getCell($fieldColumnMap['tag'].$ri)->getValue();
                            $tagId = array_search($tagV, $tagIdWord);

                            $tagId = (int)$tagId;//false -> 0 not exists tag_id

                            $tagIdSql .= '(\'' . $wordV . '\', ' . $tagId . '),' ;

                            //insert synonym tag_id
                            $synonymInfo = explode(',', $synonymV);

                            foreach ($synonymInfo as $wordV) 
                            {
                                if(!trim($wordV))
                                        continue;
                                $tagIdSql .= '(\'' . $wordV . '\', ' . $tagId . '),' ;
                            }
                            $needUpdate = true;
                        }
                    }
                }

                if(!$isDict)
                    continue;

                if($needUpdate)
                {
                    //execute tag_id update sql statement
                    $updateTagIdRet = Yii::$app->db->createCommand(substr($tagIdSql, 0, -1) . ' ON DUPLICATE KEY UPDATE `tag_id` = VALUES(`tag_id`);' )->execute();
                    
                    if($updateTagIdRet === false)
                    {
                        return [
                            'code'=>'-21',
                            'msg'=>'tag_id in dict table update failed',
                            'data'=>''
                        ];
                    }
                }
            }

            return [
                'code'=>'0',
                'msg'=>'ok',
                'data'=>''
            ];
        }
    }

    /**
     * 词性管理
     * @return string
     */
    public function actionTag()
    {

        if(Yii::$app->request->isGet)
        {
            $dictList = Yii::$app->db->createCommand("SHOW TABLES LIKE 'nlp_dict%'" )->queryAll();//检查数据存放表是否存在
            $dictList = (array)$dictList;

            $dictTemp = $dictList;
            $dictList = [];
            foreach ($dictTemp as $t) 
            {
                $tn = (array_values($t))[0];
                if(strpos($tn, 'nlp_dict_tag_') === false)//remove tag table from the query result
                {
                    continue;
                }
                $dictList[] = $tn;
            }
            

            return $this->render('tag', [ 'dictList' => $dictList
                ]);
        }
        else if(Yii::$app->request->isPost)
        {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $post = Yii::$app->request->post();

            if(empty($post['dic_name']))
            {
                return  [
                    'code'=> '-1',
                    'msg'=> '请选择词性名',
                    'data'=> []
                ];
            }

            $whereStr = ' where 1=1 ';

            if(!empty($post['tag']))
            {
                $whereStr .= ' AND l.tag like \'%' . trim($post['tag']) . '%\'';
            }

            $totals = Yii::$app->db->createCommand(
                        'SELECT COUNT(\'l.*\') FROM '. $post['dic_name'] .' l ' . $whereStr
                        )->queryScalar();

            if($totals === false)
            {
                return  [
                    'code'=> '-3',
                    'msg'=> 'table not exists',
                    'data'=> []
                ];
            }

            $pageNo = isset($post['pageNo']) ? $post['pageNo'] : 1;
            $pageSize = isset($post['pageSize']) ? $post['pageSize'] : 10;
            $offset = ($pageNo - 1) * $pageSize;
            $data = Yii::$app->db->createCommand('SELECT l.id, l.tag, r.tag parent FROM '. $post['dic_name'] .' l ' .
                            ' LEFT JOIN '. $post['dic_name'] . ' r ON l.pid = r.id ' . $whereStr . ' ORDER BY id LIMIT ' . $offset . ', '. $pageSize)->queryAll();

            return  [
                'code'=>'0',
                'msg'=>'ok',
                'data'=>[ 'total' => $totals, 'rows' => $data]
            ];
        }
    }

    /**
     * 以.xlsx格式输出unknown分词文件
     *
     */
    public function actionExportUnknown()
    {
        if(Yii::$app->request->isGet)
        {
            $get = Yii::$app->request->get();
            $start_date = isset($get['start_date']) ? (string)$get['start_date'] : '';
            $name = isset($get['name']) ? (string)$get['name'] : '';

            $offset = isset($get['o']) ? (int)$get['o'] : 0;//$get['o']
            $limit = isset($get['l']) ? (int)$get['l'] : 100;

            if($offset < 0 )
            {
                $offset = 0;
            }

            if($limit <= 0 )
            {
                $limit = 100;
            }
            if(!$start_date || !$name)
                echo '日期和名称未指定';

            $create_time_start = strtotime($start_date);
            $create_time_end = $create_time_start + 3600 * 24;

            $q = SlTaskScheduleCrontab::find();

            $q->select('id, sche_id,start_time')
                ->where('create_time >= :create_time_start and create_time <= :create_time_end', [':create_time_start' => $create_time_start, ':create_time_end' => $create_time_end])
                ->andWhere('name = :name', [':name' => $name]);
            
            $crontabData = $q->asArray()->limit(1)->one();
            $q = null;

            if( $crontabData )
            {
                $start_date_ret = preg_replace('/-/', '', substr($crontabData['start_time'], 0, 10));
                $segTable = 'nlp_seg_' . $crontabData['sche_id']. '_'.$start_date_ret.'_'.$crontabData['id'];//分词结果表
                $wsTable = 'ws_' . $crontabData['sche_id']. '_'.$start_date_ret.'_'.$crontabData['id'];//商品表

                $tableCheck = Yii::$app->db->createCommand("SHOW TABLES LIKE '". $segTable . "'" )->queryOne();//检查数据存放表是否存在

                //data source not exists , uncompleted
                if(!$tableCheck)
                    return 3;

                $ret = Yii::$app->db->createCommand('SELECT s.code, s.word, s.tag, w.product_title FROM ' . $segTable . ' s ' .
                                            'LEFT JOIN ' . $wsTable . ' w ON s.id = w.id LIMIT '. $offset . ',' . $limit
                                            )->queryAll();

                $fileName = $name.''.$start_date_ret.' '.$offset.'-'.$limit;//excel info
                $title = $crontabData['sche_id']. '_'.$start_date_ret.'_'.$crontabData['id'];

                $objPHPExcel = new PHPExcel();
                $objPHPExcel->getProperties()->setCreator('3tichina') //创建人
                ->setLastModifiedBy('3tichina') //最后修改人
                ->setTitle($title) //标题
                ->setSubject($title) //题目
                ->setDescription($title) //描述
                ->setKeywords($title) //关键字
                ->setCategory($title); //种类

                $objWorkSheet = $objPHPExcel->setActiveSheetIndex(0);
                //宽高

                $objWorkSheet->getColumnDimension('A')->setWidth(21);

                $objWorkSheet->getColumnDimension('B')->setWidth(21);
                $objWorkSheet->getColumnDimension('C')->setWidth(21);
                $objWorkSheet->getColumnDimension('D')->setWidth(300);

                //字段名
                $objWorkSheet->setCellValue('A1', 'code');
                $objWorkSheet->setCellValue('B1', 'word');
                $objWorkSheet->setCellValue('C1', 'tag');

                $objWorkSheet->setCellValue('D1', 'product_title');

                foreach ($ret as $i=>$r) 
                {
                    $wi = $i + 1;
                    $objWorkSheet->setCellValue('A'.$wi, $r['code']);
                    $objWorkSheet->setCellValue('B'.$wi, $r['word']);
                    $objWorkSheet->setCellValue('C'.$wi, $r['tag']);

                    $objWorkSheet->setCellValue('D'.$wi, $r['product_title']);
                }

                $objWorkSheet->setTitle($title);

                $this->getxlsx($fileName, $objPHPExcel);
            }

        }
        
    }

    /**
     * 输出xlsx文件
     * @param string $filename
     */
    private function getxlsx($filename, $excel) 
    {
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$filename.'.xlsx"');
        header('Cache-Control: max-age=0');
        $writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
        $writer->save('php://output');
        return ;
    }
    
    
    
    /**
     * 输出xls文件
     * @param string $filename
     */
    private function getxls($filename, $excel) 
    {
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'.$filename.'.xls"');
        header('Cache-Control: max-age=0');
        $writer = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
        $writer->save('php://output');
    }

}