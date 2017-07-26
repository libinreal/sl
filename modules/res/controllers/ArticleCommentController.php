<?php

namespace app\modules\res\controllers;

use yii\rest\Controller;
use yii\data\ActiveDataProvider;
use app\models\CommentArticle;
use Yii;

/**
 * Default controller for the `res` module
 */
class ArticleCommentController extends Controller
{
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        return new ActiveDataProvider([
            'query' => CommentArticle::find(),
            'db' => Yii::$app->getModule('ctrl')->spiderMongodb
        ]);
    }
}
