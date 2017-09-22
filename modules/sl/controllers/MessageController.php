<?php

namespace app\modules\sl\controllers;

use app\modules\sl\models\SlTaskScheduleCrontabAbnormal;

use yii\web\Response;
use yii\helpers\Json;
use Yii;

/**
 * Message controller for the `sl` module
 */
class MessageController extends \yii\web\Controller
{
	public function actionAbnormal()
    {
        if(Yii::$app->request->isGet)
        {
            return $this->render('abnormal');
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
