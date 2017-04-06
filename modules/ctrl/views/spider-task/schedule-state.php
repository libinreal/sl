<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\Button;
use yii\grid\CheckboxColumn;
use app\modules\ctrl\models\TaskScheduler;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel app\modules\ctrl\models\AuthItem */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app/ctrl/task', 'Schedule state');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="schedule-state-index">
    <a class="btn btn-success" href="/ctrl/spider-task/schedule-state-operate"><?= Yii::t('app', 'Create'); ?></a>
    <?=
    GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            [
               'class'=>CheckboxColumn::className(),
               'name'=>'id',  //设置每行数据的复选框属性
               'headerOptions' => ['width'=>'30'],
               'footer' => '<button href="#" class="btn btn-default btn-xs btn-delete" url="'. Url::toRoute('schedule-state-operate') .'">删除</button>',
               'footerOptions' => ['colspan' => 8],  //设置删除按钮垮列显示；
            ],
            [
            	'attribute' => 'scheduler_id',
             	'footerOptions' => ['class'=>'hide']
            ],
            [
            	'attribute' => 'name',
            	'value' => 'task_scheduler.name',
             	'footerOptions' => ['class'=>'hide']
            ],
            [
            	'attribute' => 'getting_number',
                'footerOptions' => ['class'=>'hide'],
            ],
            [
            	'attribute' => 'total_number',
             	'footerOptions' => ['class'=>'hide']
            ],
            [
             	'attribute' => 'end_time',
             	'footerOptions' => ['class'=>'hide']
            ],
            [
                'attribute' => 'status',
                'value' => function($model) {
                    return $model->status == TaskScheduler::STATUS_STOPPED ? Yii::t('app/ctrl/task_scheduler', 'Stopped') : Yii::t('app/ctrl/task_scheduler', 'Running');
                },
                'filter' => [
                    // 'prompt' => Yii::t('app/ctrl/auth', 'Select'),
                    TaskScheduler::STATUS_STOPPED => Yii::t('app/ctrl/task_scheduler', 'Stopped'),
                    TaskScheduler::STATUS_RUNNING => Yii::t('app/ctrl/task_scheduler', 'Running')
                ],
                'footerOptions' => ['class'=>'hide']
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'header' => '管理操作',
                'template' => '{view} {update} {delete}',
                'buttons' => [
                    'view' => function($url, $model, $key){
                       return Html::a('<span class="glyphicon glyphicon-eye-open"></span>', $url, [
                            'title' => Yii::t('app', 'View'),
                            'data-method' => 'get',
                            'data-pjax' => '1'
                        ]);
                    },
                    'delete' => function($url, $model, $key){
                       return Html::a('<span class="glyphicon glyphicon-remove"></span>', $url, [
                            'title' => Yii::t('app', 'Delete'),
                            'data-confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'),
                            'data-method' => 'delete',
                            'data-pjax' => '1',
                        ]);
                     },
                    'update' => function($url, $model) {
                        return Html::a('<span class="glyphicon glyphicon-pencil"></span>', $url, [
                            'title' => Yii::t('app', 'Update'),
                            'data-method' => 'put',
                            'data-pjax' => '1',
                        ]);
                    }
                ],
                'footerOptions' => ['class'=>'hide']
            ]
        ],
    ]);
    ?>
</div>
