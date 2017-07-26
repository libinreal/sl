<?php

namespace app\modules\nlp\controllers;

use yii\data\ActiveDataProvider;
use Yii;

/**
 * Default controller for the `nlp` module
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
