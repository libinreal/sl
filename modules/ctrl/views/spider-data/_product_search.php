<?php
use yii\helpers\Html;
use yii\grid\GridView;
use app\modules\ctrl\models\DataProductCommentTopic;
use yii\helpers\Url;
use bburim\daterangepicker\DateRangePicker as DateRangePicker;
/* @var $productModel app\modules\ctrl\models\DataProductCommentTopic */
/* @var $productProvider yii\data\ActiveDataProvider */
?>
<?=
GridView::widget([
    	'id' => 'articleGrid',
        'dataProvider' => $productProvider,
        'filterModel' => $productModel,
        'columns' => [
            /*[
               'class'=>CheckboxColumn::className(),
               'name'=>'id',  //设置每行数据的复选框属性
               'headerOptions' => ['width'=>'30'],
               'footer' => '<button href="#" class="btn btn-default btn-xs btn-delete" url="'. Url::toRoute('schedule-state-operate') .'">删除</button>',
               'footerOptions' => ['colspan' => 8],  //设置删除按钮垮列显示；
            ],*/
            [
            	'attribute' => 'id',
             	'footerOptions' => ['class'=>'hide']
            ],
            [
            	'attribute' => 'keyword',
             	'footerOptions' => ['class'=>'hide'],
            ],
            [
            	'attribute' => 'product_name',
                'footerOptions' => ['class'=>'hide'],
            ],
            [
                'attribute' => 'product_title',
                'footerOptions' => ['class'=>'hide'],
            ],
            [
                'attribute' => 'product_cate1',
                'footerOptions' => ['class'=>'hide'],
            ],
            [
                'attribute' => 'product_cate2',
                'footerOptions' => ['class'=>'hide'],
            ],
            [
                'attribute' => 'product_cate3',
                'footerOptions' => ['class'=>'hide'],
            ],
            [
            	'attribute' => 'comment_count',
             	'footerOptions' => ['class'=>'hide']
            ],
            [
                'attribute' => 'good_count',
                'footerOptions' => ['class'=>'hide']
            ],
            [
                'attribute' => 'general_count',
                'footerOptions' => ['class'=>'hide']
            ],
            [
                'attribute' => 'poor_count',
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