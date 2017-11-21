<?php

namespace app\modules\nlp\controllers;
use yii\web\Response;
use app\models\nlp\DictUploadExcelForm;
use yii\web\UploadedFile;
use yii\helpers\ArrayHelper;
use PHPExcel_IOFactory;

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
	    	$dictList = Yii::$app->db->createCommand("SHOW TABLES LIKE 'nlp_dict%'" )->queryOne();//检查数据存放表是否存在
	    	$dictList = (array)$dictList;
	       	return $this->render('index', [ 'dictList' => $dictList,
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

            $totals = Yii::$app->db->createCommand('SELECT COUNT(*) FROM '. $post['dic_name'])->queryScalar();

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
            $data = Yii::$app->db->createCommand('SELECT * FROM '. $post['dic_name'] .' ORDER BY id LIMIT ' . $offset . ', '. $pageSize)->queryAll();

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

            $dictFields = ['word', 'weight', 'tag', 'synonym'];
            $tagFields = ['tag', 'tag_zh', 'parent'];

            $sheetCt = $objPHPExcel->getSheetCount();
            $rowCt = 0;
            $columnCt = 0;

            // ************************************************ setup 1  :insert ***************************************
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
                $isDict = true;
                
                $sheetFields = [];
                $fieldColumnMap = [];

                $insertDictSql = 'INSERT INTO ' . $dictTable . ' (word, weight) VALUES';
                $insertTagSql = 'INSERT INTO ' . $tagTable . ' (tag, tag_zh) VALUES';

                for ($ri = 1;$ri <= $rowCt;$ri++)
                {
                    for ($ci = 'A';$ci <= $columnCt;$ci++)
                    {
                        if( $ri == 1 )//get field name in first row ( $ri == '1')
                        {
                            $sheetFields[] = $worksheet->getCell($ci.'1')->getValue();
                            var_dump($ci.'1', $worksheet->getCell($ci.'1')->getValue());
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
                            // var_dump($dR, $tR, $sheetFields);
                            return [
                                'code'=>'-7',
                                'msg'=>'sheet fields error,check row 1 please',
                                'data'=>''
                            ];
                        }
                    }
                    else//spell insert sql statments
                    {
                        if( $isDict )
                        {
                            $wordV = (string)$worksheet->getCell($fieldColumnMap['word'].$ri)->getValue();
                            $weightV = (float)$worksheet->getCell($fieldColumnMap['weight'].$ri)->getValue();
                            $synonymV = (float)$worksheet->getCell($fieldColumnMap['synonym'].$ri)->getValue();

                            //check empty
                            if(!$wordV)                            
                            {
                                continue;
                            }

                            $insertDictSql .= '(\'' . $wordV . '\', ' . $weightV . '),' ;
                            //insert synonym words
                            $synonymInfo = explode(',', $synonymV);
                            foreach ($synonymInfo as $wordV) 
                            {
                                $insertDictSql .= '(\'' . $wordV . '\', ' . $weightV . '),' ;
                            }
                        }
                        else
                        {
                            $tagV = (string)$worksheet->getCell($fieldColumnMap['tag'].$ri)->getValue();
                            $tagZhV = (string)$worksheet->getCell($fieldColumnMap['tag_zh'].$ri)->getValue();

                            //check empty
                            if(!$tagV)                            
                            {
                                continue;
                            }

                            $insertTagSql .= '(\'' . $tagV . '\', \'' . $tagZhV . '\'),' ;
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
                            ") ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='".$sheetTitleInfo[1]."分词词库';"
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

                    $addResult = Yii::$app->db->createCommand(substr($insertDictSql, 0, -1) . ' ON DUPLICATE KEY UPDATE  `weight` = VALUES(`weight`);')->execute();

                    if($addResult === false)
                    {
                        return [
                                'code'=>'-3',
                                'msg'=>'dict table insert data error',
                                'data'=>''
                            ];
                    }
                }
                else
                {
                    $e = Yii::$app->db->createCommand("SHOW TABLES LIKE '${tagTable}'" )->queryOne();   
                    if(!$e)
                    {

                        $tagTableCreate = Yii::$app->db->createCommand(
                            "CREATE TABLE `". $tagTable ."` (" . 
                              "`id` int(11) unsigned NOT NULL AUTO_INCREMENT," .
                              "`tag` char(30) NOT NULL DEFAULT '' COMMENT '标签'," .
                              "`pid` int(11) unsigned NOT NULL DEFAULT '0'," .
                              "`tag_zh` char(100) NOT NULL DEFAULT '' COMMENT '标签中文'," .
                              "PRIMARY KEY (`id`)," .
                              "UNIQUE KEY `tag_".$sheetTitleInfo[0]."_tag` (`tag`)," .
                              "UNIQUE KEY `tag_".$sheetTitleInfo[0]."_tag_zh` (`tag_zh`)" .
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
                    $addResult = Yii::$app->db->createCommand(substr($insertTagSql, 0, -1) . ' ON DUPLICATE KEY UPDATE  `tag` = VALUES(`tag`), `tag_zh` = VALUES(`tag_zh`);')->execute();

                    if($addResult === false)
                    {
                        return [
                                'code'=>'-3',
                                'msg'=>'tag table insert data error',
                                'data'=>''
                            ];
                    }
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
                $isDict = true;
                
                $sheetFields = [];
                $fieldColumnMap = [];

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

                    $synonymSql = 'INSERT INTO ' . $dictTable . ' (id, prime_id, sysnonym_ids) VALUES ';#update sysnonym_ids
                    for ($ri = 2;$ri <= $rowCt;$ri++)
                    {
                        for ($ci = 'A';$ci <= $columnCt;$ci++)
                        {
                                
                            $wordV = (string)$worksheet->getCell($fieldColumnMap['word'].$ri)->getValue();
                            $synonymV = (string)$worksheet->getCell($fieldColumnMap['synonym'].$ri)->getValue();
                            $primeId = array_search($wordV, $dictIdWord);

                            //check empty
                            if(!$wordV)
                            {
                                continue;
                            }

                            if($synonymV)
                            {
                                $synonymInfo = explode(',', $synonymV);

                                $synonymIds = [];
                                foreach ($synonymInfo as $s) 
                                {
                                    $synonymIds[] = array_search($s, $dictIdWord);
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
                                $synonymSql .= '(' . $primeId . ', ' . $primeId . ', \'\'),';#synonym_ids为空字符串
                            }

                        }
                    }

                    $synonymRet = Yii::$app->db->createCommand(substr($synonymSql, 0, -1) . ' ON DUPLICATE KEY UPDATE  `prime_id` = VALUES(`prime_id`), `sysnonym_ids` = VALUES(`sysnonym_ids`);')->execute();
                    if($synonymRet === false)
                    {
                        return [
                            'code'=>'-17',
                            'msg'=>'synonym_ids of dict table update failed',
                            'data'=>''
                        ];
                    }

                }
                else
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
                    $needUpdate = false;

                    for ($ri = 2;$ri <= $rowCt;$ri++)
                    {
                        for ($ci = 'A';$ci <= $columnCt;$ci++)
                        {

                            $tagV = (string)$worksheet->getCell($fieldColumnMap['tag'].$ri)->getValue();
                            $parentV = (string)$worksheet->getCell($fieldColumnMap['parent'].$ri)->getValue();

                            //check empty
                            if(!$tagV)
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

            // ************************************************ setup 3  tag_id ***************************************
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
                $isDict = true;
                
                $sheetFields = [];
                $fieldColumnMap = [];

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
                            $tagV = (string)$worksheet->getCell($fieldColumnMap['tag'].$ri)->getValue();
                            $tagId = array_search($tagV, $tagIdWord);

                            $tagId = (int)$tagId;//false -> 0 not exists tag_id

                            $tagIdSql .= '(\'' . $wordV . '\', ' . $tagId . '),' ;
                        }
                    }
                }

                if($isTag)
                    continue;

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


       	return $this->render('tag');
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
            $dictName = $get['dic_name'];
            

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