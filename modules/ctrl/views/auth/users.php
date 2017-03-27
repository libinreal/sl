<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\grid\CheckboxColumn;
use app\modules\ctrl\models\AdminUsers;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel app\modules\ctrl\models\AdminUsers */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app/ctrl/auth', 'Users');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-index">
<a class="btn btn-success" href="/ctrl/auth/user-operate"><?= Yii::t('app/ctrl/auth', 'Create'); ?></a>
    <?=
    GridView::widget([
        'showFooter' => true,  //设置显示最下面的footer
        'id' => 'userGrid',
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            [
               'class'=>CheckboxColumn::className(),
               'name'=>'id',  //设置每行数据的复选框属性
               'headerOptions' => ['width'=>'30'],
               'footer' => '<button href="#" class="btn btn-default btn-xs btn-delete" url="'. Url::toRoute('user-operate') .'">删除</button>',
               'footerOptions' => ['colspan' => 5],  //设置删除按钮垮列显示；
            ],
            ['attribute' => 'id', 'footerOptions' => ['class'=>'hide']],
            ['attribute' => 'name', 'footerOptions' => ['class'=>'hide']],
            ['attribute' => 'email', 'format' => 'email', 'footerOptions' => ['class'=>'hide']],
            [
                'attribute' => 'status',
                'value' => function($model) {
                    return $model->status == AdminUsers::STATUS_INACTIVE ? Yii::t('app/ctrl/auth', 'Inactive') : Yii::t('app/ctrl/auth', 'Active');
                },
                'filter' => [
                    // 'prompt' => Yii::t('app/ctrl/auth', 'Select'),
                    AdminUsers::STATUS_INACTIVE => Yii::t('app/ctrl/auth', 'Inactive'),
                    AdminUsers::STATUS_ACTIVE => Yii::t('app/ctrl/auth', 'Active')
                ],
                'footerOptions' => ['class'=>'hide']
            ],
            [
                'attribute' => 'last_login',
                'value'=>function($model){
                    return  date('Y-m-d H:i:s',$model->last_login);
                },
                'footerOptions' => ['class'=>'hide']
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'header' => '管理操作',
                'template' => '{view} {update} {delete}',
                'buttons' => [
                    'view' => function($url, $model, $key){
                       return Html::a('<span class="glyphicon glyphicon-eye-open"></span>', $url, [
                            'title' => Yii::t('app/ctrl/auth', 'View'),
                            'data-method' => 'get',
                            'data-pjax' => '1'
                        ]);
                    },
                    'delete' => function($url, $model, $key){
                       return Html::a('<span class="glyphicon glyphicon-remove"></span>', $url, [
                            'title' => Yii::t('app/ctrl/auth', 'Delete'),
                            'data-confirm' => Yii::t('app/ctrl/auth', 'Are you sure you want to delete this item?'),
                            'data-method' => 'delete',
                            'data-pjax' => '1',
                        ]);
                     },
                    'update' => function($url, $model) {
                        return Html::a('<span class="glyphicon glyphicon-pencil"></span>', $url, [
                            'title' => Yii::t('app/ctrl/auth', 'Update'),
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
