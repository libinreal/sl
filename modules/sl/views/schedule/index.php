<?php
use app\modules\sl\models\SlTaskSchedule;
use yii\helpers\Url;
use yii\helpers\Json;

    $this->title = 'Task Schedule';

    $this->params['breadcrumbs'] = [ 
                                        'items' => [
                                                        [
                                                        'label' => 'Home',
                                                        'url' => '',
                                                        'items' => [
                                                                    [
                                                                        'label' => 'Task',
                                                                        'url' => '/sl/schedule/index'
                                                                    ],
                                                                    [
                                                                        'label' => 'Message',
                                                                        'url' => '/sl/message/abnormal'
                                                                    ],
                                                                    [
                                                                        'label' => 'Report',
                                                                        'url' => '/sl/report/crontab-data/product'
                                                                    ]
                                                                ]
                                                        ],
                                                        [
                                                        'label' => 'Task',
                                                        'url' => '/sl/schedule/index' ,
                                                        'items' => [
                                                                    [
                                                                        'label' => 'Task Schedule',
                                                                        'url' => '/sl/schedule/index'
                                                                    ],
                                                                    [
                                                                        'label' => 'Add WeChat Task Schedule',
                                                                        'url' => '/sl/schedule/add-schedule/article'
                                                                    ],
                                                                    [
                                                                        'label' => 'Add Product Task Schedule',
                                                                        'url' => '/sl/schedule/add-schedule/product'
                                                                    ]
                                                                ]
                                                        ],
                                                        [
                                                        'label' => 'Task Schedule',
                                                        'li_class' => 'current'
                                                        ]
                                                    ]
                                    ];                                

    $curPageUrl = Url::current();

    $scheDataJs = <<<EOT
    goToPage(1);
EOT;
	$this->registerJs($scheDataJs);
    $this->beginBlock('scheJs');
?>
    var pageNo = 1, pageSize = 10, pageCount = 0,
    	paginationLen = 5, refreshUrl = "<?= $curPageUrl ?>";

    function changePageSize(_pageSize){
        pageSize = _pageSize

        goToPage(1);
    }

    function goToPage(_pageNo){
        pageNo = _pageNo

    	var filterData = $("#filterFrm").find("input").serializeObject();

    	filterData['pageNo'] = _pageNo
    	filterData['pageSize'] = pageSize
    	filterData['_csrf'] = csrfToken
        //makePagination(7, 37);return;
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

            	showScheduleData(json_data.data.rows)//刷新数据

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

    var scheStatArr =<?php echo Json::encode([
    	SlTaskSchedule::SCHE_STATUS_CLOSE => '未启动',
    	SlTaskSchedule::SCHE_STATUS_OPEN => '已启动',
    	SlTaskSchedule::SCHE_STATUS_COMPLETE => '已完成',
    ]) ?>;

    /**
     * 显示计划任务数据
     * @param  array _rows 任务数组
     * @return
     */
    function showScheduleData( _rows )
    {
    	var _container = $('.schedule_tables'),
    		_trStr = '',
    		_trLen = _rows.length

    	for(var _i = 0;_i < _trLen;_i++)
    	{
    		_trStr += '<tr sche-id="'+_rows[_i]['id']+'"><td><span class="cell">'+ _rows[_i]['id'] +'</span>'+ '</td>'
    				+ '<td><span class="cell">'+ _rows[_i]['name'] +'</span>'+ '</td>'
    				+ '<td><span class="cell">'+ _rows[_i]['pf_name'] +'</span>'+ '</td>'

    				+ '<td><span class="cell">'+ _rows[_i]['brand_name'].substr(0, 62) +'</span>'+ '</td>'
    				+ '<td><span class="cell">'+ _rows[_i]['key_words'] +'</span>'+ '</td>'
    				+ '<td><span class="cell">'+ scheStatArr[_rows[_i]['sche_status']] +'</span>'+ '</td>'
    				+ '<td><span class="cell">';

                    if(tempLoginCookie == 1)
                    {
                        _trStr += '<a href="javascript:updateScheStat( \''+ _rows[_i]['sche_status'] +'\', \''+<?php echo SlTaskSchedule::SCHE_STATUS_OPEN;?>+'\', \''+_rows[_i]['id']+'\');" class="a--success">启动</a>'
        				+ '<a href="javascript:updateScheStat(\''+ _rows[_i]['sche_status'] +'\', \''+<?php echo SlTaskSchedule::SCHE_STATUS_CLOSE;?>+'\', \''+_rows[_i]['id']+'\');" class="a--danger">停止</a>'
        				+ '<a href="/sl/schedule/edit-schedule/'+ _rows[_i]['data_type'] + '/' + _rows[_i]['id']+ '" class="a--edit">编辑</a>'
        				+ '<a href="javascript:deleteSche(\''+_rows[_i]['id']+'\');" class="a--danger">删除</a>';
                    }

    				_trStr += '<a href="/sl/schedule/task-sche-crontab/'+_rows[_i]['id']+'" class="a--check">查看</a></span></td>'
                    + '</tr>'
    	}
    	_container.find('tr:gt(0)').remove();
    	_container.find('tr:eq(0)').after(_trStr);
    }

    /**
     * 更改计划任务状态
     * @param  string _curStat 当前任务计划状态
     * @param  string _newStat 要更改的任务计划状态
     * @param  string _id 要更改的任务计划id
     * @return boolean
     */
    function updateScheStat(_curStat, _newStat, _id)
    {
    	var _unstartStat = <?php echo SlTaskSchedule::SCHE_STATUS_CLOSE; ?>,
        _startStat = <?php echo SlTaskSchedule::SCHE_STATUS_OPEN; ?>,
        _okStat = <?php echo SlTaskSchedule::SCHE_STATUS_COMPLETE; ?>,
        _updateScheUrl = '/sl/schedule/update-schedule',
        _container = $('.schedule_tables');

        if(_curStat == _okStat)
        {
            $.alert('已完成');
            return;
        }

        if( _newStat == _startStat && _curStat == _unstartStat)//启动
        {
            var _updateScheData = {};
            _updateScheData['_csrf'] = csrfToken;

            _updateScheData['id'] = _id;
            _updateScheData['sche_status'] = _newStat;

            $.confirm({
                title: '弹框',
                body: '是否启动计划任务'+ _id +'?',
                okHide: function(){
                    $.ajax({
                        crossDomain: true,
                        url: _updateScheUrl,
                        type: 'post',
                        data: _updateScheData,
                        dataType: 'json',
                        success: function (json_data) {
                            // console.log(JSON.stringify(json_data));
                            if(json_data.code == '0')
                            {
                                _container.find("tr[sche-id='"+_id+"']").find("td:eq(5)").find("span").html("已启动");
                                $.alert('启动计划任务'+_id+'成功');
                            }
                            else
                            {
                                $.alert('启动计划任务'+_id+'失败');
                            }

                        }
                    });
                }
            })
        }
        else if( _newStat == _unstartStat && _curStat == _startStat)//停止
        {
            var _updateScheData = {};
            _updateScheData['_csrf'] = csrfToken;

            _updateScheData['id'] = _id;
            _updateScheData['sche_status'] = _newStat;

            $.confirm({
                title: '弹框',
                body: '是否停止计划任务'+ _id +'?',
                okHide: function(){
                    $.ajax({
                        crossDomain: true,
                        url: _updateScheUrl,
                        type: 'post',
                        data: _updateScheData,
                        dataType: 'json',
                        success: function (json_data) {
                            // console.log(JSON.stringify(json_data));
                            if(json_data.code == '0')
                            {
                                _container.find("tr[sche-id='"+_id+"']").find("td:eq(5)").find("span").html("未启动");
                                $.alert('停止计划任务'+_id+'成功');
                            }
                            else
                            {
                                $.alert('停止计划任务'+_id+'失败');
                            }

                        }
                    });
                }
            })
        }
        else if(_newStat == _startStat)
        {
            $.alert({
                    title: '提示',
                    body: '已启动!',
                });
        }
        else if(_newStat == _unstartStat)
        {
            $.alert({
                    title: '提示',
                    body: '已停止!',
                });
        }
    }

    /**
     * 删除计划任务
     * @param  string _id 要删除的任务计划id
     * @return
     */
    function deleteSche(_id)
    {
        var _updateScheData = {},
            _removeCrontabUrl = '/sl/schedule/remove-sche';

        _updateScheData['_csrf'] = csrfToken;
        _updateScheData['id'] = _id;

        $.confirm({
            title: '弹框',
            body: '是否删除任务'+ _id +'?',
            okHide: function(){
                $.ajax({
                    crossDomain: true,
                    url: _removeCrontabUrl,
                    type: 'post',
                    data: _updateScheData,
                    dataType: 'json',
                    success: function (json_data) {
                        if(json_data.code == '0')
                        {
                            $('.schedule_tables').find("tr[sche-id='"+_id+"']").remove();
                            $.alert('删除计划任务'+_id+'成功');
                        }
                        else
                        {
                            $.alert('删除计划任务'+_id+'失败');
                        }

                    }
                });
            }
        })
    }

<?php
$this->endBlock();
$this->registerJs($this->blocks['scheJs'], \yii\web\View::POS_END);
?>

<div class="block clearfix">
				<div class="section clearfix">
					<span class="title-prefix-md">计划任务列表</span>
					<div class="sl-add-text fr" onclick="javascript:location.href='/sl/schedule/add-schedule/product'">新增电商计划</div>
                    <div class="sl-add-text fr" onclick="javascript:location.href='/sl/schedule/add-schedule/article'">新增微信计划</div>
				</div>
				<div class="sl-query-wrapper sui-form clearfix">
					<form id="filterFrm" method="POST">
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
						<div class="sl-query__label">修改时间</div>
						<div class="sl-query__control">
							<input type="text" name="update_time_s" class="input-medium input-date"><span>-</span>
      						<input type="text" name="update_time_e" class="input-medium input-date">
						</div>
					</div>
					<button type="button" class="sui-btn btn-primary fl" style="margin-top: 33px;" onclick="javascript:goToPage(1);">搜索</button>
				</form>
				</div>
				<div class="sl-table-wrapper">
					<table class="sl-table schedule_tables">
						<tbody><tr class="sl-table__header">
							<th><span class="cell">计划任务ID</span></th>
							<th><span class="cell">任务名</span></th>
							<th><span class="cell">渠道</span></th>
							<th><span class="cell">品牌</span></th>
							<th><span class="cell">关键字</span></th>
							<th><span class="cell">状态</span></th>
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
						<div class="sl-pagination__text"></div><!-- item1-10 in 213 items -->
					</div>
				</div>
			</div>