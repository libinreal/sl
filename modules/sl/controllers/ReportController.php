<?php

namespace app\modules\sl\controllers;

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

            $abnormalModel = new SlTaskScheduleCrontabAbnormal();
            $abnormalQuery = $abnormalModel->getSearchQuery();

            if(!$abnormalQuery)
            {
                return ['code'=>'-1', 'msg'=>'Input data invalid'];
            }

            $totals = $abnormalQuery->count();

            $data = $abnormalQuery->limit( $pageSize )->offset( ($pageNo - 1) * $pageSize )->asArray()->orderBy('[[id]] DESC')->all();

            /*$commandQuery = clone $abnormalQuery;
            echo $commandQuery->createCommand()->getRawSql();exit;*/

             return  [
                    'code'=>'0',
                    'msg'=>'ok',
                    'data'=>[ 'total' => $totals, 'rows' => $data]
                    ];
        }
    }
}
