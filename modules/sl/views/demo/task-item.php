<?php
use app\modules\sl\models\SlTaskItem;
use yii\helpers\Url;
use yii\helpers\Json;

    $this->title = '任务运行状态';
    /*$this->params['breadcrumbs'][] = 'SL System';
    $this->params['breadcrumbs'][] = '计划任务列表';
    $this->params['breadcrumbs'][] = $this->title;*/
    $curPageUrl = Url::current();

        $taskItemDataJs = <<<EOT
    goToPage(1);
EOT;
	$this->registerJs($taskItemDataJs);
    $this->beginBlock('taskJs');
?>
    var pageNo = 1, pageSize = 10, pageCount = 0,
    	paginationLen = 5, refreshUrl = "<?php $taskUrl = strstr($curPageUrl, '?', true); echo $taskUrl.'/'.$cron_id;?>";

    function goToPage(_pageNo = 1){
    	var filterData = $("#filterFrm").find("input").serializeObject();

    	filterData['pageNo'] = _pageNo
    	filterData['pageSize'] = pageSize
    	filterData['_csrf'] = csrfToken

    	filterData['cron_id'] = <?php echo $cron_id; ?>

    	$.ajax({
            crossDomain: true,
            url: refreshUrl,
            type: 'post',
            data: filterData,
            dataType: 'json',
            success: function (json_data) {
            	// console.log(JSON.stringify(json_data));

            	var _total = json_data.data.total
            	pageCount = Math.ceil(_total / pageSize)
            	makePagination(_pageNo, pageCount)//分页

            	showTaskData(json_data.data.rows)//刷新数据
            }
        });

    }

    function makePagination(_pageNo, _pageCount)
    {
    	var _paginationStr,
    		_activeStr = '',
    		_startPage = 1,
    		_endPage = _pageCount,
    		_pos = Math.floor(_pageNo / paginationLen),
    		_pageContainer = $('.slpc__page-nums')

    		_paginationStr = '<div class="first"><i class="sui-icon icon-step-backward"></i></div>' +
    						 '<div class="prev"><i class="sui-icon icon-caret-left"></i></div>'

    		//console.log('_pageCount - _pageNo - _pos + 1 >= 0  ' + (_pageCount - _pageNo - _pos + 1 ))

    		if( _pageCount > paginationLen && _pageCount - _pageNo - _pos + 1 >= 0 )//When pageCount >= paginationLen
			{
				_startPage = _pageNo - _pos
				_endPage = _startPage + paginationLen - 1
			}

    		for(var _i = _startPage; _i <= _endPage; _i++)
    		{
    			if(_i == _pageNo)
    			{
    				_activeStr = ' is-active'
    			}
    			else
    			{
    				_activeStr = ''
    			}
    			_paginationStr += '<div class="sl-page-num sui-icon' + _activeStr + '" onclick="goToPage(\''+_i+'\')">' + _i + '</div>'
    		}

    		_paginationStr += '<div class="next"><i class="sui-icon icon-caret-right"></i></div>'
    						+ '<div class="last"><i class="sui-icon icon-step-forward"></i></div>'

    		_pageContainer.html(_paginationStr);
    }

    var taskStatArr =<?php echo Json::encode([
    	SlTaskItem::TASK_STATUS_CLOSE => '未启动',
    	SlTaskItem::TASK_STATUS_OPEN => '已启动',
    	SlTaskItem::TASK_STATUS_COMPLETE => '已完成',
    ]) ?>;

    /**
     * 显示任务数据
     * @param  array _rows 任务数组
     * @return
     */
    function showTaskData( _rows )
    {
    	var _container = $('.task_tables'),
    		_trStr = '',
    		_trLen = _rows.length

    	for(var _i = 0;_i < _trLen;_i++)
    	{
    		_trStr += '<tr task-id="'+_rows[_i]['id']+'"><td><span class="cell">'+ _rows[_i]['id'] +'</span>'+ '</td>'
    				+ '<td><span class="cell">'+ _rows[_i]['sche_id'] +'</span>'+ '</td>'
    				+ '<td><span class="cell">'+ _rows[_i]['name'] +'</span>'+ '</td>'

    				+ '<td><span class="cell">'+ _rows[_i]['brand_name'].substr(0, 5) +'</span>'+ '</td>'
    				+ '<td><span class="cell">'+ _rows[_i]['key_words'].substr(0, 5) +'</span>'+ '</td>'
    				+ '<td><span class="cell">'+ _rows[_i]['pf_name'].substr(0, 5) +'</span>'+ '</td>'

    				+ '<td><span class="cell">'+ _rows[_i]['dt_category'] +'</span>'+ '</td>'
    				+ '<td><span class="cell">'+ taskStatArr[_rows[_i]['task_status']] +'</span>'+ '</td>'
    				+ '<td><span class="cell">'+ ((_rows[_i]['task_progress']) * 100).toFixed(2) +'%</span>'+ '</td>'

    				+ '<td><span class="cell">'+ _rows[_i]['task_time'] +'</span>'+ '</td>'

    				+ '<td><span class="cell"><a href="javascript:updateTaskStat( \''+ _rows[_i]['task_status'] +'\', \''+<?php echo SlTaskItem::TASK_STATUS_OPEN;?>+'\');" class="a--success">启动</a>'
    				+ '<a href="javascript:updateTaskStat(\''+ _rows[_i]['task_status'] +'\', \''+<?php echo SlTaskItem::TASK_STATUS_CLOSE;?>+'\');" class="a--danger">停止</a>'
    				+ '<a href="javascript:deleteTask(\''+_rows[_i]['id']+'\');" class="a--danger">删除</a>'
    	}
    	_container.find('tr:gt(0)').remove();
    	_container.find('tr:eq(0)').after(_trStr);
    }

<?php
$this->endBlock();
$this->registerJs($this->blocks['taskJs'], \yii\web\View::POS_END);
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
											<input value="" name="task_status" type="hidden">
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
						<div class="sl-query__label">开始时间</div>
						<div class="sl-query__control">
							<input type="text" name="task_time_s" class="input-medium input-date"><span>-</span>
      						<input type="text" name="task_time_e" class="input-medium input-date">
						</div>
					</div>
					<button type="button" class="sui-btn btn-primary fl" style="margin-top: 33px;">搜索</button>
				</form>
				</div>
				<div class="task_tables sl-table-wrapper">
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
							<th><span class="cell">任务进度</span></th>
							<th><span class="cell">开始时间</span></th>
							<th><span class="cell">操作</span></th>
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