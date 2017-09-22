<?php
use app\modules\sl\models\SlTaskItem;
use yii\helpers\Url;
use yii\helpers\Json;

    $this->title = '任务项运行状态';
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

    function changePageSize(_pageSize){
        pageSize = _pageSize

        goToPage(1);
    }

    function goToPage(_pageNo){
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

                if(json_data.data.rows.length > 0)
                {
                    var sOff = (_pageNo - 1 ) * pageSize + 1
                    var oOff = (_pageNo - 1 ) * pageSize + json_data.data.rows.length

                    var _summStr = "item" +  sOff + "-" +  oOff + " in " + json_data.data.total + " items"//summary
                    $(".sl-pagination__text").text( _summStr );

                }
            }
        });

    }

    function makePagination(_pageNo, _pageCount)
    {
    	var _paginationStr,

            _strFirst = '',
            _strPrev = '',
            _strNext = '',
            _strLast = '',

    		_activeStr = '',
    		_startPage = 1,
    		_endPage = _pageCount,
    		_pos = Math.ceil(paginationLen / 2),
    		_pageContainer = $('.slpc__page-nums')



            if(_pageCount - _pageNo >= _pos)//两边翻页
            {
            	//console.log('两边')
                if(_pageNo > _pos)
                    _startPage = _pageNo - _pos + 1
                _endPage = _startPage + paginationLen - 1
                if(_endPage > _pageCount)
                    _endPage = _pageCount

                if(_pageNo > 1)//两边翻页
                {
                    _strFirst = ' onclick="goToPage(1);" '
                    _strPrev = ' onclick="goToPage('+ (_pageNo - 1) +');" '
                    _strNext = ' onclick="goToPage('+ (_pageNo + 1) +');" '
                    _strLast = ' onclick="goToPage('+ _pageCount +');" '
                }
                else//右向翻页
                {
                    _strNext = ' onclick="goToPage('+ (_pageNo + 1) +');" '
                    _strLast = ' onclick="goToPage('+ _pageCount +');" '
                }
            }
            else if(_pageCount >= paginationLen)//左向翻页
            {
            	//console.log('左向')
                _startPage = _pageCount - paginationLen + 1
                _endPage = _pageCount

                _strFirst = ' onclick="goToPage(1);" '
                _strPrev = ' onclick="goToPage('+ (_pageNo - 1) +');" '

                if(_pageNo < _pageCount)//两边翻页
                {
                    _strFirst = ' onclick="goToPage(1);" '
                    _strPrev = ' onclick="goToPage('+ (_pageNo - 1) +');" '
                    _strNext = ' onclick="goToPage('+ (_pageNo + 1) +');" '
                    _strLast = ' onclick="goToPage('+ _pageCount +');" '
                }
                else//左向翻页
                {
                    _strFirst = ' onclick="goToPage(1);" '
                    _strPrev = ' onclick="goToPage('+ (_pageNo - 1) +');" '
                }
            }
            else if(_pageCount < paginationLen && _pageCount != 0)//无法翻页
            {
            	//console.log('无法')
                _startPage = 1;
                _endPage = _pageCount;

                if(_pageNo > 1 && _pageNo < _pageCount)//两边翻页
                {
                    _strFirst = ' onclick="goToPage(1);" '
                    _strPrev = ' onclick="goToPage('+ (_pageNo - 1) +');" '
                    _strNext = ' onclick="goToPage('+ (_pageNo + 1) +');" '
                    _strLast = ' onclick="goToPage('+ _pageCount +');" '
                }
                else if(_pageNo == 1 && _pageNo != _pageCount)//右向翻页
                {
                    _strNext = ' onclick="goToPage('+ (_pageNo + 1) +');" '
                    _strLast = ' onclick="goToPage('+ _pageCount +');" '
                }
                else if(_pageNo != 1 && _pageNo == _pageCount)//左向翻页
                {
                    _strFirst = ' onclick="goToPage(1);" '
                    _strPrev = ' onclick="goToPage('+ (_pageNo - 1) +');" '
                }
            }

            _paginationStr = '<div class="first"' + _strFirst + '><i class="sui-icon icon-step-backward"></i></div>' +
                             '<div class="prev"' + _strPrev + '><i class="sui-icon icon-caret-left"></i></div>'

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
    			_paginationStr += '<div class="sl-page-num sui-icon' + _activeStr + '" onclick="goToPage('+_i+')">' + _i + '</div>'
    		}

    		_paginationStr += '<div class="next"' + _strNext + '><i class="sui-icon icon-caret-right"></i></div>'
    						+ '<div class="last"' + _strLast + '><i class="sui-icon icon-step-forward"></i></div>'
            //console.log(' _pos ' +  _pos + ' _pageCount ' + _pageCount + ' _pageNo ' + _pageNo + ' _startPage ' + _startPage + ' _endPage ' + _endPage)
    		_pageContainer.html(_paginationStr);
    }

    var taskStatArr =<?php echo Json::encode([
    	SlTaskItem::TASK_STATUS_CLOSE => '未启动',
    	SlTaskItem::TASK_STATUS_OPEN => '正在进行',
    	SlTaskItem::TASK_STATUS_COMPLETE => '已完成',
    ]) ?>;

    var TASK_STAT = {
            'TASK_STATUS_CLOSE':<?php echo '\''.SlTaskItem::TASK_STATUS_CLOSE.'\''; ?>,
            'TASK_STATUS_OPEN':<?php echo '\''.SlTaskItem::TASK_STATUS_OPEN.'\''; ?>,
            'TASK_STATUS_COMPLETE':<?php echo '\''.SlTaskItem::TASK_STATUS_COMPLETE.'\''; ?>,

            'CONTROL_DEFAULT':<?php echo '\''.SlTaskItem::CONTROL_DEFAULT.'\'';?>,
            'CONTROL_STARTED':<?php echo '\''.SlTaskItem::CONTROL_STARTED.'\'';?>,
            'CONTROL_STOPPED':<?php echo '\''.SlTaskItem::CONTROL_STOPPED.'\'';?>,
            'CONTROL_RESTARTED':<?php echo '\''.SlTaskItem::CONTROL_RESTARTED.'\'';?>
            }

    /**
     * 显示任务数据
     * @param  array _rows 任务数组
     * @return
     */
    function showTaskData( _rows )
    {
    	var _container = $('.task_tables'),
    		_trStr = '',
    		_trLen = _rows.length,
            _statStr = '';

    	for(var _i = 0;_i < _trLen;_i++)
    	{
            if(_rows[_i]['control_status'] == TASK_STAT['CONTROL_STOPPED'])
            {
                _statStr = '已停止'
            }
            else
            {
                _statStr = taskStatArr[_rows[_i]['complete_status']]
                
            }
            
    		_trStr += '<tr task-id="'+_rows[_i]['id']+'"><td><span class="cell">'+ _rows[_i]['id'] +'</span>'+ '</td>'
    				+ '<td><span class="cell">'+ _rows[_i]['sche_id'] +'</span></td>'
    				+ '<td><span class="cell">'+ _rows[_i]['name'] +'</span></td>'

    				+ '<td><span class="cell">'+ _rows[_i]['brand_name'].substr(0, 5) +'</span></td>'
    				+ '<td><span class="cell">'+ _rows[_i]['key_words'].substr(0, 5) +'</span></td>'
    				+ '<td><span class="cell">'+ _rows[_i]['pf_name'].substr(0, 5) +'</span></td>'

    				+ '<td><span class="cell">'+ _rows[_i]['dt_category'] +'</span></td>'
    				+ '<td><span class="cell">'+ _statStr +'</span></td>'
    				+ '<td><span class="cell">'+ ((_rows[_i]['task_progress']) * 100).toFixed(2) +'%</span></td>'

                    + '<td><span class="cell">'+ _rows[_i]['task_time'] +'</span></td>'
    				+ '<td><span class="cell">'+ _rows[_i]['act_time'] +'</span></td>'
    				+ '<td><span class="cell">'+ _rows[_i]['complete_time'] +'</span></td>';

    				if( _rows[_i]['task_status'] == TASK_STAT['TASK_STATUS_OPEN'] && _rows[_i]['control_status'] == TASK_STAT['CONTROL_STOPPED'] )//启动
                    {
                        _trStr += '<td><span class="cell"><a href="javascript:updateTaskStat( '+ TASK_STAT['CONTROL_STARTED'] +', \''+_rows[_i]['id']+'\');" class="a--success">启动</a></span></td>'
                    }
                    else if( _rows[_i]['task_status'] == TASK_STAT['TASK_STATUS_CLOSE'] && (_rows[_i]['control_status'] == TASK_STAT['CONTROL_RESTARTED'] || _rows[_i]['control_status'] == TASK_STAT['CONTROL_STARTED'] || _rows[_i]['control_status'] == TASK_STAT['CONTROL_DEFAULT'] ) )//停止
                    {
    				    _trStr += '<td><span class="cell"><a href="javascript:updateTaskStat( '+ TASK_STAT['CONTROL_STOPPED'] +', \''+_rows[_i]['id']+'\');" class="a--danger">停止</a></span></td>'
                    }
                    else if( (_rows[_i]['task_status'] == TASK_STAT['TASK_STATUS_OPEN'] || _rows[_i]['task_status'] == TASK_STAT['TASK_STATUS_COMPLETE'] ) && (_rows[_i]['control_status'] == TASK_STAT['CONTROL_DEFAULT'] || _rows[_i]['control_status'] == TASK_STAT['CONTROL_RESTARTED'] || _rows[_i]['control_status'] == TASK_STAT['CONTROL_STARTED']) )//重启
                    {
                        _trStr += '<td><span class="cell"><a href="javascript:updateTaskStat(  '+ TASK_STAT['CONTROL_RESTARTED'] +', \''+_rows[_i]['id']+'\');" class="a--success">重启</a></span></td>'   
                    }

                    _trStr += '</tr>'
    	}
    	_container.find('tr:gt(0)').remove();
    	_container.find('tr:eq(0)').after(_trStr);
    }

    /**
     * 更改每日任务状态
     * @param  string _ctrlStat 更改的状态
     * @param  string _id 要更改的任务id
     * @return boolean
     */
    function updateTaskStat(_ctrlStat, _id)
    {
        var _actStr,
            _actRetStr,
            _opStr,
        _updateItemUrl = '/sl/schedule/update-task-item-stat',
        _container = $('.task_tables');


        var _updateItemData = {};
        _updateItemData['_csrf'] = csrfToken;

        _updateItemData['id'] = _id;
        _updateItemData['control_status'] = _ctrlStat;

        if(_ctrlStat == TASK_STAT['CONTROL_STARTED'])
        {
            _actStr = '启动'
            _opStr = '<a href="javascript:updateTaskStat( '+ TASK_STAT['CONTROL_STOPPED'] +', \''+_id+'\');" class="a--danger">停止</a>'
        }
        else if(_ctrlStat == TASK_STAT['CONTROL_STOPPED'])
        {
            _actStr = '停止'
            _actRetStr = '已停止'
            _opStr = '<a href="javascript:updateTaskStat( '+ TASK_STAT['CONTROL_STARTED'] +', \''+_id+'\');" class="a--success">启动</a>'
        }
        else if(_ctrlStat == TASK_STAT['CONTROL_RESTARTED'])
        {
            _actStr = '重启'
            _opStr = '<a href="javascript:updateTaskStat( '+ TASK_STAT['CONTROL_RESTARTED'] +', \''+_id+'\');" class="a--success">重启</a>'
        }

        $.confirm({
            title: '弹框',
            body: '是否'+_actStr+'任务项'+ _id +'?',
            okHide: function(){
                $.ajax({
                    crossDomain: true,
                    url: _updateItemUrl,
                    type: 'post',
                    data: _updateItemData,
                    dataType: 'json',
                    success: function (json_data) {
                        if(json_data.code == '0')
                        {
                            if(_actRetStr)
                                _container.find("tr[task-id='"+_id+"']").find("td:eq(7)").find("span").html(_actRetStr);

                            if(_ctrlStat == TASK_STAT['CONTROL_RESTARTED'])//重启
                            {
                                _container.find("tr[task-id='"+_id+"']").find("td:eq(7)").find("span").html('未启动');
                                _container.find("tr[task-id='"+_id+"']").find("td:eq(8)").find("span").html('0.00%');
                                _container.find("tr[task-id='"+_id+"']").find("td:eq(10)").find("span").html('');
                                _container.find("tr[task-id='"+_id+"']").find("td:eq(11)").find("span").html('');

                            }
                            _container.find("tr[task-id='"+_id+"']").find("td:eq(12)").find("span").html(_opStr);

                            $.alert(_actStr+'任务项'+_id+'成功');
                        }
                        else
                        {
                            $.alert(_actStr+'任务项'+_id+'失败');
                        }

                    }
                });
            }
        })

        return true;
    }

    /**
     * 删除任务项
     * @param  string _id 要删除的任务id
     * @return
     */
    function deleteItem(_id)
    {
        var _updateItemData = {},
            _removeItemUrl = '/sl/schedule/remove-task-item';

        _updateItemData['_csrf'] = csrfToken;
        _updateItemData['id'] = _id;

        $.confirm({
            title: '弹框',
            body: '是否删除任务'+ _id +'?',
            okHide: function(){
                $.ajax({
                    crossDomain: true,
                    url: _removeItemUrl,
                    type: 'post',
                    data: _updateItemData,
                    dataType: 'json',
                    success: function (json_data) {
                        if(json_data.code == '0')
                        {
                            $('.task_tables').find("tr[task-id='"+_id+"']").remove();
                            $.alert('删除任务'+_id+'成功');
                        }
                        else
                        {
                            $.alert('删除任务'+_id+'失败');
                        }

                    }
                });
            }
        })
    }

<?php
$this->endBlock();
$this->registerJs($this->blocks['taskJs'], \yii\web\View::POS_END);
?>

<div class="block clearfix">
				<div class="section clearfix">
					<span class="title-prefix-md"><?php echo $this->title; ?></span>
					<div class="sl-add-text fr" style="display: none;" onclick="javascript:location.href='/sl/schedule/add-schedule'">新增</div>
				</div>
				<div class="sl-query-wrapper sui-form clearfix">
					<form id="filterFrm">
					<div class="sl-query">
                        <div class="sl-query__label">名字</div>
                        <div class="sl-query__control">
                            <input type="text" name="name" class="input-medium">
                        </div>
                    </div>
                    <div class="sl-query">
                        <div class="sl-query__label">渠道</div>
                        <div class="sl-query__control">
                            <input type="text" name="pf_name" class="input-medium">
                        </div>
                    </div>
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
										<a role="button" data-toggle="dropdown" href="#" style="width: 79px;" class="dropdown-toggle">
											<input value="" name="complete_status" type="hidden">
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
										<a role="button" data-toggle="dropdown" href="#" style="width: 79px;" class="dropdown-toggle">
											<input value="" name="dt_category" type="hidden">
											<i class="caret"></i><span>全部</span>
										</a>
										<ul role="menu" class="sui-dropdown-menu">
											<li role="presentation"> <a role="menuitem" tabindex="-1" href="javascript:void(0);" value="">全部</a> </li>
											<li role="presentation"> <a role="menuitem" tabindex="-1" href="javascript:void(0);" value="商品">商品</a> </li>
											<li role="presentation"> <a role="menuitem" tabindex="-1" href="javascript:void(0);" value="评论">评论</a> </li>
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
					<button type="button" class="sui-btn btn-primary fl" style="margin-top: 33px;" onclick="javascript:goToPage(1);">搜索</button>
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
                            <th><span class="cell">计划开始时间</span></th>
							<th><span class="cell">实际开始时间</span></th>
							<th><span class="cell">完成时间</span></th>
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
										<li role="presentation"> <a role="menuitem" tabindex="-1" href="javascript:changePageSize(10);" value="10">10</a> </li>
                                        <li role="presentation"> <a role="menuitem" tabindex="-1" href="javascript:changePageSize(20);" value="20">20</a> </li>
                                        <li role="presentation"> <a role="menuitem" tabindex="-1" href="javascript:changePageSize(30);" value="30">30</a> </li>
                                        <li role="presentation"> <a role="menuitem" tabindex="-1" href="javascript:changePageSize(40);" value="40">40</a> </li>
                                        <li role="presentation"> <a role="menuitem" tabindex="-1" href="javascript:changePageSize(50);" value="50">50</a> </li>
									</ul>
								</span>
							</span>
						</div>
						<div class="sl-pagination__text">item1-10 in 213 items</div>
					</div>
				</div>
			</div>