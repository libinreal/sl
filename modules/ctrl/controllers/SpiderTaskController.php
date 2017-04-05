<?php

namespace app\modules\ctrl\controllers;

use Yii;
use yii\web\Controller;
use \app\modules\ctrl\models\TaskRule;
use \app\modules\ctrl\models\TaskScheduler;

class SpiderTaskController extends Controller
{
    public $adminUser = ['name'=>'admin', 'role_name'=>'管理员'];
    public function actionTaskGroupOperate()
    {
        return $this->render('task-group-operate');
    }

    public function actionTaskGroups()
    {
        return $this->render('task-groups');
    }

    public function actionTaskRuleOperate()
    {
        return $this->render('task-rule-operate');
    }

    public function actionTaskScheduleOperate()
    {
        return $this->render('task-schedule-operate');
    }

    public function actionTaskSchedules()
    {
        $searchModel = new TaskScheduler();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('task-schedules', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider
        ]);
    }

    public function actionTaskStatistics()
    {
        return $this->render('task-statistics');
    }

    public function actionTaskRules()
    {
        $searchModel = new TaskRule();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('task-rules', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider
        ]);
    }

}
