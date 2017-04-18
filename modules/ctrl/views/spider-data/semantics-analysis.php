<?php
use yii\helpers\Html;
use yii\grid\GridView;
use app\modules\ctrl\controllers\SemanticsAnalysisForm;
use yii\widgets\ActiveForm;
/* @var $articleModel app\modules\ctrl\models\DataArticleTopic */
/* @var $articleProvider yii\data\ActiveDataProvider */
/* @var $formModel app\modules\ctrl\controllers\SemanticsAnalysisForm */
/* @var $this yii\web\View */
$this->title = Yii::t('app/ctrl/spider_data', 'Semantics analysis');
$this->params['breadcrumbs'][] = $this->title;

// 查询表单
$searchForm = ActiveForm::begin(['action' => ['test/getpost'],'method'=>'post',]);
echo $searchForm->field($formModel, 'from')->textInput(['maxlength' => 20]);
echo $searchForm->field($formModel, 'kw')->textInput(['maxlength' => 20]);
?>

<?=
GridView::widget([
    	'id' => 'commentsGrid',
        'dataProvider' => $dataProvider,
        'columns' => [
        	[
            	'attribute' => 'id',
             	'footerOptions' => ['class'=>'hide']
            ],
            [
            	'attribute' => 'site_name',
             	'footerOptions' => ['class'=>'hide']
            ],
            [
            	'attribute' => 'title',
             	'footerOptions' => ['class'=>'hide']
        ],
            [
            	'attribute' => 'crawl_time',
             	'footerOptions' => ['class'=>'hide']
            ]
        ]
]);
?>

