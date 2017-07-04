<?php

namespace app\modules\sl\controllers;

use yii\data\ActiveDataProvider;
use Yii;

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

        return $this->render('index');
    }

    /**
     * 新增计划任务
     * method: GET,POST
     * @return string
     */
    public function actionAddSchedule()
    {

        return $this->render('add-schedule');
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
