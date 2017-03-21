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

    public function actionUsers(){
        return $this->render('users');
    }

    public function actionUserOperate(){
        return '';
    }

    public function actionMenu()
    {
        return '';
    }

    public function actionMenuOperate()
    {
        return '';
    }

    public function actionRoles(){
        return '';
    }

    public function actionRoleOperate(){
        return '';
    }

    public function actionPermissions(){
        return '';
    }

    public function actionPermissionOperate(){
        return '';
    }
}
