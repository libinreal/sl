<?php

namespace app\modules\ctrl\controllers;

class DefaultController extends \yii\web\Controller
{
	public $adminUser = ['name'=>'admin', 'role_name'=>'管理员'];

    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionLogin()
    {
        $this->layout = 'login';
    	return $this->render('login');
    }

}
