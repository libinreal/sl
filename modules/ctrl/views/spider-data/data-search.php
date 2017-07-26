<?php
/* @var $this yii\web\View */

$this->title = Yii::t('app/ctrl/spider_data', 'Content search');
$this->params['breadcrumbs'][] = $this->title;

// Define  ranges correctly
	$date_ranges = new \yii\web\JsExpression("{
	                    '". Yii::t('app', 'Today')."'	       : [Date.today(), Date.today()],
	                    '". Yii::t('app', 'Yesterday')."'      : [Date.today().add({ days: -1 }), Date.today().add({ days: -1 })],
	                    '". Yii::t('app', 'Last 7 Days')."'    : [Date.today().add({ days: -6 }), Date.today()],
	                    '". Yii::t('app', 'Last 30 Days')."'   : [Date.today().add({ days: -29 }), Date.today()],
	                    '". Yii::t('app', 'This Month')."'     : [Date.today().moveToFirstDayOfMonth(), Date.today().moveToLastDayOfMonth()],
	                    '". Yii::t('app', 'This Year')."'      : [Date.today().moveToMonth(0,-1).moveToFirstDayOfMonth(), Date.today()],
	                    '". Yii::t('app', 'Last Month')."'     : [Date.today().moveToFirstDayOfMonth().add({ months: -1 }), Date.today().moveToFirstDayOfMonth().add({ days: -1 })]
	                }");

	// Define empty callback fust for fun
	$date_ranges_callback = new \yii\web\JsExpression("function(){}");
	$category = Yii::$app->request->get('category');
?>
<div class="row">
	<div class="col-xs-12">
		<div class="nav-tabs-custom">
			<ul class="nav nav-tabs">
				<li <?php if( $category == 'article' || empty( $category ) ): ?> class="active"<?php endif; ?>>
					<a href="/ctrl/spider-data/data-search/article" aria-expanded="true"><?= Yii::t('app', 'Article') ?></a>
				</li>
				<li <?php if( $category == 'product' ): ?> class="active"<?php endif; ?>>
					<a href="/ctrl/spider-data/data-search/product" aria-expanded="true"><?= Yii::t('app', 'Product') ?></a>
				</li>
			</ul>
			<div class="tab-content">

				<!--  article START  -->
				<div class="tab-pane <?php if( $category == 'article' || empty( $category ) ): echo 'active'; endif; ?>">
					<?php if( $category == 'article'  || empty( $category ) ): ?>
	    				<?= $this->render( "_article_search", [
				            'articleModel' => $articleModel,
				            'articleProvider' => $articleProvider,
				            'date_ranges_callback' => $date_ranges_callback,
				            'date_ranges' => $date_ranges,

				        ] );
				        ?>
			        <?php endif; ?>
    			</div><!-- .tab-pane -->
    			<!--  article END  -->

    			<!--  product  START -->
    			<div class="tab-pane <?php if( $category == 'product' ): echo 'active'; endif; ?>">
    				<?php if( $category == 'product' ): ?>
	    				<?= $this->render( "_product_search", [
				            'productModel' => $productModel,
				            'productProvider' => $productProvider,
				            'date_ranges_callback' => $date_ranges_callback,
				            'date_ranges' => $date_ranges,

				        ] ); ?>
			        <?php endif; ?>
    			</div><!-- .tab-pane -->
    			<!--  product  END -->

    		</div><!-- .tab-content -->
    	</div><!-- .nav-tabs-custom -->
    </div><!-- .col-xs-12 -->
</div><!-- .row -->
