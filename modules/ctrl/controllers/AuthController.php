<?php

namespace app\modules\ctrl\controllers;

use yii\web\Controller;

/**
 * Default controller for the `ctrl` module
 */
class AuthController extends Controller
{
	public $adminUser = ['name'=>'admin', 'role_name'=>'管理员'];
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {

        return $this->render('index');
    }

    public function actionLogin(){
    	$this->layout = 'login';
    	return $this->render('login');
    }
}
