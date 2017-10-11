<?php 
use app\components\helpers\HtmlHelper;

$breadcrumbsJs = <<<EOT
//面包屑 START 

$('.xbreadcrumbs').xBreadcrumbs();

//面包屑  END

EOT;
    $this->registerJs($breadcrumbsJs);
?>
<div class="content">
	<div class="nav-path">
		<div class="np-prs"><a href="">SL</a></div>
		<div class="prs-left">
			<!--
			<span class="prs-text fl">SL System</span>
			<select name="" class="prs__select" style="display: none;">
				<option value="">任务控制</option>
			</select>

			<div class="breadcrumb fl">
				<div class="breadcrumb-item">
					<?php echo $this->title; ?>
				</div>
			</div>
			-->
		<?php echo HtmlHelper::renderBreadcrumbs( $this->params['breadcrumbs'], 'xbreadcrumbs' ); ?>			

		</div>
		<div class="prs-right">
			<div class="icon icon-mail"></div>
			<div class="icon icon-star"></div>
		</div>
	</div>
    <?= $content ?>
</div>