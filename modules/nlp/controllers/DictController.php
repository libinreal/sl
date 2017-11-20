<?php

namespace app\modules\nlp\controllers;
use yii\web\Response;
use app\models\nlp\DictUploadExcelForm;
use yii\web\UploadedFile;

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

            $totals = Yii::$app->db->createCommand('SELECT COUNT(*) FROM [['. $post['dic_name'] .']]')->queryScalar();

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
            $data = Yii::$app->db->createCommand('SELECT * FROM [['. $post['dic_name'] .']] ORDER BY id LIMIT ' . $offset . ', '. $pageSize)->queryAll();

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
            $post = Yii::$app->request->post();
            $file = $post['dictFile'];

            //save excel with UploadedFile
            $excelForm = new DictUploadExcelForm();
            $excelForm->excel = UploadedFile::getInstance($excelForm, 'excel');
            if (!$excelForm->upload()) {
                
                return [
                    'code'=>'-1',
                    'msg'=>'File saving error.',
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

            $dictFields = ['word', 'weight', 'is_prime', 'synonym'];
            $tagFields = ['tag', 'tag_zh', 'parent'];

            $sheetCt = $objPHPExcel->getSheetCount();
            $rowCt = 0;
            $columnCt = 0;

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

                $insertDictSql = 'INSERT INTO [[' . $dictTable . ']] (word, weight, is_prime) VALUES';
                $insertTagSql = 'INSERT INTO [[' . $tagTable . ']] (tag, tag_zh) VALUES';

                for ($ri = 2;$ri <= $rowCt;$ri++)
                {
                    for ($ci = 'A';$ci <= $columnCt;$ci++)
                    {
                        if( $ri == 2 )//get field name in first row ( $ri == '2')
                        {
                            $sheetFields[] = $sheet->getCell($ci.'2')->getValue();
                        }
                    }

                    if($ri == 2)//decide which table to insert
                    {
                        $dR = array_diff( $dictFields, $sheetFields );
                        $tR = array_diff( $tagFields, $sheetFields );

                        if(count($dR) == 0 )
                        {
                            $isDict = true;
                            $isTag = false;
                            foreach ($dictFields as $f) 
                            {
                                $fieldColumnMap[$f] = chr(ord('A') + array_search($f, $sheetFields));//[ 'word' =>'A', 'weight'=>'B', 'is_prime' => 'C'…]
                            }
                        }
                        else if(count($tR) == 0)
                        {
                            $isDict = false;
                            $isTag = true;
                            foreach ($tagFields as $f) 
                            {
                                $fieldColumnMap[$f] = chr(ord('A') + array_search($f, $sheetFields));//[ 'word' =>'A', 'weight'=>'B', 'is_prime' => 'C'…]
                            }
                        }
                        else//error sheet fields
                        {
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
                            $wordV = $sheet->getCell($fieldColumnMap['word'].$ri)->getValue();
                            $weightV = (float)$sheet->getCell($fieldColumnMap['weight'].$ri)->getValue();
                            $isPrimeV = (int)$sheet->getCell($fieldColumnMap['is_prime'].$ri)->getValue();

                            $insertDictSql .= '(\'' . $wordV . '\', ' . $weightV . ', ' . $isPrimeV . '),' ;
                        }
                        else
                        {
                            $tagV = $sheet->getCell($fieldColumnMap['tag'].$ri)->getValue();
                            $tagZhV = $sheet->getCell($fieldColumnMap['tag_zh'].$ri)->getValue();

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
                              "`is_prime` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0:非主要1:主要'," .
                              "`synonym_ids` text NOT NULL COMMENT '近义词id集合'," .
                              "PRIMARY KEY (`id`)," .
                              "UNIQUE KEY `nlp_dict_electrical_word` (`word`)" .
                            ") ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='".$sheetTitleInfo[1]."分词词库';"
                        )->execute();//创建词库表

                        if(!$dictTableCreate)
                        {
                            return [
                                'code'=>'-9',
                                'msg'=>'dict table can not be created,insert failed',
                                'data'=>''
                            ];
                        }
                    }

                    Yii::$app->db->createCommand("")->queryOne();
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
                              "UNIQUE KEY `tag_electrical_tag` (`tag`)," .
                              "UNIQUE KEY `tag_electrical_tag_zh` (`tag_zh`)" .
                              "UNIQUE KEY `nlp_dict_electrical_word` (`word`)" .
                            ") ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='".$sheetTitleInfo[1]."分词词性';"
                        )->execute();//创建词性表

                        if(!$tagTableCreate)
                        {
                            return [
                                'code'=>'-9',
                                'msg'=>'tag table can not be created,insert failed',
                                'data'=>''
                            ];
                        }
                    }
                }
            }

            foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) 
            {
                $sheetTitle = $worksheet->getTitle();
                $sheetTitleInfo = explode('-', $sheetTitle);

                //dict table & tag table
                $dictTable = $dictTablePrefix . $sheetTitleInfo[0];
                $tagTable = $tagTablePrefix . $sheetTitleInfo[0];

                

                $insertDictSql = 'INSERT INTO [[' . $dictTable . ']] (word, weight, )'
                $insertTagSql = 'INSERT INTO [[' . $tagTable . ']] (word, weight, )'

                foreach ($worksheet->getRowIterator() as $row) 
                {
                    if( $row->getRowIndex() == 1 )//get table name through field name in first row
                    {
                        foreach ($insert as $key => $value)
                        {
                            # code...
                        }
                    }

                    $cellIterator = $row->getCellIterator();
                    $cellIterator->setIterateOnlyExistingCells(false); // Loop all cells, even if it is not set
                    foreach ($cellIterator as $cell) {
                        if (!is_null($cell)) {
                            echo '        Cell - ' , $cell->getCoordinate() , ' - ' , $cell->getCalculatedValue();
                        }
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
     * 判断Excel字段是否符合mysql
     */
    private function _isFullField($mysql, $excel)
    {
        for()
        {

        }
        return 
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