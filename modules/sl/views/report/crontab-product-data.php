<?php
use app\modules\sl\models\SlTaskScheduleCrontabAbnormal;
use yii\helpers\Url;
use yii\helpers\Json;

    $this->title = 'Product Report';
    /*$this->params['breadcrumbs'][] = 'SL System';
    $this->params['breadcrumbs'][] = '计划任务列表';
    $this->params['breadcrumbs'][] = $this->title;*/
    $curPageUrl = Url::current();

$menuFontCss = <<<EOT
    .sui-btn-group .sui-btn{
        min-width:43px;
    }

    .switch-format{
        float:right;
        margin-right:26px;
    }
    
    .switch-format button{
        background-color:#fff;
        margin-top:33px;
    }

    .sl-diagram-wrapper{
        height:460px;
        display:none;
    }

    #barTotal{
        float:left;
        height:460px;
        margin-bottom:26px;
    }

    #pies{
        float:right;
        height:460px;
        margin-bottom:26px;
        margin-right:36px;
    }

    .diagram-title{
        width: 100%; 
        height: 30px;
        margin-top: 30px;
        margin-bottom:-10px;
    }
    .diagram-title span{
        font-size:16px;        
        text-align:center;
        color:#672A7A;
    }
EOT;
$this->registerCss($menuFontCss);

$this->beginBlock('reportJs');
?>
    var pageNo = 1, pageSize = 10, pageCount = 0,
    	paginationLen = 5, refreshUrl = "<?= $curPageUrl ?>",
        dataFormat = 'list',
        pieTotalArr = [];
    var barTotal;

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

    	filterData['data_type'] = 'product'

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
                    + '<td><span class="cell">'+ _rows[_i]['brand_name'] +'</span>'+ '</td>'
    				+ '<td><span class="cell">'+ _rows[_i]['number'] +'</span>'+ '</td>'
                    + '</tr>'    				
    	}
    	_container.find('tr:gt(0)').remove();
    	_container.find('tr:eq(0)').after(_trStr);
    }

    /**
     * 显示图表数据
     * @return 
     */
    function goToDiagram()
    {
        var filterData = $("#filterFrm").find("input").serializeObject();
        if(!filterData.name || !filterData.start_time_s)
        {
            $.alert('任务名称和日期不能为空')
            return;
        }

        filterData['pageNo'] = 0
        filterData['pageSize'] = 0
        filterData['_csrf'] = csrfToken

        filterData['data_type'] = 'product'

        $.ajax({
            crossDomain: true,
            url: refreshUrl,
            type: 'post',
            data: filterData,
            dataType: 'json',
            success: function (json_data) {
                var _pbk, _pbbk, _pfObj = {}, _pfNameArr = [],
                    _pfk = '',
                    _brand = '',
                    _pfNumberObj = {}, _pfValueArr = [],
                    _pfBrandArr = [];

                var _width = $('.sl-diagram-wrapper').width(),
                    _height = $('.sl-diagram-wrapper').height();

                $('#barTotal').css({
                    'width': (_width * 0.3) + 'px'
                })
                $('#pies').css({
                    'width': (_width * 0.6) + 'px'
                })
                
                //init bar
                if(!barTotal)
                    barTotal = echarts.init(document.getElementById('barTotal'));

                for(var _rk in json_data.data.rows)
                {
                    _pfk = json_data.data.rows[_rk].pf_name;
                    _brand = json_data.data.rows[_rk].brand_name;

                    if( !_pfObj[_pfk] )
                    {
                        //pf number
                        _pfNumberObj[_pfk] = 0

                        //pf - brand
                        _pfObj[_pfk] = {};
                    }

                    //pf - brand - number
                    if(!_pfObj[_pfk][_brand])
                    {
                        _pfObj[_pfk][_brand] = 0;
                    }

                    //pf number
                    _pfNumberObj[_pfk] += parseFloat(json_data.data.rows[_rk].number);

                    //pf - brand
                    _pfObj[_pfk][_brand] += parseFloat(json_data.data.rows[_rk].number);
                }

                //get pf total number
                _pbk = 0;
                _pbColorArr = ['#F18276','#FF808A'];
                _pbbColorArr = ['#CB9F50','#F1951C', '#D96007', '#EC9E7E', '#AC5FCA', '#EC468C', '#DC3C52', '#C94742',
                                '#D86756', '#CB8194', '#866733', '#85530F', '#893C04', '#8A5440', '#855595', '#8A2852', '#892632',
                                '#862F2C', '#833F34', '#8C5866'];

                for(var _fK in _pfObj)
                {
                    //pf total
                    _pfNameArr.push({'value':_fK, 'textStyle':{'color':'#672A7A', 'fontSize':16}});
                    _pfValueArr.push({
                        'value':_pfNumberObj[_fK],
                        'itemStyle':{'normal':{'color':_pbColorArr[_pbk]}}
                    });

                    _pfBrandArr.push([]);

                    _pbbk = 0;
                    //pf - brand total
                    for(var _b in _pfObj[_fK])
                    {
                        _pfBrandArr[_pbk].push({
                                'name':_b,
                                'value':_pfObj[_fK][_b],
                                'itemStyle':{'normal':{'color':_pbbColorArr[_pbbk]}}
                        });
                        _pbbk++;
                    }

                    _pbk++;
                }

                //remove and dispose old pie charts and dom
                for(var _ek in pieTotalArr)
                {
                    pieTotalArr[_ek].dispose();
                }
                $('#pies').html('');
                pieTotalArr = [];

                //bar picture render
                barTotal.setOption({
                    /* title: {
                        text: '渠道采集量',
                    },
                    tooltip : {
                        trigger: 'axis',
                        axisPointer : {            // 坐标轴指示器，坐标轴触发有效
                            type : 'shadow'        // 默认为直线，可选为：'line' | 'shadow'
                        },
                        formatter: function (params) {
                            var tar = params[1];
                            return tar.name + '<br/>' + tar.seriesName + ' : ' + tar.value;
                        }
                    }, */
                    grid: {
                        left: '3%',
                        right: '4%',
                        bottom: '3%',
                        top: '3%',
                        containLabel: true
                    },
                    xAxis: {
                        type : 'category',
                        splitLine: {show:false},
                        data : _pfNameArr
                    },
                    yAxis: {
                        type : 'value'
                    },
                    series: [
                        {
                            name: '采集量',
                            type: 'bar',
                            label: {
                                normal: {
                                    show: true,
                                    position: 'top'
                                }
                            },
                            barWidth:82,
                            data:_pfValueArr
                        }
                    ]
                });

                //pie picture render
                var _pie, _pieDom, _pieId
                for(var _bk in _pfBrandArr)
                {
                    _pieDom = document.createElement("div");
                    _pieId = "pie-"+_bk
                    _pieDom.setAttribute("id", _pieId);

                    _pieDom.style.width = (_width * 0.6 / _pfBrandArr.length) + "px"
                    _pieDom.style.height = _height + "px"

                    _pieDom.style.float = "left"

                    //init pie
                    document.getElementById("pies").appendChild(_pieDom);
                    _pie = echarts.init(_pieDom);
                    pieTotalArr.push(_pie);

                    _pie.setOption({
                        title: {'text':_pfNameArr[_bk]['value'],
                                'bottom':'1%',
                                'left':'47%',
                                'textStyle':{'color':'#672A7A','fontSize':16,'fontWeight':'normal', 'align':'center'}
                                },
                        tooltip : {
                            trigger: 'item',
                            formatter: "{b} : {c} ({d}%)"
                        },
                        series : [
                            {
                                type: 'pie',
                                radius : '66%',
                                center: ['50%', '50%'],
                                data: _pfBrandArr[_bk],
                                label: {
                                    normal: {
                                        show: true,
                                        //position: 'inner',
                                        formatter: function(params){
                                
                                            return params.name + '\r\n' + params.value;
                                        }
                                    }
                                },
                                itemStyle: {
                                    emphasis: {
                                        shadowBlur: 10,
                                        shadowOffsetX: 0,
                                        shadowColor: 'rgba(0, 0, 0, 0.5)'
                                    }
                                }
                            }
                        ]
                    });
                }
            }
        });
    }

    /**
     *刷新数据
     */
    function reportSearch()
    {
        if(dataFormat == 'list')
        {
            goToPage(1);
        }
        else
        {
            goToDiagram();
        }
    }

    /**
     * 切换数据展示形式
     * @param button 所点击的按钮
     * @return
     */
     function toggleDataFormat(btn)
     {
        _btn = $(btn)
        _format = _btn.attr('data-id');

        if(dataFormat == _format)
        {
            return;
        }

        //button refresh
        var _tempBtn
        _btn.parent().children().each(function(){

            _tempBtn = $(this)
            //当前点击按钮 高亮显示
            if(_tempBtn.attr('data-id') == _format)
            {
                _tempBtn.css({
                    'background-color':'#672A7A',
                    'color':'#fff'
                });
            }
            else
            {
                _tempBtn.css({
                    'background-color':'#fff',
                    'color':'#672A7A'
                });
            }
        })

        //hide other container
        $('.sl-data-container').hide();

        if (_format == 'list')
        {
            $('.sl-table-wrapper').show();
            goToPage(1);
        }
        else if(_format == 'diagram')
        {
            $('.sl-diagram-wrapper').show();
            goToDiagram();
        }

        dataFormat = _format;
        return;
     }

<?php
$this->endBlock();
$this->registerJs($this->blocks['reportJs'], \yii\web\View::POS_END);
app\assets\SLAdminAsset::addScript($this, '@web/admin/js/echarts.common.min.js');
?>

<div class="block clearfix">
				<div class="section clearfix">
					<span class="title-prefix-md">产品每日任务数据报表</span>
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
                    <div class="sui-btn-group switch-format">
                    <button type="button" class="sui-btn btn-primary" data-id="list" style="margin-top: 33px;background-color:#672A7A;color:#fff;" onclick="javascript:toggleDataFormat(this);">列表</button>
                    <button type="button" class="sui-btn btn-primary" data-id="diagram" style="margin-top: 33px;background-color:#fff;color:#672A7A;" onclick="javascript:toggleDataFormat(this);">图表</button>
                    </div>
				</div>

                <!-- list start -->
				<div class="sl-table-wrapper sl-data-container">
					<table class="sl-table report_tables">
						<tbody><tr class="sl-table__header">
                            <th><span class="cell">渠道名称</span></th>
							<th><span class="cell">品牌名称</span></th>
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
                <!-- list start -->

                <!-- diagram start -->
                <div class="sl-diagram-wrapper sl-data-container">
                    <div class="diagram-title" style="width: 100%; height: 30px;">
                        <span style="width:544px;float: left; font-size:16px">渠道采集量</span>
                        <span style="width:1090px;float: right; font-size:16px">品牌采集量</span>
                    </div>

                    <div class="clearfix">
                    </div>

                    <div class="bar-total" id="barTotal">
                    </div>
                    <div id="pies">
                    </div>
                </div>
                <!-- diagram end -->
			</div>