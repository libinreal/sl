<?php
use yii\helpers\Html;
use yii\grid\GridView;
use app\modules\ctrl\controllers\SemanticsAnalysisForm;
use yii\widgets\ActiveForm;
/* @var $articleModel app\modules\ctrl\models\DataArticleTopic */
/* @var $articleProvider yii\data\ActiveDataProvider */
/* @var $formModel app\modules\ctrl\controllers\SemanticsAnalysisForm */
/* @var $this yii\web\View */

app\assets\AdminLteSelect2Asset::register($this);

$this->title = Yii::t('app/ctrl/spider_data', 'Semantics analysis');
$this->params['breadcrumbs'][] = $this->title;


// 查询表单
//
$searchForm = ActiveForm::begin(['action' => ['/ctrl/spider-data/semantics-analysis'],'method'=>'get']);
?>

<div class="col-lg-5">
    <div class="row">
        <div class="col-lg-6">
<?= $searchForm->field($formModel, 'from')->dropDownList( $fromSites, ['prompt'=>yii::t('app', 'Please choose'), 'class' => 'form-control select2']); ?>
        </div>
        <div class="col-lg-6">
<?= $searchForm->field($formModel, 'kw', ['template' => '<div class=\'input-group\'>{input}<span class=\'input-group-btn\'>' .
                                                                '<input type=\'submit\' class=\'btn btn-info btn-flat\'' . yii::t('app', 'Search') . '/></span></div>',
                                           'inputOptions' =>['maxlength' => 20, 'class' => 'form-control']
                                            ]); ?>
        </div>
    </div>
</div>
<?php ActiveForm::end(); ?>

<?php $this->beginBlock('js'); ?>
    $(function(){
        $('.select2').select2({'width':'auto'});
    });

    //获取
    function getCommentByTopic( id ){

    }
<?php
$this->endBlock();
$this->registerJs($this->blocks['js'], \yii\web\View::POS_END);
?>

<?=
GridView::widget([
    	'id' => 'commentsGrid',
        'dataProvider' => $dataProvider,
        'rowOptions' => function($model, $key, $index, $grid){
            return ['onclick' => 'getCommentByTopic('.$model->id.');'];
        },
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

