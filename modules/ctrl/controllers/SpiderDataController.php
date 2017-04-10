<?php

namespace app\modules\ctrl\controllers;

class SpiderDataController extends \yii\web\Controller
{
    public $adminUser = ['name'=>'admin', 'role_name'=>'管理员'];
    public function actionDataDashboard()
    {
        return $this->render('data-dashboard');
    }

    public function actionDataOverview()
    {
        return $this->render('data-overview');
    }

    public function actionDataSearch()
    {
        return $this->render('data-search');
    }

    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionSemanticsAnalysis()
    {
        return $this->render('semantics-analysis');
    }

}
