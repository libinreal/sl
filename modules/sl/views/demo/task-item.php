<?php
use app\modules\sl\models\SlTaskSchedule;
use yii\helpers\Url;
    $this->title = '任务运行状态';
    /*$this->params['breadcrumbs'][] = 'SL System';
    $this->params['breadcrumbs'][] = '计划任务列表';
    $this->params['breadcrumbs'][] = $this->title;*/
    $curPageUrl = Url::current();
    $this->beginBlock('scheJs');
?>
    var pageNo = 1, pageSize = 10, pageCount = 0,  refreshUrl = "<?= $curPageUrl ?>";
    goToPage();
    function goToPage(_pageNo = 1){
    	var csrfToken = $('meta[name="csrf-token"]').attr("content");
    	var filterData = $("#filterFrm").serializeObject();
    	var reqData = $.extend({}, filterData, {pageNo:pageNo, pageSize:pageSize, _csrf:csrfToken});
    	$.ajax({
            crossDomain: true,
            url: refreshUrl,
            type: 'post',
            data: reqData,
            dataType: 'json',
            success: function (json_data) {
            	// console.log(JSON.stringify(json_data));

            }
        });

    }

<?php
$this->endBlock();
$this->registerJs($this->blocks['scheJs'], \yii\web\View::POS_END);
?>

<div class="block clearfix">
				<div class="section clearfix">
					<span class="title-prefix-md">任务运行状态</span>
					<div class="sl-add-text fr" onclick="javascript:location.href='/sl/demo/add-schedule'">新增</div>
				</div>
				<div class="sl-query-wrapper sui-form clearfix">
					<form id="filterFrm">
					<div class="sl-query">
						<div class="sl-query__label">品牌</div>
						<div class="sl-query__control">
							<input type="text" name="brand_name" class="input-medium">
						</div>
					</div>
					<div class="sl-query">
						<div class="sl-query__label">关键字</div>
						<div class="sl-query__control">
							<input type="text" name="key_words" class="input-medium">
						</div>
					</div>
					<div class="sl-query">
						<div class="sl-query__label">状态</div>
						<div class="sl-query__control">
							<span class="sui-dropdown dropdown-bordered select">
									<span class="dropdown-inner">
										<a role="button" data-toggle="dropdown" href="#" class="dropdown-toggle">
											<input value="" name="sche_status" type="hidden">
											<i class="caret"></i><span>全部</span>
										</a>
										<ul role="menu" class="sui-dropdown-menu">
											<li role="presentation"> <a role="menuitem" tabindex="-1" href="javascript:void(0);" value="">全部</a> </li>
											<li role="presentation"> <a role="menuitem" tabindex="-1" href="javascript:void(0);" value="0">未启动</a> </li>
											<li role="presentation"> <a role="menuitem" tabindex="-1" href="javascript:void(0);" value="1">已启动</a> </li>
											<li role="presentation"> <a role="menuitem" tabindex="-1" href="javascript:void(0);" value="2">已完成</a> </li>
										</ul>
									</span>
							</span>
						</div>
					</div>
					<div class="sl-query">
						<div class="sl-query__label">抓取内容</div>
						<div class="sl-query__control">
							<span class="sui-dropdown dropdown-bordered select">
									<span class="dropdown-inner">
										<a role="button" data-toggle="dropdown" href="#" class="dropdown-toggle">
											<input value="" name="dt_category" type="hidden">
											<i class="caret"></i><span>全部</span>
										</a>
										<ul role="menu" class="sui-dropdown-menu">
											<li role="presentation"> <a role="menuitem" tabindex="-1" href="javascript:void(0);" value="">全部</a> </li>
											<li role="presentation"> <a role="menuitem" tabindex="-1" href="javascript:void(0);" value="0">商品</a> </li>
											<li role="presentation"> <a role="menuitem" tabindex="-1" href="javascript:void(0);" value="1">评论</a> </li>
										</ul>
									</span>
							</span>
						</div>
					</div>
					<div class="sl-query input-daterange" data-toggle="datepicker">
						<div class="sl-query__label">修改时间</div>
						<div class="sl-query__control">
							<input type="text" name="update_time_s" class="input-medium input-date"><span>-</span>
      						<input type="text" name="update_time_e" class="input-medium input-date">
						</div>
					</div>
					<button type="button" class="sui-btn btn-primary fl" style="margin-top: 33px;">搜索</button>
				</form>
				</div>
				<div class="sl-table-wrapper">
					<table class="sl-table">
						<tbody><tr class="sl-table__header">
							<th><span class="cell">任务ID</span></th>
							<th><span class="cell">计划任务ID</span></th>
							<th><span class="cell">任务名</span></th>
							<th><span class="cell">品牌</span></th>
							<th><span class="cell">关键字</span></th>
							<th><span class="cell">渠道</span></th>
							<th><span class="cell">抓取内容</span></th>
							<th><span class="cell">状态</span></th>
							<th><span class="cell">状态</span></th>
							<th><span class="cell">任务进度</span></th>
							<th><span class="cell">更新时间</span></th>
							<th><span class="cell">操作</span></th>
						</tr>
						<tr class="sl-table__row">
							<td><span class="cell">1</span></td>
							<td><span class="cell">1</span></td>
							<td><span class="cell">京东华为手机详情和评论内容</span></td>
							<td><span class="cell">华为</span></td>
							<td><span class="cell">手机安静了开发经费放假啊可怜的放假啊来看</span></td>
							<td><span class="cell">京东</span></td>
							<td><span class="cell">商品</span></td>
							<td><span class="cell">未启动</span></td>
							<td><span class="cell">662331</span></td>
							<td><span class="cell">99%</span></td>
							<td><span class="cell">2017/6/26 14:09</span></td>
							<td><span class="cell">
								<a href="javascript:" class="a--success">启动</a>
								<a href="javascript:" class="a--danger">停止</a>
								<a href="javascript:" class="a--danger">删除</a>
							</span></td>
						</tr>
						<tr class="sl-table__row">
							<td><span class="cell">1</span></td>
							<td><span class="cell">1</span></td>
							<td><span class="cell">京东华为手机详情和评论内容</span></td>
							<td><span class="cell">华为</span></td>
							<td><span class="cell">手机安静了开发经费放假啊可怜的放假啊来看</span></td>
							<td><span class="cell">京东</span></td>
							<td><span class="cell">商品</span></td>
							<td><span class="cell">未启动</span></td>
							<td><span class="cell">662331</span></td>
							<td><span class="cell">99%</span></td>
							<td><span class="cell">2017/6/26 14:09</span></td>
							<td><span class="cell">
								<a href="javascript:" class="a--success">启动</a>
								<a href="javascript:" class="a--danger">停止</a>
								<a href="javascript:" class="a--danger">删除</a>
							</span></td>
						</tr>
						<tr class="sl-table__row">
							<td><span class="cell">1</span></td>
							<td><span class="cell">1</span></td>
							<td><span class="cell">京东华为手机详情和评论内容</span></td>
							<td><span class="cell">华为</span></td>
							<td><span class="cell">手机安静了开发经费放假啊可怜的放假啊来看</span></td>
							<td><span class="cell">京东</span></td>
							<td><span class="cell">商品</span></td>
							<td><span class="cell">未启动</span></td>
							<td><span class="cell">662331</span></td>
							<td><span class="cell">99%</span></td>
							<td><span class="cell">2017/6/26 14:09</span></td>
							<td><span class="cell">
								<a href="javascript:" class="a--success">启动</a>
								<a href="javascript:" class="a--danger">停止</a>
								<a href="javascript:" class="a--danger">删除</a>
							</span></td>
						</tr>
					</tbody></table>
					<div class="sl-pagination">
						<div class="sl-pagination__control">
							<div class="slpc__page-nums clearfix fl">
								<div class="first"><i class="sui-icon icon-step-backward"></i></div>
								<div class="prev"><i class="sui-icon icon-caret-left"></i></div>
								<div class="sl-page-num sui-icon is-active">1</div>
								<div class="sl-page-num sui-icon">2</div>
								<div class="sl-page-num sui-icon">3</div>
								<div class="sl-page-num sui-icon">4</div>
								<div class="next"><i class="sui-icon icon-caret-right"></i></div>
								<div class="last"><i class="sui-icon icon-step-forward"></i></div>

							</div>
							<span class="sui-dropdown dropdown-bordered select select--sm fl">
								<span class="dropdown-inner">
									<a role="button" data-toggle="dropdown" href="#" class="dropdown-toggle">
										<input value="1" name="" type="hidden">
										<i class="caret"></i><span>10</span>
									</a>
									<ul role="menu" class="sui-dropdown-menu">
										<li role="presentation"> <a role="menuitem" tabindex="-1" href="javascript:void(0);" value="1">10</a> </li>
										<li role="presentation"> <a role="menuitem" tabindex="-1" href="javascript:void(0);" value="2">9</a> </li>
									</ul>
								</span>
							</span>
						</div>
						<div class="sl-pagination__text">item1-10 in 213 items</div>
					</div>
				</div>
			</div>