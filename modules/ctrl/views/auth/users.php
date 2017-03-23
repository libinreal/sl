<?php

use yii\helpers\Html;
use yii\grid\GridView;
use app\modules\ctrl\models\AdminUsers;

/* @var $this yii\web\View */
/* @var $searchModel app\modules\ctrl\models\AdminUsers */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', '用户列表');
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
                    return $model->status == 0 ? '禁用' : '正常';
                },
                'filter' => [
                    0 => '禁用',
                    1 => '正常'
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
                            'title' => Yii::t('app', '编辑'),
                            'data-method' => 'get',
                            'data-pjax' => '1'
                        ]);
                    },
                    'delete' => function($url, $model, $key){
                       return Html::a('<span class="glyphicon glyphicon-remove"></span>', $url, [
                            'title' => Yii::t('app', '删除'),
                            'aria-label' => Yii::t('app', '删除'),
                            'data-confirm' => Yii::t('app', '你确定要删除？'),
                            'data-method' => 'delete',
                            'data-pjax' => '1',
                        ]);
                     },
                    'update' => function($url, $model) {
                        return Html::a('<span class="glyphicon glyphicon-pencil"></span>', $url, [
                            'title' => Yii::t('app', '编辑'),
                            'aria-label' => Yii::t('app', '编辑'),
                            'data-confirm' => Yii::t('app', '你确定要更新?'),
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
