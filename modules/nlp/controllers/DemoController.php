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
    public function actionWordClass()
    {

        return $this->render('word-class');
    }

    /**
     * 实体识别
     * @return [type] [description]
     */
    public function actionNameEntityRecognize()
    {
    	return $this->render('name-entity-recognize');
    }

    /**
     * 依存文法
     * @return [type] [description]
     */
    public function actionDependParse()
    {
    	return $this->render('depend-parse');
    }

    /**
     * 情感分析
     * @return [type] [description]
     */
    public function actionSentimentAnalyse()
    {
    	return $this->render('sentiment-analyse');
    }
}
