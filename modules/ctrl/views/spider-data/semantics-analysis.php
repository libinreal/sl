<?php
use yii\helpers\Html;
use yii\grid\GridView;
use app\modules\ctrl\controllers\SemanticsAnalysisForm;
use yii\widgets\ActiveForm;
use yii\bootstrap\Modal;

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
<?php
ActiveForm::end();
$js = <<<JS
    var comments_link = '$commentsLink';
    $(function(){
        $('.select2').select2({'width':'auto'});
    });

    //获取
    function getCommentByTopic( id ){
        var gridView = '<div class="grid-view"><div class="summary">第{rows_index}条，共{total_num}条数据</div><table class="table table-striped table-bordered"' +
        '<thead><tr><th>评论</th><th>评论时间</th><th>评论时间</th><th>评论时间</th></tr></thead><tbody>';
        $.get(comments_link, null, function( ret, stat, xhr ){
            var total_num = xhr.getResponseHeader("X-Pagination-Total-Count");
            var page_count = xhr.getResponseHeader("X-Pagination-Page-Count");
            var current_page = xhr.getResponseHeader("X-Pagination-Current-Page");
            var per_page = xhr.getResponseHeader("X-Pagination-Per-Page");

            var row_start = (current_page - 1) * per_page + 1;
            var row_end = row_start + per_page;
            gridView = gridView.replace('{rows_index}', row_start + '-' + row_end).replace('{total_num}', total_num);

            for(var r in ret){
                gridView += '<tr>';
                gridView += '<td>' + r.content + '</td>';
                gridView += '<td>' + r.time + '</td>';
                gridView += '<td>' + '0' + '</td>';
                gridView += '<td>' + '0' + '</td>';
                gridView += '</tr>';
            }

            if( current_page == 1 ){
                gridView += '</tbody></table><ul class="pagination"><li>';
            }else if( current_page == page_count ){
                gridView += '</tbody></table><ul class="pagination"><li>';
            }else{
                gridView += '</tbody></table><ul class="pagination"><li>';
            }
        }, 'json');
    }
JS;
$this->registerJs($js, \yii\web\View::POS_END);
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
<?php
Modal::begin([
    'id' => 'comments-modal',
    'header' => '<h4 class="modal-title">用户评论</h4>',
    'footer' =>  '<a href="#" class="btn btn-primary" data-dismiss="modal">关闭</a>',
]);
Modal::end();
?>

