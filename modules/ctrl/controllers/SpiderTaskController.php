<?php

namespace app\modules\ctrl\controllers;

use Yii;
use yii\web\Controller;
use \app\modules\ctrl\models\TaskRule;

class SpiderTaskController extends Controller
{
    public function actionTaskGroupOperate()
    {
        return $this->render('task-group-operate');
    }

    public function actionTaskGroups()
    {
        return $this->render('task-groups');
    }

    public function actionTaskOperate()
    {
        return $this->render('task-operate');
    }

    public function actionTaskScheduleOperate()
    {
        return $this->render('task-schedule-operate');
    }

    public function actionTaskSchedules()
    {
        return $this->render('task-schedules');
    }

    public function actionTaskStatistics()
    {
        return $this->render('task-statistics');
    }

    public function actionTasks()
    {
        $searchModel = new TaskRule();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('tasks', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider
        ]);
    }

}
