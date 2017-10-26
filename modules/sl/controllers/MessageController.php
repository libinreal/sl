<?php

namespace app\modules\sl\controllers;

use app\models\sl\SlTaskScheduleCrontabAbnormal;

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

            foreach ($data as &$d) 
            {
            	$d['add_time'] = date('Y-m-d H:i:s', $d['add_time']);
            }
            unset($d);

            /*$commandQuery = clone $abnormalQuery;
            echo $commandQuery->createCommand()->getRawSql();exit;*/

             return  [
                    'code'=>'0',
                    'msg'=>'ok',
                    'data'=>[ 'total' => $totals, 'rows' => $data]
                    ];
        }
    }

    public function actionUpdateAbnormal()
    {
    	if(Yii::$app->request->isPost)
        {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $post = Yii::$app->request->post();

            $defaultRet = [
                    'code' => '-1',
                    'msg' => 'Request data error'
                ];

            if(!empty($post) && !empty($post['id']))
            {
                $abnormalModel = SlTaskScheduleCrontabAbnormal::findOne($post['id']);
            }
            else
            {
            	return $defaultRet;
            }

            //数据验证失败
            if ( !$abnormalModel->load( $post, '' ) || !$abnormalModel->validate() )
            {
                // var_dump( $abnormalModel->getErrors());exit;
                return $defaultRet;
            }

            $abnormalModel->save();

            return  [
                'code'=>'0',
                'msg'=>'ok'
            ];

        }
    }
}
