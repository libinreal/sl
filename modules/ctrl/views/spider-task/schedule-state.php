<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\Button;
use yii\grid\CheckboxColumn;
use app\modules\ctrl\models\TaskSchedulerState;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel app\modules\ctrl\models\TaskSchedulerState */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app/ctrl/task', 'Schedule state');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="schedule-state-index">
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
                'label' => Yii::t('app/ctrl/task_scheduler_state', 'Schedule id'),
             	'footerOptions' => ['class'=>'hide']
            ],
            [
            	'attribute' => 'name',
            	'value' => 'taskScheduler.name',
             	'footerOptions' => ['class'=>'hide'],
                'label' => Yii::t('app/ctrl/task_scheduler', 'Name'),
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
             	'attribute' => 'taskScheduler.start_time',
                'label' => Yii::t('app/ctrl/task_scheduler', 'Start time'),
             	'footerOptions' => ['class'=>'hide']
            ],
            [
                'attribute' => 'taskScheduler.end_time',
                'label' => Yii::t('app/ctrl/task_scheduler', 'End time'),
                'footerOptions' => ['class'=>'hide']
            ],
            [
                'attribute' => 'state',
                'value' => function($model) {
                    return $model->state;
                },
                'label' => Yii::t('app/ctrl/task_scheduler_state', 'State'),
                'filter' => [
                    TaskSchedulerState::STATE_STOPPED => Yii::t('app/ctrl/task_scheduler_state', 'Stopped'),
                    TaskSchedulerState::STATE_RUNNING => Yii::t('app/ctrl/task_scheduler_state', 'Running')
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
