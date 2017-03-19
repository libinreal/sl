<?php

namespace app\modules\ctrl\controllers;

use yii\web\Controller;

/**
 * Default controller for the `ctrl` module
 */
class AuthController extends Controller
{
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }
}
