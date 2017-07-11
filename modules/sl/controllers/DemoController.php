<?php

namespace app\modules\sl\controllers;

use yii\data\ActiveDataProvider;
use Yii;
use app\modules\sl\models\SlTaskSchedule;
use app\modules\sl\models\SlGlobalSettings;
use app\modules\sl\models\SlScheduleProductClass;
use app\modules\sl\models\SlScheduleProductBrand;
use app\modules\sl\models\SlScheduleProductClassBrand;
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
     * 更新渠道设置
     * @method POST
     * @return [type] [description]
     */
    public function actionUpdateScheduleSettings()
    {
        if( Yii::$app->request->isAjax)
        {
            $post = Yii::$app->request->post();
            $childrenSettings = SettingHelper::getPfSetting( $post['pk'], '', 1);

            Yii::$app->response->format = Response::FORMAT_JSON;

            if( !empty($childrenSettings))
            {
                $childrenSettingsItem = $childrenSettings[ $post['pk'] ];

                foreach ($childrenSettingsItem as $itemKey => $itemValue)
                {
                   if( isset($post[$itemKey]) )
                   {
                        $settingsModel = SlGlobalSettings::findOne( $itemValue['id'] );
                        $settingsModel->value = $post[$itemKey];
                        $settingsModel->update();
                   }
                }

                return  [
                    'code' => '0',
                    'msg' => 'ok',
                    'data' => []
                ];

            }

            return  [
                'code' => '-1',
                'msg' => 'Settings not found',
                'data' => []
            ];

        }
    }

    /**
     * 获取产品分类下的品牌
     * @return [type] [description]
     */
    public function actionGetProductBrand()
    {
        if( Yii::$app->request->isAjax)
        {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $post = Yii::$app->request->post();
            $q = SlScheduleProductClassBrand::find();

            $q->select([
                SlScheduleProductClassBrand::tableName().'.class_id',
                SlScheduleProductClassBrand::tableName().'.brand_id',
                SlScheduleProductBrand::tableName().'.name',
            ]);
            if(!empty( $post['class_id']))
                $q->where([SlScheduleProductClassBrand::tableName().'.class_id' => $post['class_id']]);

            $items = $q->joinWith('productBrand')
                        ->asArray()
                        ->all();
            /*$commandQuery = clone $q;
            echo $commandQuery->createCommand()->getRawSql();exit;*/



            foreach ($items as &$v) {
                unset($v['productBrand']);
            }
            unset($v);


            return  [
                'code' => '0',
                'msg' => 'ok',
                'data' => $items
            ];
        }
    }

    /**
     * 获取产品类别
     * @return [type] [description]
     */
    public function actionGetProductClass()
    {
        if( Yii::$app->request->isAjax)
        {
            $items = SlScheduleProductClass::find()
                    ->asArray()
                    ->all();
            return  [
                'code' => '0',
                'msg' => 'ok',
                'data' => $items
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

            $productClassArr = SlScheduleProductClass::find()->orderBy('id')->asArray()->all();
            return $this->render('add-schedule', ['pfSettings' => $pfSettings, 'productClassArr' => $productClassArr]);

        }
        else if( Yii::$app->request->isAjax)
        {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $scheModel = new SlTaskSchedule();
            $post = Yii::$app->request->post();


            //数据验证失败
            if ( !$scheModel->load( $post, '' ) || !$scheModel->validate() )
            {
                // var_dump( $scheModel->getErrors());exit;
                return [
                    'code' => -1,
                    'msg' => 'Data not invalid',
                    'data' => []
                ];
            }

            $scheModel->save();


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
