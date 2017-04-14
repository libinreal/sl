<?php

namespace app\modules\ctrl\controllers;
use \app\modules\ctrl\models\CommentArticle;
use \app\modules\ctrl\models\CommentProduct;
use Yii;

class SpiderDataController extends \yii\web\Controller
{
    public $adminUser = ['name'=>'admin', 'role_name'=>'管理员'];
    public function actionDataDashboard()
    {
        return $this->render('data-dashboard');
    }

    public function actionDataOverview()
    {
        return $this->render('data-overview');
    }

    public function actionDataSearch()
    {
        $category = Yii::$app->request->get('category');
        if( $category == 'article' || empty($category) )
        {
            $articleModel = new CommentArticle();
            $articleProvider = $articleModel->search(Yii::$app->request->queryParams);

            return $this->render('data-search', [
                'articleModel' => $articleModel,
                'articleProvider' => $articleProvider
            ]);
        }
        else if( $category == 'product' )
        {
            $productModel = new CommentProduct();
            $productProvider = $productModel->search(Yii::$app->request->queryParams);

            return $this->render('data-search', [
                'productModel' => $productModel,
                'productProvider' => $productProvider
            ]);
        }
    }

    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionSemanticsAnalysis()
    {
        return $this->render('semantics-analysis');
    }

}
