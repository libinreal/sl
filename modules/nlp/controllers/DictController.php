<?php

namespace app\modules\nlp\controllers;
use yii\web\Response;

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

            $objReader = PHPExcel_IOFactory::createReader('Excel2007');#上传文件只支持xlsx格式
            $objReader->setReadDataOnly(true);
            $objPHPExcel = $objReader->load($dictName);

            foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) 
            {
                echo 'Worksheet - ' , $worksheet->getTitle() , EOL;

                foreach ($worksheet->getRowIterator() as $row) 
                {
                    echo '    Row number - ' , $row->getRowIndex() , EOL;

                    $cellIterator = $row->getCellIterator();
                    $cellIterator->setIterateOnlyExistingCells(false); // Loop all cells, even if it is not set
                    foreach ($cellIterator as $cell) {
                        if (!is_null($cell)) {
                            echo '        Cell - ' , $cell->getCoordinate() , ' - ' , $cell->getCalculatedValue() , EOL;
                        }
                    }
                }
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