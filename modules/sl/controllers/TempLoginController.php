<?php

namespace app\modules\sl\controllers;

use yii\web\Response;
use yii\web\Cookie;
use yii\helpers\Json;
use Yii;

/**
 * Report controller for the `sl` module
 */
class TempLoginController extends \yii\web\Controller
{
	public function actionIndex()
    {
        if(Yii::$app->request->isGet)
        {
            $cookies = Yii::$app->response->cookies;
            $cookies->add(new Cookie([
                'name'=>'log_state',
                'value'=>'1',
                'expire'=>time() + 24 * 3600,
                ]));
            echo 'Logged in.';
            return ;
        }
    }
}
