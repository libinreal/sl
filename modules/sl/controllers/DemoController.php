<?php

namespace app\modules\sl\controllers;

use yii\data\ActiveDataProvider;
use Yii;

/**
 * Default controller for the `sl` module
 */
class DemoController extends \yii\web\Controller
{
    /**
     * 词性分析
     * @return string
     */
    public function actionIndex()
    {

        return $this->render('index');
    }

}
