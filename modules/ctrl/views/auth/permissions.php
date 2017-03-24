<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\Button;
use app\modules\ctrl\models\AuthItem;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel app\modules\ctrl\models\AuthItem */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app/ctrl/auth', 'Permissions');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-index">
    <!-- ?=
    Button::widget([
            'label' => '权限列表',
            'options' => ['class' => 'btn'],
    ]);
    ? -->
    <?=
    GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            'name',
            [
                'attribute' => 'ruleName',
                'label' => Yii::t('app/ctrl/auth', 'Rule Name'),
                'filter' => $rules
            ],
            'description',
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
                            'aria-label' => Yii::t('app/ctrl/auth', 'Delete'),
                            'data-confirm' => Yii::t('app/ctrl/auth', 'Are you sure you want to delete this item?'),
                            'data-method' => 'delete',
                            'data-pjax' => '1',
                        ]);
                     },
                    'update' => function($url, $model) {
                        return Html::a('<span class="glyphicon glyphicon-pencil"></span>', $url, [
                            'title' => Yii::t('app/ctrl/auth', 'Update'),
                            'aria-label' => Yii::t('app/ctrl/auth', 'Update'),
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
