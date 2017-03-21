<?php

namespace app\modules\ctrl\controllers;

class SpiderTaskController extends \yii\web\Controller
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
        return $this->render('tasks');
    }

}
