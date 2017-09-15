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
use app\modules\sl\models\SlScheduleArticleClass;
use app\modules\sl\models\SlScheduleArticleTag;
use app\modules\sl\models\SlScheduleArticleClassTag;

use yii\web\Response;
use app\modules\sl\components\SettingHelper;
use yii\helpers\Json;
/**
 * Default controller for the `sl` module
 */
class ScheduleController extends \yii\web\Controller
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

            $data = $scheQuery->limit( $pageSize )->offset( ($pageNo - 1) * $pageSize )->asArray()->orderBy('[[id]] DESC')->all();

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
            $q = SlScheduleProductClassBrand::find()->alias('cb');

            $q->select([

                'cb.class_id',
                'cb.brand_id',
                'b.name',
            ]);
            if(!empty( $post['class_id']))
                $q->where(['cb.class_id' => $post['class_id']]);

            $items = $q->joinWith('productBrand')
                        ->asArray()
                        ->all();

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
            $get = Yii::$app->request->get();

            //fileter out the request `data_type` settings
            $getPfSettings = [];
            foreach ($pfSettings as $pfK => $pfV) 
            {
                $data_type = $pfV[$pfK . '_data_type'];
                
                if($data_type == $get['data_type'])
                {
                    $getPfSettings[$pfK] = $pfV;
                }
            }

            if( $get['data_type'] == 'product' )
            {
                $dataClassArr = SlScheduleProductClass::find()->orderBy('id')->indexBy('id')->asArray()->all();
            }
            else if( $get['data_type'] == 'article' )
            {
                $dataClassArr = SlScheduleArticleClass::find()->orderBy('id')->indexBy('id')->asArray()->all();
            }   
                
            $viewName = 'add-' . $get['data_type'] . '-schedule';

            return $this->render($viewName, ['pfSettings' => $getPfSettings, 'dataClassArr' => $dataClassArr]);

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

            if(!empty($post['name']))//edit schedule,delete all the crontab and item on this day
            {
                $cronId = SlTaskScheduleCrontab::find()
                    ->select('id')
                    ->where(['sche_id' => $scheModel->id])
                    ->andWhere(['>', 'create_time', strtotime('today')])
                    ->asArray()
                    ->one();
                //删除任务项
                /*SlTaskItem::find()
                    ->where(['cron_id' => $cronId])
                    ->delete();*/

                //清空然后删除数据存放表
                /*Yii::$app->getModule('sl')->db->createCommand('TRUNCATE ' . 'ws_' . $scheModel->id . '_'. date('Ymd') . '_' . $cronId)->execute();
                Yii::$app->getModule('sl')->db->createCommand('DROP TABLE ' . 'ws_' . $scheModel->id . '_'. date('Ymd') . '_' . $cronId)->execute();*/
                //删除每日任务
                if($cronId)
                {
                    Yii::$app->getModule('sl')->db->createCommand()->delete(SlTaskScheduleCrontab::tableName(), 'id = ' . $cronId['id'])->execute();

                    return  [
                        'code'=>'0',
                        'msg'=>'Success',
                        'data'=>[]
                        ];
                }
            }

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
            $alert_params = Json::decode( $scheEditData['alert_params'] );

            $user_agent = Json::decode( $scheEditData['user_agent'] );
            $keyWords = Json::decode( $scheEditData['key_words'] );

            if( !is_array($cookie) || empty( $cookie ) ) $cookie = [];
            if( !is_array($user_agent) || empty( $user_agent ) ) $user_agent = [];
            if( !is_array($alert_params) || empty( $alert_params ) ) $alert_params = [];

            if( !is_array($pfNameArr) ) $pfNameArr = [];
            if( !is_array($brandArr) ) $brandArr = [];
            if( !is_array($classArr) ) $classArr = [];

            if( !is_array($catArr) ) $catArr = [];
            if( !is_array($keyWords) ) $keyWords = [];

            $scheEditData['pfNameArr'] = $pfNameArr;
            $scheEditData['brandArr'] = $brandArr;
            $scheEditData['classArr'] = $classArr;

            $scheEditData['catArr'] = $catArr;
            $scheEditData['cookie'] = $cookie;
            $scheEditData['user_agent'] = $user_agent;

            $scheEditData['alert_params'] = $alert_params;
            $scheEditData['key_words'] = $keyWords;

            //product classes and brands
            $classSelectIds = [];
            $brandSelectIds = [];
            $classMap = [];
            //article classes and tags
            $kwSelectIds = [];
            $articleClassArr = [];
            $articleTagArr = [];

            $curCategoryMap = [];

            $pfArr = Yii::$app->getModule('sl')->params['PLATFORM_LIST'];
            $pfSettings = SettingHelper::getPfSetting( array_keys( $pfArr ));
            
            //fileter out the request `data_type` settings
            $getPfSettings = [];
            foreach ($pfSettings as $pfK => $pfV) 
            {
                $data_type = $pfV[$pfK . '_data_type'];
                
                if($data_type == $get['data_type'])
                {
                    $getPfSettings[$pfK] = $pfV;
                }
            }

            
            $viewName = 'add-' . $get['data_type'] . '-schedule';
            $funcElementStr = function(&$_ele, $_ele_key){$_ele = strval($_ele);};

            if( $get['data_type'] == 'product' )
            {
                //get class and brand relation
                $dataClassArr = SlScheduleProductClass::find()
                ->alias('c')
                ->joinWith('productBrand')
                ->select('c.id, c.name class_name, cb.brand_id, b.name brand_name')
                ->asArray()
                ->all();

                //get selected classes and brands
                $classSelect = SlScheduleProductClass::find()->select('id')->indexBy('id')->where(['in', 'name', $classArr])->asArray()->all();
                $brandSelect = SlScheduleProductBrand::find()->select('id')->indexBy('id')->where(['in', 'name', $brandArr])->asArray()->all();
                $classMap = SlScheduleProductClassBrand::find()
                            ->alias('cb')
                            ->select('c.id, cb.brand_id, cb.class_id, b.name')
                            ->joinWith('productClass')
                            ->joinWith('productBrand')
                            ->where(['in', 'c.name', $classArr])
                            ->orderBy('c.id')
                            ->asArray()->all();

                foreach ($classMap as &$c)
                {
                    unset($c['productClass']);
                    unset($c['productBrand']);
                }

                unset($c);

                $classSelectIds = array_keys($classSelect);
                $brandSelectIds = array_keys($brandSelect);

                array_walk( $classSelectIds, $funcElementStr );
                array_walk( $brandSelectIds, $funcElementStr );
                
            }
            else if( $get['data_type'] == 'article' )
            {
                $dataClassArr = SlScheduleArticleClass::find()
                ->alias('c')
                ->joinWith('articleTag')
                ->select('c.id, c.name class_name, ct.tag_id, t.name tag_name')
                ->asArray()
                ->all();

                //get tags
                $tagArr = SlScheduleArticleTag::find()->asArray()->all();
                foreach ($tagArr as $w) 
                {
                    if(in_array( $w['name'], $keyWords))
                        $kwSelectIds[] = strval($w['id']);
                    $articleTagArr[$w['id']] = $w['name'];
                }

                //get classes
                foreach ($dataClassArr as $d) 
                {
                    $articleClassArr[$d['id']] = $d['class_name'];
                }

                $classMapArr = SlScheduleArticleClassTag::find()
                    ->alias('ct')
                    ->select('c.id, ct.tag_id, ct.class_id, t.name tag_name, c.name class_name')
                    ->joinWith('articleTag')
                    ->joinWith('articleClass')
                    ->asArray()
                    ->orderBy('c.id ASC')
                    ->all();

                $classMap = [];
                foreach ($classMapArr as $c) 
                {
                    if(!isset($classMap[$c['id']]))
                    {
                        $classMap[$c['id']] = array(
                                                    'name' => $c['class_name'], 
                                                    'tags' => array()
                                            );

                        $curCategoryMap[$c['id']] = array();
                    }

                    $curCategoryMap[$c['id']][] = strval($c['tag_id']);

                    $classMap[$c['id']]['tags'][$c['tag_id']] = array($c['tag_name']);
                }
            } 

            return $this->render($viewName, ['pfSettings' => $getPfSettings,
                                                    'dataClassArr' => $dataClassArr,
                                                    'scheEditData' => $scheEditData,

                                                    //product data
                                                    'classSelectIds' => $classSelectIds,
                                                    'brandSelectIds' => $brandSelectIds,
                                                    
                                                    //article data
                                                    'kwSelectIds' => $kwSelectIds,
                                                    'articleClassArr' => $articleClassArr,
                                                    'articleTagArr' => $articleTagArr,

                                                    'curCategoryMap' => $curCategoryMap,

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
                    ->createCommand('UPDATE '.SlTaskItem::tableName().' SET [[task_status]] = '.SlTaskItem::TASK_STATUS_OPEN.', [[control_status]] = '.SlTaskItem::CONTROL_RESTARTED.' WHERE [[sche_id]] = '. $scheModel->id . ' AND [[task_status]] <> '.SlTaskItem::TASK_STATUS_COMPLETE)
                    ->execute();
            }
            /*** 实际任务状态更新 ***/


           return  [
                    'code'=>'0',
                    'msg'=>'Success',
                    'data'=>[]
                    ];
        }
    }

    /**
     * 获取所有分类下的品牌
     *
     */
    public function actionClassBrandManage()
    {
        if(Yii::$app->request->isPost)
        {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $classBrand = SlScheduleProductClass::find()
                ->alias('c')
                ->joinWith('productBrand')
                ->select('c.id, c.name class_name, cb.brand_id, b.name brand_name')
                ->asArray()
                ->all();
            $brand = SlScheduleProductBrand::find()
                ->asArray()
                ->all();

            $ret['cb'] = $classBrand;
            $ret['b'] = $brand;

            return [
                    'code' => '0',
                    'data' => $ret,
                    'msg' => ''
                ];
        }
    }

    //添加产品分类
    public function actionAddProductClass()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $name = trim(Yii::$app->request->post('n', ''));
        if($name)
        {
            Yii::$app->getModule('sl')->db->createCommand('INSERT IGNORE INTO '.SlScheduleProductClass::tableName(). '([[name]]) VALUES(\''. $name.'\');')->execute();
            $id = Yii::$app->getModule('sl')->db->getLastInsertID();

            if($id)
            {
                return [
                        'code' => '0',
                        'data' => $id,
                        'msg' => ''
                    ];
            }
            else
            {
                return [
                        'code' => '1',
                        'data' => [],
                        'msg' => 'Add Failed'
                    ];
            }
        }

        return [
                'code' => '-1',
                'data' => [],
                'msg' => 'Invalid request data'
            ];
    }

    //添加产品品牌
    public function actionAddProductBrand()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $name = trim(Yii::$app->request->post('n', ''));
        if($name)
        {
            Yii::$app->getModule('sl')->db->createCommand('INSERT IGNORE INTO '.SlScheduleProductBrand::tableName(). '([[name]]) VALUES(\''. $name.'\');')->execute();
            $id = Yii::$app->getModule('sl')->db->getLastInsertID();

            if($id)
            {
                return [
                        'code' => '0',
                        'data' => $id,
                        'msg' => ''
                    ];
            }
            else
            {
                return [
                        'code' => '1',
                        'data' => [],
                        'msg' => 'Add Failed'
                    ];
            }
        }

        return [
                'code' => '-1',
                'data' => [],
                'msg' => 'Invalid request data'
            ];
    }

    /**
     * 获取所有品牌所属的分类
     *
     */
    public function actionBrandClassManage()
    {
        if(Yii::$app->request->isPost)
        {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $brandClass = SlScheduleProductBrand::find()
                ->alias('b')
                ->joinWith('productClass')
                ->select('b.id, b.name brand_name, bc.class_id, c.name class_name')
                ->asArray()
                ->all();

            $class = SlScheduleProductClass::find()
                ->asArray()
                ->all();

            $ret['bc'] = $brandClass;
            $ret['c'] = $class;

            return [
                    'code' => '0',
                    'data' => $ret,
                    'msg' => ''
                ];
        }
    }

    /**
     * 保存品牌关联关系
     */
    public function actionSaveBrandMap()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $brands = Yii::$app->request->post('b', []);
        $brandMap = Yii::$app->request->post('m', []);

        $brandValues = '';
        // $brands = array_values( $brands );

        $newBrandIds = [];
        foreach ($brands as $bid => $brand)
        {
            $brand = trim($brand);
            if(!$brand)
                continue;
            
            $newBrandIds[] = $bid;

            $brandValues .= '(' . $bid . ', \'' . $brand . '\'),';
        }

        $origBrands = SlScheduleProductBrand::find()->select('id')->asArray()->all();
        //Brands and maps to be deleted 
        $delBrandIds = [];
        foreach ($origBrands as $oB) 
        {
            if(!in_array( $oB['id'], $newBrandIds ))
                $delBrandIds[] = $oB['id'];
        }
        //deleted records in brands and maps
        if(!empty( $delBrandIds ))
        {
            SlScheduleProductBrand::deleteAll(['in', 'id', $delBrandIds]);
        }
        SlScheduleProductClassBrand::deleteAll();

        $mapValues = '';
        foreach ($brandMap as $_bid => $_cidArr)
        {
            foreach ($_cidArr as $_cid)
            {
                $mapValues .= '(' . $_bid . ',' . $_cid . '),';
            }
        }

        $bRet = true;
        $mRet = true;

        if(!empty($brandValues))
            $bRet = Yii::$app->getModule('sl')->db->createCommand('INSERT IGNORE INTO '.SlScheduleProductBrand::tableName(). '([[id]], [[name]]) VALUES '. substr($brandValues, 0,-1))->execute();

        if(!empty($mapValues))
            $mRet = Yii::$app->getModule('sl')->db->createCommand('INSERT IGNORE INTO '.SlScheduleProductClassBrand::tableName(). '([[brand_id]], [[class_id]]) VALUES '. substr($mapValues, 0,-1))->execute();

        if($bRet !==false && $mRet !== false)
        {
            return [
                    'code' => '0',
                    'data' => [],
                    'msg' => ''
                ];
        }
        else
        {
            return [
                    'code' => '1',
                    'data' => [],
                    'msg' => ''
                ];
        }
    }

    /**
     * 保存分类关联关系
     */
    public function actionSaveClassMap()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $clses = Yii::$app->request->post('c', []);
        $clsMap = Yii::$app->request->post('m', []);

        $clsValues = '';

        $newClsIds = [];

        foreach ($clses as $cid => $cls)
        {
            $cls = trim($cls);
            if(!$cls)
                continue;

            $newClsIds[] = $cid;

            $clsValues .= '(' . $cid . ', \'' . $cls . '\'),';
        }

        $origClses = SlScheduleProductClass::find()->select('id')->asArray()->all();
        //Classes and maps to be deleted 
        $delClsIds = [];
        foreach ($origClses as $oC) 
        {
            if(!in_array( $oC['id'], $newClsIds ))
                $delClsIds[] = $oC['id'];
        }
        //deleted records in classes and maps
        if(!empty( $delClsIds ))
        {
            SlScheduleProductClass::deleteAll(['in', 'id', $delClsIds]);
        }
        SlScheduleProductClassBrand::deleteAll();

        $mapValues = '';
        foreach ($clsMap as $_cid => $_bidArr)
        {
            foreach ($_bidArr as $_bid)
            {
                $mapValues .= '(' . $_cid . ',' . $_bid . '),';
            }
        }

        $cRet = true;
        $mRet = true;

        if(!empty($clsValues))
            $cRet = Yii::$app->getModule('sl')->db->createCommand('INSERT IGNORE INTO '.SlScheduleProductClass::tableName(). '([[id]], [[name]]) VALUES '. substr($clsValues, 0,-1))->execute();

        if(!empty($mapValues))
            $mRet = Yii::$app->getModule('sl')->db->createCommand('INSERT IGNORE INTO '.SlScheduleProductClassBrand::tableName(). '([[class_id]], [[brand_id]]) VALUES '. substr($mapValues, 0,-1))->execute();

        if($cRet !==false && $mRet !== false)
        {
            return [
                    'code' => '0',
                    'data' => [],
                    'msg' => ''
                ];
        }
        else
        {
            return [
                    'code' => '1',
                    'data' => [],
                    'msg' => ''
                ];
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
            $cronModel = SlTaskScheduleCrontab::findOne($id);
            $cronModel->is_delete = SlTaskScheduleCrontab::DELETED;
            $ret = $cronModel->save();

            return  [
                'code'=>($ret !== false) ? '0' : '1',
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

            $data = $taskQuery->limit( $pageSize )->offset( ($pageNo - 1) * $pageSize )->asArray()->orderBy('[[id]] DESC')->all();

            foreach ($data as &$d)
            {
                $d['task_time'] = date('Y-m-d H:i:s', $d['task_time']);

                if(!empty($d['complete_time']))
                    $d['complete_time'] = date('Y-m-d H:i:s', $d['complete_time']);
                else
                    $d['complete_time'] = '';

                if(!empty($d['act_time']))
                    $d['act_time'] = date('Y-m-d H:i:s', $d['act_time']);
                else
                    $d['act_time'] = '';
            }
            unset($d);

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
                        ->select('cron.id, cron.name, cron.start_time, cron.complete_time, cron.act_time, cron.task_status, cron.control_status, cron.task_progress, cron.sche_id, sche.key_words, sche.dt_category, sche.pf_name, sche.brand_name')
                        ->limit( $pageSize )
                        ->offset( ($pageNo - 1) * $pageSize )
                        ->asArray()
                        ->orderBy('cron.id DESC')
                        ->all();

            foreach ($data as &$d)
            {
                unset($d['schedule']);

                if(!empty($d['complete_time']))
                    $d['complete_time'] = date('Y-m-d H:i:s', $d['complete_time']);
                else
                    $d['complete_time'] = '';

                if(!empty($d['act_time']))
                    $d['act_time'] = date('Y-m-d H:i:s', $d['act_time']);
                else
                    $d['act_time'] = '';
            }
            unset($d);

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
                    ->createCommand('UPDATE '.SlTaskItem::tableName().' SET complete_status = '.SlTaskItem::TASK_STATUS_CLOSE.', control_status = '.SlTaskItem::CONTROL_STOPPED.' WHERE cron_id = '. $cronModel->id. ' AND task_status <> '.SlTaskItem::TASK_STATUS_COMPLETE)
                    ->execute();
            }
            else
            {
                Yii::$app->getModule('sl')
                    ->db
                    ->createCommand('UPDATE '.SlTaskItem::tableName().' SET complete_status = '.SlTaskItem::TASK_STATUS_OPEN.', control_status = '.SlTaskItem::CONTROL_RESTARTED.' WHERE cron_id = '. $cronModel->id. ' AND task_status <> '.SlTaskItem::TASK_STATUS_COMPLETE)
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

    /**
     * 更新任务项状态
     * @return
     */
    public function actionUpdateTaskItem()
    {
        if(Yii::$app->request->isPost)
        {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $post = Yii::$app->request->post();

            $defaultRet = [
                    'code' => '-1',
                    'msg' => 'Invalid request data',
                    'data' => []
            ];

            if($post && !empty($post['id']))
                $itemModel = SlTaskItem::findOne($post['id']);
            else
                return $defaultRet;

            //数据验证失败
            if ( !$itemModel->load( $post, '' ) || !$itemModel->validate() )
            {
                return $defaultRet;
            }

            $itemModel->save();

            return  [
                    'code'=>'0',
                    'msg'=>'Success',
                    'data'=>[]
                    ];
        }
    }

    /**
     * 删除任务项
     * @return
     */
    public function actionRemoveTaskItem()
    {
        $request = Yii::$app->request;

        if($request->isPost)
        {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $id = $request->post('id', '');
            $itemModel = SlTaskItem::findOne($id);
            $itemModel->is_delete = SlTaskItem::DELETED;
            $ret = $itemModel->save();

            return  [
                'code'=>($ret !== false) ? '0' : '1',
                'msg'=>'',
                'data'=>[]
            ];
        }
    }

    /**
     * 获取所有计划爬取的文章分类以及标签
     * @method POST
     */
    public function actionClassTagManage()
    {
        if(Yii::$app->request->isPost)
        {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $classTag = SlScheduleArticleClass::find()
                ->alias('c')
                ->joinWith('articleTag')
                ->select('c.id, c.name class_name, ct.tag_id, t.name tag_name')
                ->asArray()
                ->all();
            $tag = SlScheduleArticleTag::find()
                ->asArray()
                ->all();

            $ret['ct'] = $classTag;
            $ret['t'] = $tag;

            return [
                    'code' => '0',
                    'data' => $ret,
                    'msg' => ''
                ];
        }
    }

    /**
     * 添加计划爬取的文章分类
     * @method POST
     */
    public function actionAddArticleClass()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $name = trim(Yii::$app->request->post('n', ''));
        if($name)
        {
            Yii::$app->getModule('sl')->db->createCommand('INSERT IGNORE INTO '.SlScheduleArticleClass::tableName(). '([[name]]) VALUES(\''. $name.'\');')->execute();
            $id = Yii::$app->getModule('sl')->db->getLastInsertID();

            if($id)
            {
                return [
                        'code' => '0',
                        'data' => $id,
                        'msg' => ''
                    ];
            }
            else
            {
                return [
                        'code' => '1',
                        'data' => [],
                        'msg' => 'Add Failed'
                    ];
            }
        }

        return [
                'code' => '-1',
                'data' => [],
                'msg' => 'Invalid request data'
            ];
    }

    /**
     * 添加计划爬取的文章标签
     * @method POST
     */
    public function actionAddArticleTag()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $name = trim(Yii::$app->request->post('n', ''));
        if($name)
        {
            Yii::$app->getModule('sl')->db->createCommand('INSERT IGNORE INTO '.SlScheduleArticleTag::tableName(). '([[name]]) VALUES(\''. $name.'\');')->execute();
            $id = Yii::$app->getModule('sl')->db->getLastInsertID();

            if($id)
            {
                return [
                        'code' => '0',
                        'data' => $id,
                        'msg' => ''
                    ];
            }
            else
            {
                return [
                        'code' => '1',
                        'data' => [],
                        'msg' => 'Add Failed'
                    ];
            }
        }

        return [
                'code' => '-1',
                'data' => [],
                'msg' => 'Invalid request data'
            ];
    }

    /**
     * 保存爬取文章的分类和标签关系
     * @method POST
     */
    public function actionSaveArticleClassTag()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $tags = Yii::$app->request->post('t', []);
        $clses = Yii::$app->request->post('c', []);
        $clsMap = Yii::$app->request->post('m', []);

        $tagValues = '';
        $clsValues = '';

        $newClsIds = [];
        $newTagIds = [];

        foreach ($tags as $tid => $tag)
        {
            $tag = trim($tag);
            if(!$tag)
                continue;

            $newTagIds[] = $tid;

            $tagValues .= '('. $tid . ', \'' . $tag . '\'),';
        }

        foreach ($clses as $cid => $cls)
        {
            $cls = trim($cls);
            if(!$cls)
                continue;

            $newClsIds[] = $cid;

            $clsValues .= '(' . $cid . ', \'' . $cls . '\'),';
        }

        $mapValues = '';
        foreach ($clsMap as $_cid => $_cidArr)
        {
            //未分类
            if($_cid == '0')
                continue;

            foreach ($_cidArr as $_tid)
            {
                $mapValues .= '(' . $_cid . ',' . $_tid . '),';
            }
        }

        $origClses = SlScheduleArticleClass::find()->select('id')->asArray()->all();
        //Classes and maps to be deleted 
        $delClsIds = [];
        foreach ($origClses as $oC) 
        {
            if(!in_array( $oC['id'], $newClsIds ))
                $delClsIds[] = $oC['id'];
        }

        $origTags = SlScheduleArticleTag::find()->select('id')->asArray()->all();
        //Classes and maps to be deleted 
        $delTagIds = [];
        foreach ($origTags as $oT) 
        {
            if(!in_array( $oT['id'], $newTagIds ))
                $delTagIds[] = $oT['id'];
        }        

        if(!empty($delClsIds))
        {
            SlScheduleArticleClass::deleteAll(['in', 'id', $delClsIds]);
        }

        if(!empty($delTagIds))
        {
            SlScheduleArticleTag::deleteAll(['in', 'id', $delTagIds]);
        }

        SlScheduleArticleClassTag::deleteAll();

        $tRet = true;
        $cRet = true;
        $mRet = true;

        if(!empty($tagValues))
            $tRet = Yii::$app->getModule('sl')->db->createCommand('INSERT IGNORE INTO '.SlScheduleArticleTag::tableName(). '([[id]], [[name]]) VALUES '. substr($tagValues, 0,-1))->execute();

        if(!empty($clsValues))
            $cRet = Yii::$app->getModule('sl')->db->createCommand('INSERT IGNORE INTO '.SlScheduleArticleClass::tableName(). '([[id]], [[name]]) VALUES '. substr($clsValues, 0,-1))->execute();

        if(!empty($mapValues))
            $mRet = Yii::$app->getModule('sl')->db->createCommand('INSERT IGNORE INTO '.SlScheduleArticleClassTag::tableName(). '([[class_id]], [[tag_id]]) VALUES '. substr($mapValues, 0,-1))->execute();

        if($tRet !==false && $mRet !== false && $cRet !== false)
        {
            return [
                    'code' => '0',
                    'data' => [],
                    'msg' => ''
                ];
        }
        else
        {
            return [
                    'code' => '1',
                    'data' => [],
                    'msg' => ''
                ];
        }
    }

    public function actionTest()
    {
        return $this->render('test');
    }
}
