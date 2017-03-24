<?php

use yii\helpers\Html;
use yii\grid\GridView;
use app\modules\ctrl\models\AdminUsers;

/* @var $this yii\web\View */
/* @var $searchModel app\modules\ctrl\models\AdminUsers */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app/ctrl/auth', 'Users');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-index">

    <?=
    GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            'id',
            'name',
            'email:email',
            [
                'attribute' => 'status',
                'value' => function($model) {
                    return $model->status == AdminUsers::STATUS_INACTIVE ? Yii::t('app/ctrl/auth', 'Inactive') : Yii::t('app/ctrl/auth', 'Active');
                },
                'filter' => [
                    AdminUsers::STATUS_INACTIVE => Yii::t('app/ctrl/auth', 'Inactive'),
                    AdminUsers::STATUS_ACTIVE => Yii::t('app/ctrl/auth', 'Active')
                ]
            ],
            [
                'value'=>function($model){
                    return  date('Y-m-d H:i:s',$model->last_login);
                }
            ],
            [
                'class' => 'yii\grid\ActionColumn',
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
                ]
                ],
            ],
        ]);
        ?>
</div>
