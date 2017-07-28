<?php

namespace app\modules\sl\controllers;

use yii\data\ActiveDataProvider;
use Yii;
use app\modules\sl\models\SlTaskSchedule;
use app\modules\sl\models\SlTaskItem;
use app\modules\sl\models\SlGlobalSettings;
use app\modules\sl\models\SlScheduleProductClass;
use app\modules\sl\models\SlScheduleProductBrand;
use app\modules\sl\models\SlScheduleProductClassBrand;
use app\modules\sl\models\SlTaskScheduleCrontab;
use yii\web\Response;
use app\modules\sl\components\SettingHelper;
use yii\helpers\Json;
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
            Yii::$app->response->format = Response::FORMAT_JSON;
            $post = Yii::$app->request->post();

            $pageNo = @$post['pageNo'];
            $pageSize = @$post['pageSize'];

            $scheModel = new SlTaskSchedule();
            $scheQuery = $scheModel->getSearchQuery();

            if(!$scheQuery)
            {
                return ['code'=>'-1', 'msg'=>'Input data invalid'];
            }

            $totals = $scheQuery->count();

            $data = $scheQuery->limit( $pageSize )->offset( ($pageNo - 1) * $pageSize )->asArray()->all();

            /*$commandQuery = clone $scheQuery;
            echo $commandQuery->createCommand()->getRawSql();exit;*/

             return  [
                    'code'=>'0',
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

            $productClassArr = SlScheduleProductClass::find()->orderBy('id')->indexBy('id')->asArray()->all();
            return $this->render('add-schedule', ['pfSettings' => $pfSettings, 'productClassArr' => $productClassArr]);

        }
        else if( Yii::$app->request->isAjax)
        {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $post = Yii::$app->request->post();

            if(!empty($post) && !empty($post['id']))
            {
                $scheModel = SlTaskSchedule::findOne($post['id']);
            }
            else
            {
                $scheModel = new SlTaskSchedule();
            }

            //数据验证失败
            if ( !$scheModel->load( $post, '' ) || !$scheModel->validate() )
            {
                // var_dump( $scheModel->getErrors());exit;
                return [
                    'code' => '-1',
                    'msg' => 'Submit data error',
                    'data' => []
                ];
            }

            $scheModel->save();


            return  [
                    'code'=>'0',
                    'msg'=>'Success',
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
        if(Yii::$app->request->isGet)
        {
            $get = Yii::$app->request->get();

            $scheEditData = SlTaskSchedule::find()->where(['id' => $get['sche_id'] ])->asArray()->one();

            $pfNameArr = Json::decode( $scheEditData['pf_name'] );
            $brandArr = Json::decode( $scheEditData['brand_name'] );
            $classArr = Json::decode( $scheEditData['class_name'] );

            $catArr = Json::decode( $scheEditData['dt_category'] );
            $cookie = Json::decode( $scheEditData['cookie'] );
            $user_agent = Json::decode( $scheEditData['user_agent'] );

            if( !is_array($cookie) || empty( $cookie ) ) $cookie = [];
            if( !is_array($user_agent) || empty( $user_agent ) ) $user_agent = [];

            if( !is_array($pfNameArr) ) $pfNameArr = [];
            if( !is_array($brandArr) ) $brandArr = [];
            if( !is_array($classArr) ) $classArr = [];

            if( !is_array($catArr) ) $catArr = [];

            $scheEditData['pfNameArr'] = $pfNameArr;
            $scheEditData['brandArr'] = $brandArr;
            $scheEditData['classArr'] = $classArr;

            $scheEditData['catArr'] = $catArr;
            $scheEditData['cookie'] = $cookie;
            $scheEditData['user_agent'] = $user_agent;

            $classSelect = SlScheduleProductClass::find()->select('id')->indexBy('id')->where(['in', 'name', $classArr])->asArray()->all();
            $brandSelect = SlScheduleProductBrand::find()->select('id')->indexBy('id')->where(['in', 'name', $brandArr])->asArray()->all();
            $classMap = SlScheduleProductClassBrand::find()
                        ->select(SlScheduleProductClass::tableName().'.id, brand_id,class_id,'.SlScheduleProductBrand::tableName().'.name')
                        ->joinWith('productClass')
                        ->joinWith('productBrand')
                        ->where(['in', SlScheduleProductClass::tableName().'.name', $classArr])
                        ->orderBy(SlScheduleProductClass::tableName().'.id')
                        ->asArray()->all();

            foreach ($classMap as &$c)
            {
                unset($c['productClass']);
                unset($c['productBrand']);
            }

            unset($c);

            $funcElementStr = function(&$_ele, $_ele_key){$_ele = strval($_ele);};

            $classSelectIds = array_keys($classSelect);
            $brandSelectIds = array_keys($brandSelect);

            array_walk( $classSelectIds, $funcElementStr );
            array_walk( $brandSelectIds, $funcElementStr );

            $pfArr = Yii::$app->getModule('sl')->params['PLATFORM_LIST'];
            $pfSettings = SettingHelper::getPfSetting( array_keys( $pfArr ));

            $productClassArr = SlScheduleProductClass::find()->orderBy('id')->indexBy('id')->asArray()->all();

            return $this->render('add-schedule', ['pfSettings' => $pfSettings,
                                                    'productClassArr' => $productClassArr,
                                                    'scheEditData' => $scheEditData,
                                                    'classSelectIds' => $classSelectIds,
                                                    'brandSelectIds' => $brandSelectIds,
                                                    'classMap' => $classMap,
                                                    ]);
        }
        else if(Yii::$app->request->isPost)
        {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $post = Yii::$app->request->post();

            $defaultRet = [
                    'code' => '-1',
                    'msg' => 'Schedule data error',
                    'data' => []
            ];

            if($post && !empty($post['id']))
                $scheModel = SlTaskSchedule::findOne($post['id']);
            else
                return $defaultRet;


            //数据验证失败
            if ( !$scheModel->load( $post, '' ) || !$scheModel->validate() )
            {
                // var_dump( $scheModel->getErrors());exit;
                return [
                    'code' => -1,
                    'msg' => 'Submit data error',
                    'data' => []
                ];
            }

            $scheModel->save();

            /*** 实际任务状态更新 ***/
            if($scheModel->sche_status == SlTaskSchedule::SCHE_STATUS_CLOSE)
            {
                Yii::$app->getModule('sl')
                    ->db
                    ->createCommand('UPDATE '.SlTaskScheduleCrontab::tableName().' SET [[task_status]] = '.SlTaskScheduleCrontab::TASK_STATUS_UNSTARTED.', [[control_status]] = '.SlTaskScheduleCrontab::CONTROL_STOPPED.' WHERE [[sche_id]] = '. $scheModel->id. ' AND [[task_status]] <> '.SlTaskScheduleCrontab::TASK_STATUS_COMPLETED)
                    ->execute();

                Yii::$app->getModule('sl')
                    ->db
                    ->createCommand('UPDATE '.SlTaskItem::tableName().' SET [[task_status]] = '.SlTaskItem::TASK_STATUS_CLOSE.', [[control_status]] = '.SlTaskItem::CONTROL_STOPPED.' WHERE [[sche_id]] = '. $scheModel->id. ' AND [[task_status]] <> '.SlTaskItem::TASK_STATUS_COMPLETE)
                    ->execute();

            }
            else
            {
                Yii::$app->getModule('sl')
                    ->db
                    ->createCommand('UPDATE '.SlTaskScheduleCrontab::tableName().' SET [[task_status]] = '.SlTaskScheduleCrontab::TASK_STATUS_EXECUTING.', [[control_status]] = '.SlTaskScheduleCrontab::CONTROL_STARTED.' WHERE [[sche_id]] = '. $scheModel->id. ' AND [[task_status]] <> '.SlTaskScheduleCrontab::TASK_STATUS_COMPLETED)
                    ->execute();

                Yii::$app->getModule('sl')
                    ->db
                    ->createCommand('UPDATE '.SlTaskItem::tableName().' SET [[task_status]] = '.SlTaskItem::TASK_STATUS_OPEN.', [[control_status]] = '.SlTaskItem::CONTROL_STARTED.' WHERE [[sche_id]] = '. $scheModel->id . ' AND [[task_status]] <> '.SlTaskItem::TASK_STATUS_COMPLETE)
                    ->execute();
            }
            /*** 实际任务状态更新 ***/


           return  [
                    'code'=>'0',
                    'msg'=>'Success',
                    'data'=>[]
                    ];;
        }
    }

    /**
     * 删除计划任务
     * @return
     */
    public function actionRemoveSche()
    {
        $request = Yii::$app->request;

        if($request->isPost)
        {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $id = $request->post('id', '');
            $ret = SlTaskSchedule::findOne($id)->delete();
            return  [
                'code'=> $ret ? '0' : '1',
                'msg'=>'',
                'data'=>[]
            ];
        }
    }

    /**
     * 删除每日任务
     * @return
     */
    public function actionRemoveCrontab()
    {
        $request = Yii::$app->request;

        if($request->isPost)
        {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $id = $request->post('id', '');
            $ret = SlTaskScheduleCrontab::findOne($id)->delete();
            return  [
                'code'=>$ret ? '0' : '1',
                'msg'=>'',
                'data'=>[]
            ];
        }
    }

    /**
     * 子任务以及任务运行状态
     * method: GET
     * @return string
     */
    public function actionTaskItem()
    {
        if(Yii::$app->request->isGet)
        {
            $get = Yii::$app->request->get();

            return $this->render('task-item', ['cron_id' => $get['cron_id']]);
        }
        else if(Yii::$app->request->isPost)
        {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $post = Yii::$app->request->post();

            $pageNo = @$post['pageNo'];
            $pageSize = @$post['pageSize'];

            $taskModel = new SlTaskItem();
            $taskQuery = $taskModel->getSearchQuery();

            if(!$taskQuery)
            {
                return ['code'=>-1, 'msg'=>'Input data invalid'];
            }

            $totals = $taskQuery->count();

            $data = $taskQuery->limit( $pageSize )->offset( ($pageNo - 1) * $pageSize )->asArray()->all();

            foreach ($data as &$d)
            {
                $d['task_time'] = date('Y-m-d H:i:s', $d['task_time']);

                if(!empty($d['complete_time']))
                    $d['complete_time'] = date('Y-m-d H:i:s', $d['complete_time']);
                else
                    $d['complete_time'] = '';
            }
            unset($d);
            /*$commandQuery = clone $scheQuery;
            echo $commandQuery->createCommand()->getRawSql();exit;*/

             return  [
                    'code'=>'0',
                    'msg'=>'ok',
                    'data'=>[ 'total' => $totals, 'rows' => $data]
                    ];
        }
    }

    public function actionTaskScheCrontab()
    {
        if(Yii::$app->request->isGet)
        {
            $get = Yii::$app->request->get();

            return $this->render('task-sche-crontab', ['sche_id' => $get['sche_id']]);
        }
        else if(Yii::$app->request->isPost)
        {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $request = Yii::$app->request;

            $pageNo = $request->post('pageNo', '');
            $pageSize = $request->post('pageSize', '');

            $scheCronModel = new SlTaskScheduleCrontab();
            $scheCronQuery = $scheCronModel->getSearchQuery();

            if(!$scheCronQuery)
            {
                return ['code'=>-1, 'msg'=>'Input data invalid'];
            }

            $totals = $scheCronQuery->count();

            $data = $scheCronQuery
                        ->select('cron.id, cron.name, cron.start_time, cron.complete_time, cron.task_status, cron.control_status, cron.task_progress, cron.sche_id, sche.key_words, sche.dt_category, sche.pf_name, sche.brand_name')
                        ->limit( $pageSize )
                        ->offset( ($pageNo - 1) * $pageSize )
                        ->asArray()
                        ->all();

            foreach ($data as &$d)
            {
                unset($d['schedule']);

                if(!empty($d['complete_time']))
                    $d['complete_time'] = date('Y-m-d H:i:s', $d['complete_time']);
                else
                    $d['complete_time'] = '';
            }
            unset($d);
            /*$commandQuery = clone $scheQuery;
            echo $commandQuery->createCommand()->getRawSql();exit;*/

             return  [
                    'code'=>'0',
                    'msg'=>'ok',
                    'data'=>[ 'total' => $totals, 'rows' => $data]
                    ];
        }
    }

    /**
     * 更新每日任务(停止、启动)
     * method: POST
     * @return string
     */
    public function actionUpdateTaskScheCrontab()
    {
        if(Yii::$app->request->isPost)
        {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $post = Yii::$app->request->post();

            $defaultRet = [
                    'code' => '-1',
                    'msg' => 'Crontab data error',
                    'data' => []
            ];

            if($post && !empty($post['id']))
                $cronModel = SlTaskScheduleCrontab::findOne($post['id']);
            else
                return $defaultRet;

            //数据验证失败
            if ( !$cronModel->load( $post, '' ) || !$cronModel->validate() )
            {
                return $defaultRet;
            }

            $cronModel->save();

            /*** 任务项状态更新 ***/
            if($cronModel->control_status == SlTaskScheduleCrontab::CONTROL_STOPPED)
            {
                Yii::$app->getModule('sl')
                    ->db
                    ->createCommand('UPDATE '.SlTaskItem::tableName().' SET task_status = '.SlTaskItem::TASK_STATUS_CLOSE.', control_status = '.SlTaskItem::CONTROL_STOPPED.' WHERE cron_id = '. $cronModel->id. ' AND task_status <> '.SlTaskItem::TASK_STATUS_COMPLETE)
                    ->execute();
            }
            else
            {
                Yii::$app->getModule('sl')
                    ->db
                    ->createCommand('UPDATE '.SlTaskItem::tableName().' SET task_status = '.SlTaskItem::TASK_STATUS_OPEN.', control_status = '.SlTaskItem::CONTROL_STARTED.' WHERE cron_id = '. $cronModel->id. ' AND task_status <> '.SlTaskItem::TASK_STATUS_COMPLETE)
                    ->execute();
            }
            /*** 任务项状态更新 ***/

            return  [
                    'code'=>'0',
                    'msg'=>'Success',
                    'data'=>[]
                    ];
        }
    }
}
