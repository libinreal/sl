<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $searchModel app\modules\ctrl\models\AdminMenus */

$this->title = Yii::t('app/ctrl/auth', 'Menus');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="menu-index">

    <?php Pjax::begin(); ?>
    <?=
    GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            [
                'attribute' => 'name',
                'label' => Yii::t('app/ctrl/admin_menus', 'name'),
            ],
            [
                'attribute' => 'menuParent.name',
                'filter' => Html::activeTextInput($searchModel, 'parent_name', [
                    'class' => 'form-control', 'id' => null
                ]),
                'label' => Yii::t('app/ctrl/admin_menus', 'parent_name'),
            ],
            [
                'attribute' => 'url',
                'label' => Yii::t('app/ctrl/admin_menus', 'url'),
            ],
            [
                'attribute' => 'order',
                'label' => Yii::t('app/ctrl/admin_menus', 'order'),
            ],
            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]);
    ?>
<?php Pjax::end(); ?>

</div>
