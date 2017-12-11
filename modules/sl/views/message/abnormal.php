<?php
use app\models\sl\SlTaskScheduleCrontabAbnormal;
use yii\helpers\Url;
use yii\helpers\Json;

$this->title = 'Task Alert Message';

$this->params['breadcrumbs'] = [ 
                                    'items' => [
                                                    [
                                                    'label' => 'Home',
                                                    'url' => '/',
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
                                                    'label' => 'Message',
                                                    'url' => '/sl/message/abnormal' ,
                                                    ],
                                                    [
                                                    'label' => 'Task Alert Message',
                                                    'li_class' => 'current'
                                                    ]
                                                ]
                                ];

    $curPageUrl = Url::current();

    $scheDataJs = <<<EOT
    goToPage(1);
EOT;
	$this->registerJs($scheDataJs);
    $this->beginBlock('abnormalJs');
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

            	showAbnormal(json_data.data.rows)//刷新数据

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

    var abnormalResolveStat =<?php echo Json::encode([
    	SlTaskScheduleCrontabAbnormal::RESOLVE_TYPE_UNRESOLVED => '未解决',
    	SlTaskScheduleCrontabAbnormal::RESOLVE_TYPE_RESOLVED => '已解决',
    	SlTaskScheduleCrontabAbnormal::RESOLVE_TYPE_IGNORED => '忽略',
    ]) ?>,
        RESOLVE_STAT = {
            'RESOLVE_TYPE_UNRESOLVED':<?php echo '\'' . SlTaskScheduleCrontabAbnormal::RESOLVE_TYPE_UNRESOLVED .'\'';?>,
            'RESOLVE_TYPE_RESOLVED':<?php echo '\'' . SlTaskScheduleCrontabAbnormal::RESOLVE_TYPE_RESOLVED .'\'';?>,
            'RESOLVE_TYPE_IGNORED':<?php echo '\'' . SlTaskScheduleCrontabAbnormal::RESOLVE_TYPE_IGNORED .'\'';?>
        };



    /**
     * 显示计划任务数据
     * @param  array _rows 任务数组
     * @return
     */
    function showAbnormal( _rows )
    {
    	var _container = $('.abnormal_tables'),
    		_trStr = '',
    		_trLen = _rows.length

    	for(var _i = 0;_i < _trLen;_i++)
    	{
    		_trStr += '<tr msg-id="'+_rows[_i]['id']+'">'
                    + '<td><span class="cell">'+ _rows[_i]['sche_id'] +'</span>'+ '</td>'
                    + '<td><span class="cell">'+ _rows[_i]['cron_id'] +'</span>'+ '</td>'
    				+ '<td><span class="cell">'+ _rows[_i]['name'] +'</span>'+ '</td>'
    				+ '<td><span class="cell">'+ _rows[_i]['add_time'] +'</span>'+ '</td>'

    				+ '<td><span class="cell">'+ _rows[_i]['msg'].substr(0, 62) +'</span>'+ '</td>'
    				+ '<td><span class="cell">'+ abnormalResolveStat[_rows[_i]['resolve_stat']] +'</span>'+ '</td>'


                    if(_rows[_i]['resolve_stat'] == RESOLVE_STAT['RESOLVE_TYPE_UNRESOLVED'])
                    {
                        _trStr += '<td><span class="cell">'
        				+ '<a href="javascript:updateAbnormalStat(\''+<?php echo SlTaskScheduleCrontabAbnormal::RESOLVE_TYPE_RESOLVED;?>+'\', \''+_rows[_i]['id']+'\');" class="a--success">解决</a>'
        				+ '<a href="javascript:updateAbnormalStat(\''+<?php echo SlTaskScheduleCrontabAbnormal::RESOLVE_TYPE_IGNORED;?>+'\', \''+_rows[_i]['id']+'\');" class="a--edit">忽略</a>'
                        + '</span></td>'
                    }
                    else
                    {
                        _trStr += '<td><span class="cell"></span></td>'   
                    }
            _trStr += '</tr>'
    				
    	}
    	_container.find('tr:gt(0)').remove();
    	_container.find('tr:eq(0)').after(_trStr);
    }

    /**
     * 更改计划任务状态
     * @param  string _newStat 要更改的任务计划状态
     * @param  string _id 要更改的任务计划id
     * @return boolean
     */
    function updateAbnormalStat( _newStat, _id)
    {
        var _actStr,
            _updateAbnormal = '/sl/message/update-abnormal',
            _container = $('.abnormal_tables');

        var _updateAbnormalData = {};
        _updateAbnormalData['_csrf'] = csrfToken;

        _updateAbnormalData['id'] = _id;
        _updateAbnormalData['resolve_stat'] = _newStat;

        if( RESOLVE_STAT['RESOLVE_TYPE_IGNORED'] == _newStat)
        {
            _actStr = '忽略';
        }
        else if( RESOLVE_STAT['RESOLVE_TYPE_RESOLVED'] == _newStat)
        {
            _actStr = '已解决';
        }

        $.confirm({
            title: '弹框',
            body: '是否设置消息'+ _id +'状态为'+_actStr+'?',
            okHide: function(){
                $.ajax({
                    crossDomain: true,
                    url: _updateAbnormal,
                    type: 'post',
                    data: _updateAbnormalData,
                    dataType: 'json',
                    success: function (json_data) {
                        
                        if(json_data.code == '0')
                        {
                            _container.find("tr[msg-id='"+_id+"']").find("td:eq(5)").find("span").html(_actStr);
                            _container.find("tr[msg-id='"+_id+"']").find("td:eq(6)").find("span").html('');
                            $.alert('消息'+_id+'设置成功');
                        }
                        else
                        {
                            $.alert('消息'+_id+'设置失败');
                        }

                    }
                });
            }
        })
        
    }

<?php
$this->endBlock();
$this->registerJs($this->blocks['abnormalJs'], \yii\web\View::POS_END);
?>

<div class="block clearfix">
				<div class="section clearfix">
					<span class="title-prefix-md">任务报警消息</span>
				</div>
				<div class="sl-query-wrapper sui-form clearfix">
					<form id="filterFrm" method="POST">
                    <div class="sl-query">
                        <div class="sl-query__label">计划任务ID</div>
                        <div class="sl-query__control">
                            <input type="text" name="sche_id" class="input-medium">
                        </div>
                    </div>
                    <div class="sl-query">
                        <div class="sl-query__label">每日任务ID</div>
                        <div class="sl-query__control">
                            <input type="text" name="cron_id" class="input-medium">
                        </div>
                    </div>
                    <div class="sl-query">
                        <div class="sl-query__label">任务名称</div>
                        <div class="sl-query__control">
                            <input type="text" name="name" class="input-medium">
                        </div>
                    </div>
					<div class="sl-query input-daterange" data-toggle="datepicker">
						<div class="sl-query__label">报警时间</div>
						<div class="sl-query__control">
							<input type="text" name="add_time_s" class="input-medium input-date"><span>-</span>
      						<input type="text" name="add_time_e" class="input-medium input-date">
						</div>
					</div>
                    <div class="sl-query">
                        <div class="sl-query__label">报警内容</div>
                        <div class="sl-query__control">
                            <input type="text" name="msg" class="input-medium">
                        </div>
                    </div>
                    <div class="sl-query">
                        <div class="sl-query__label">处理状态</div>
                        <div class="sl-query__control">
                            <span class="sui-dropdown dropdown-bordered select">
                                    <span class="dropdown-inner">
                                        <a role="button" data-toggle="dropdown" href="#" style="width: 79px;" class="dropdown-toggle">
                                            <input value="" name="resolve_stat" type="hidden">
                                            <i class="caret"></i><span>全部</span>
                                        </a>
                                        <ul role="menu" class="sui-dropdown-menu">
                                            <li role="presentation"> <a role="menuitem" tabindex="-1" href="javascript:void(0);" value="">全部</a> </li>
                                            <li role="presentation"> <a role="menuitem" tabindex="-1" href="javascript:void(0);" value="0">未解决</a> </li>
                                            <li role="presentation"> <a role="menuitem" tabindex="-1" href="javascript:void(0);" value="1">已解决</a> </li>
                                            <li role="presentation"> <a role="menuitem" tabindex="-1" href="javascript:void(0);" value="2">忽略</a> </li>
                                        </ul>
                                    </span>
                            </span>
                        </div>
                    </div>
					<button type="button" class="sui-btn btn-primary fl" style="margin-top: 33px;" onclick="javascript:goToPage(1);">搜索</button>
				</form>
				</div>
				<div class="sl-table-wrapper">
					<table class="sl-table abnormal_tables">
						<tbody><tr class="sl-table__header">
                            <th><span class="cell">计划任务ID</span></th>
							<th><span class="cell">每日任务ID</span></th>
							<th><span class="cell">任务名</span></th>
							<th><span class="cell">报警时间</span></th>
							<th><span class="cell">报警内容</span></th>
							<th><span class="cell">处理状态</span></th>
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