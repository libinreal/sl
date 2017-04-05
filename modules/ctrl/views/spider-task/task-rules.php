<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\Button;
use yii\grid\CheckboxColumn;
use app\modules\ctrl\models\TaskRule;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel app\modules\ctrl\models\AuthItem */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app/ctrl/task', 'Rule list');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="tasks-index">
    <a class="btn btn-success" href="/ctrl/spider-task/task-rule-operate"><?= Yii::t('app', 'Create'); ?></a>
    <?=
    GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            [
               'class'=>CheckboxColumn::className(),
               'name'=>'id',  //设置每行数据的复选框属性
               'headerOptions' => ['width'=>'30'],
               'footer' => '<button href="#" class="btn btn-default btn-xs btn-delete" url="'. Url::toRoute('task-rule-operate') .'">删除</button>',
               'footerOptions' => ['colspan' => 7],  //设置删除按钮垮列显示；
            ],
            [
            	'attribute' => 'rule_id',
             	'footerOptions' => ['class'=>'hide']
            ],
            [
            	'attribute' => 'site',
             	'footerOptions' => ['class'=>'hide']
            ],
            [
            	'attribute' => 'type',
                'value' => function($model) {
                	if($model->type == TaskRule::WEB_TYPE_UNKNOWN){
                		return  Yii::t('app/ctrl/task_rule', 'Unknown');
                	}else if($model->type == TaskRule::WEB_TYPE_LIST){
                		return  Yii::t('app/ctrl/task_rule', 'List mode');
                	}else if($model->type == TaskRule::WEB_TYPE_CONTENT){
                		return  Yii::t('app/ctrl/task_rule', 'Content mode');
                	}
                    return '';
                },
                'filter' => [
                    // 'prompt' => Yii::t('app/ctrl/auth', 'Select'),
                    TaskRule::WEB_TYPE_UNKNOWN => Yii::t('app/ctrl/task_rule', 'Unknown'),
                    TaskRule::WEB_TYPE_LIST => Yii::t('app/ctrl/task_rule', 'List mode'),
                    TaskRule::WEB_TYPE_CONTENT => Yii::t('app/ctrl/task_rule', 'Content mode')
                ],
                'footerOptions' => ['class'=>'hide'],
            ],
            [
            	'attribute' => 'delay',
             	'footerOptions' => ['class'=>'hide']
            ],
            [
            	'attribute' => 'encode',
             	'footerOptions' => ['class'=>'hide']
            ],
            [
                'attribute' => 'auto_proxy',
                'value' => function($model) {
                    return $model->auto_proxy == TaskRule::PROXY_CLOSED ? Yii::t('app/ctrl/task_rule', 'Proxy closed') : Yii::t('app/ctrl/task_rule', 'Proxy opened');
                },
                'filter' => [
                    // 'prompt' => Yii::t('app/ctrl/auth', 'Select'),
                    TaskRule::PROXY_CLOSED => Yii::t('app/ctrl/task_rule', 'Proxy closed'),
                    TaskRule::PROXY_OPENED => Yii::t('app/ctrl/task_rule', 'Proxy opened')
                ],
                'footerOptions' => ['class'=>'hide']
            ],
            [
                'class' => 'yii\grid\ActionColumn',
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
                            'data-confirm' => Yii::t('app', 'Are you sure you want to delete this item?'),
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
                ],
            ],
        ]);
        ?>
</div>
