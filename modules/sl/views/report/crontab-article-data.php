<?php
use app\modules\sl\models\SlTaskScheduleCrontabAbnormal;
use yii\helpers\Url;
use yii\helpers\Json;

    $this->title = 'Wechat Report';
    /*$this->params['breadcrumbs'][] = 'SL System';
    $this->params['breadcrumbs'][] = '计划任务列表';
    $this->params['breadcrumbs'][] = $this->title;*/
    $curPageUrl = Url::current();
    $this->beginBlock('reportJs');
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
        if(!filterData.name || !filterData.start_time_s)
        {
            $.alert('任务名称和日期不能为空')
            return;
        }

    	filterData['pageNo'] = _pageNo
    	filterData['pageSize'] = pageSize
        filterData['_csrf'] = csrfToken

    	filterData['data_type'] = 'article'

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

            	showReport(json_data.data.rows)//刷新数据

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

    /**
     * 显示每日任务数据报表
     * @param  array _rows 任务数组
     * @return
     */
    function showReport( _rows )
    {
    	var _container = $('.report_tables'),
    		_trStr = '',
    		_trLen = _rows.length

    	for(var _i = 0;_i < _trLen;_i++)
    	{
    		_trStr += '<tr msg-id="'+_rows[_i]['id']+'">'
                    + '<td><span class="cell">'+ _rows[_i]['pf_name'] +'</span>'+ '</td>'
                    + '<td><span class="cell">'+ _rows[_i]['keyword'] +'</span>'+ '</td>'
    				+ '<td><span class="cell">'+ _rows[_i]['number'] +'</span>'+ '</td>'
                    + '</tr>'    				
    	}
    	_container.find('tr:gt(0)').remove();
    	_container.find('tr:eq(0)').after(_trStr);
    }

<?php
$this->endBlock();
$this->registerJs($this->blocks['reportJs'], \yii\web\View::POS_END);
?>

<div class="block clearfix">
				<div class="section clearfix">
					<span class="title-prefix-md">微信每日任务数据报表</span>
				</div>
				<div class="sl-query-wrapper sui-form clearfix">
					<form id="filterFrm" method="POST">
                    <div class="sl-query">
                        <div class="sl-query__label">任务名称</div>
                        <div class="sl-query__control">
                            <input type="text" name="name" class="input-medium">
                        </div>
                    </div>
					<div class="sl-query input-daterange" data-toggle="datepicker">
						<div class="sl-query__label">任务日期</div>
						<div class="sl-query__control">
							<input type="text" name="start_time_s" class="input-medium input-date">
						</div>
					</div>
                    
					<button type="button" class="sui-btn btn-primary fl" style="margin-top: 33px;" onclick="javascript:reportSearch();">搜索</button>
				</form>
				</div>
				<div class="sl-table-wrapper">
					<table class="sl-table report_tables">
						<tbody><tr class="sl-table__header">
                            <th><span class="cell">渠道名称</span></th>
							<th><span class="cell">关键字</span></th>
							<th><span class="cell">采集记录数</span></th>
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