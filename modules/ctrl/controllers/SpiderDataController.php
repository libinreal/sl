<?php

namespace app\modules\ctrl\controllers;
use \app\modules\ctrl\models\CommentArticle;
use \app\modules\ctrl\models\CommentProduct;
use \app\modules\ctrl\models\DataArticleTopic;
use \app\modules\ctrl\models\DataProductCommentTopic;
use yii\data\ActiveDataProvider;
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
            // $articleModel = new CommentArticle();
            $articleModel = new DataArticleTopic();
            $articleProvider = $articleModel->search(Yii::$app->request->queryParams);

            return $this->render('data-search', [
                'articleModel' => $articleModel,
                'articleProvider' => $articleProvider
            ]);
        }
        else if( $category == 'product' )
        {
            // $productModel = new CommentProduct();
            $productModel = new DataProductCommentTopic();
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
        $queryParams = Yii::$app->request->get('SemanticsAnalysisForm');
        $from = $queryParams['from'];
        $kw = $queryParams['kw'];

        $sourceDb = Yii::$app->getModule('ctrl')->sourceDb;
        $spiderMongodb = Yii::$app->getModule('ctrl')->spiderMongodb;
        $module = Yii::$app->getModule('ctrl');
        $formModel = new SemanticsAnalysisForm;

        $product = $module->params['spiderData.fromSites']['product'];
        $article = $module->params['spiderData.fromSites']['article'];
        $fromSites = array_merge( $product, $article);
        // var_dump( Yii::$app->request->getQueryParams(), $from, $kw );exit;

        $dataProvider = new ActiveDataProvider([
            'query' => DataArticleTopic::find()->where('1=0'),
            'db'  => $sourceDb
        ]);

        if( !empty($from) && !empty($kw) )
        {
            if( in_array($from, array_keys($article) ) )
            {
                $comments = CommentArticle::find()->where(['like', 'content', $kw])->andWhere(['like', 'code', $from])->buildCursor($spiderMongodb);

                $codes = [];
                foreach ($comments as $comment)
                {
                    if( !in_array($comment['code'], $codes ) )
                        $codes[] = $comment['code'];
                }

                $query = DataArticleTopic::find();
                $dataProvider = new ActiveDataProvider([
                    'query' => $query,
                    'db'  => $sourceDb
                ]);
                $query->where(['in', 'article_code', $codes]);
            }
            else if( in_array($from, array_keys($product) ) )
            {
                $comments = CommentProduct::find()->where(['like', 'content', $kw])->andWhere(['like', 'code', $from])->buildCursor($spiderMongodb);

                $codes = [];
                foreach ($comments as $comment)
                {
                    if( !in_array($comment['code'], $codes ) )
                        $codes[] = $comment['code'];
                }

                $query = DataProductCommentTopic::find();
                $dataProvider = new ActiveDataProvider([
                    'query' => $query,
                    'db'  => $sourceDb
                ]);
                $query->andWhere(['in', 'product_code', $codes]);
            }
        }

        return $this->render('semantics-analysis', [
                'dataProvider' => $dataProvider,
                'formModel' => $formModel,
                'fromSites' => $fromSites,
            ]);
    }

}

/**
 * ctrl/spider-data/semantics-analysis 查询表单
 */
class SemanticsAnalysisForm extends \yii\base\Model{
    public $from;
    public $kw;

    public function rules()
    {
        $module = Yii::$app->getModule('ctrl');
        $product = array_keys( $module->params['spiderData.fromSites']['product'] );
        $article = array_keys( $module->params['spiderData.fromSites']['article'] );
        return [
            ['from', 'in', 'range' => array_merge($article, $product) ],
            ['kw', 'string', 'min' => 2],
        ];
    }

    public function attributeLabels()
    {
        return [
            'from' => Yii::t('app/ctrl/spider_data', 'From'),
            'kw' => Yii::t('app/ctrl/spider_data', 'Key word'),
        ];
    }
}
