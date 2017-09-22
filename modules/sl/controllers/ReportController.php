<?php

namespace app\modules\sl\controllers;
use app\modules\sl\models\SlTaskScheduleCrontab;

use yii\web\Response;
use yii\helpers\Json;
use Yii;

/**
 * Report controller for the `sl` module
 */
class ReportController extends \yii\web\Controller
{
	public function actionCrontabData()
    {
        if(Yii::$app->request->isGet)
        {
            $get = Yii::$app->request->get();

            if(!isset($get['data_type']))
            {
                return;
            }

            return $this->render('crontab-'.$get['data_type'].'-data');
        }
        else if(Yii::$app->request->isPost)
        {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $post = Yii::$app->request->post();

            $pageNo = isset($post['pageNo']) ? $post['pageNo'] : 1;
            $pageSize = isset($post['pageSize']) ? $post['pageSize'] : 10;
            $dataType = isset($post['data_type']) ? $post['data_type'] : '';

            //name && start_time_s are both necessary
            $name = isset($post['name']) ? trim($post['name']) : '';
            $taskDate = isset($post['start_time_s']) ? trim($post['start_time_s']) : '';
            $taskDate = preg_replace('/-/', '', $taskDate);

            $defaultResult = [
                                'code' => '-1',
                                'msg' => 'Request Data error'
                            ];

            if(empty($name) || empty($taskDate) || empty($dataType))
            {
                return $defaultResult;
            }

            //get crontab infomation
            $scheCronModel = new SlTaskScheduleCrontab();
            $scheCronQuery = $scheCronModel->getSearchQuery();

            if(!$scheCronQuery)
            {
                return $defaultResult;
            }

            $cronInfo = $scheCronQuery
                        ->select('cron.id, cron.sche_id')
                        ->asArray()
                        ->one();

            if(empty($cronInfo))
            {
                $defaultResult['msg'] = 'Data not found';
                return $defaultResult;
            }

            //table exist
            $crontabDataTable = 'ws_' . $cronInfo['sche_id']. '_'.$taskDate.'_'.$cronInfo['id'];
            $tableCheck = Yii::$app->getModule('sl')->db->createCommand("SHOW TABLES LIKE '". $crontabDataTable . "'" )->queryOne();//检查数据存放表是否存在

            if(!$tableCheck)
            {
                return $defaultResult;
            }

            /*$offset = ($pageNo - 1) * $pageSize;
            $limitStr = $offset . ',' .$pageSize;
            $totals = Yii::$app->getModule('sl')->db
                        ->createCommand('SELECT COUNT(*) FROM '. $crontabDataTable .' GROUP BY [[product_channel]], [[product_brand1]]')
                        ->queryScalar();*/

            $offset = ($pageNo - 1) * $pageSize;
            $lastkey = $offset + $pageSize - 1;

            if($dataType == 'product')
            {
                $dataArr = Yii::$app->getModule('sl')->db
                            ->createCommand('SELECT [[product_channel]] `pf_name`, [[product_brand1]] `brand_name`, COUNT([[product_channel]]) `number` FROM '. $crontabDataTable .' GROUP BY [[product_channel]], [[product_brand1]]')
                            ->queryAll();
            }
            else if($dataType == 'article')
            {
                $dataArr = Yii::$app->getModule('sl')->db
                            ->createCommand('SELECT [[article_channel]] `pf_name`, [[keyword]], COUNT([[article_channel]]) `number` FROM '. $crontabDataTable .' GROUP BY [[article_channel]], [[keyword]]')
                            ->queryAll();
            }

            $totals = count($dataArr);

            $data = array();
            if($totals > 0)
            {
                foreach($dataArr as $k=>$d)
                {
                    if($k >= $offset && $k <= $lastkey )
                    {
                        $data[] = $d;
                    }
                }
            }

             return  [
                    'code'=>'0',
                    'msg'=>'',
                    'data'=>[ 'total' => $totals, 'rows' => $data]
                    ];
        }
    }
}
