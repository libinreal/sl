<?php

namespace app\modules\sl\controllers;

use yii\data\ActiveDataProvider;
use Yii;
use app\modules\sl\models\SlTaskSchedule;
use yii\web\Response;
use app\modules\sl\components\SettingHelper;

/**
 * Default controller for the `sl` module
 */
class DemoController extends \yii\web\Controller
{
    /**
     * 计划任务
     * method: GET
     * @return string
     */
    public function actionIndex()
    {
        // var_dump(Yii::$app->request->isAjax);
        if(Yii::$app->request->isGet)
        {
            return $this->render('index');
        }
        else if(Yii::$app->request->isPost)
        {
            $post = Yii::$app->request->post();

            $pageNo = @$post['pageNo'];
            $pageSize = @$post['pageSize'];

            $scheModel = new SlTaskSchedule();
            $scheQuery = $scheModel->getSearchQuery();

            if(!$scheQuery)
            {
                return ['code'=>-1, 'msg'=>'Input data invalid'];
            }

            $totals = $scheQuery->count();

            $data = $scheQuery->limit( $pageSize )->offset( ($pageNo - 1) * $pageSize )->asArray()->all();
            // Yii::$app->response->format = Response::FORMAT_JSON;
            Yii::$app->response->format = Response::FORMAT_JSON;

            /*$commandQuery = clone $scheQuery;
            echo $commandQuery->createCommand()->getRawSql();exit;*/

             return  [
                    'code'=>0,
                    'msg'=>'ok',
                    'data'=>[ 'total' => $totals, 'rows' => $data]
                    ];
        }


    }

    /**
     * 新增计划任务
     * method: GET,POST
     * @return string
     */
    public function actionAddSchedule()
    {

        if( Yii::$app->request->isGet )
        {

            $pfArr = Yii::$app->getModule('sl')->params['PLATFORM_LIST'];
            $pfSettings = SettingHelper::getPfSetting( array_keys( $pfArr ));

            return $this->render('add-schedule', ['pfSettings' => $pfSettings]);

        }
        else if( Yii::$app->request->isAjax)
        {
            $scheModel = new SlTaskSchedule();
            //数据验证失败
            if ( !$scheModel->load( Yii::$app->request->queryParams ) || !$scheModel->validate() )
            {
                return $this->render('add-schedule');
            }

            $scheModel->save();

            Yii::$app->response->format = Response::FORMAT_JSON;
            return  [
                    'code'=>0,
                    'msg'=>'ok',
                    'data'=>[]
                    ];
        }

    }

    /**
     * 编辑计划任务
     * method: GET,POST
     * @return string
     */
    public function actionUpdateSchedule()
    {

        return $this->render('update-schedule');
    }

    /**
     * 子任务
     * method: GET
     * @return string
     */
    public function actionTaskItem()
    {
        return $this->render('task-item');
    }

    /**
     * 编辑子任务
     * method: POST
     * @return string
     */
    public function actionUpdateTaskItem()
    {

    }
}
