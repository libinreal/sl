<?php

namespace app\modules\ctrl\controllers;

use Yii;
use yii\web\Controller;
use \app\modules\ctrl\models\AdminUsers;
use \app\modules\ctrl\models\AuthItem;
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

        $searchModel = new AdminUsers();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('users', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider
            ]);
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
        $searchModel = new AuthItem();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('roles', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider
        ]);
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
