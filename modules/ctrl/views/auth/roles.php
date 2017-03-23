<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\Button;
use app\modules\ctrl\models\AdminUsers;

/* @var $this yii\web\View */
/* @var $searchModel app\modules\ctrl\models\AuthItem */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', '权限分组');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-index">
    <?=
    Button::widget([
            'label' => '权限列表',
            'options' => ['class' => 'btn'],
    ]);
    ?>
    <?=
    GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            'name',
            'description',
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
