<?php
use yii\helpers\Url;
use yii\helpers\Json;

    $this->title = '词性列表';
    $this->params['breadcrumbs'][] = 'NLP System';
    $this->params['breadcrumbs'][] = '词性列表';
    $this->params['breadcrumbs'][] = $this->title;
    app\assets\NLPAdminAsset::addScript($this, '@web/nlp/JSAjaxFileUploader/JQuery.JSAjaxFileUploader.min.js');
    app\assets\NLPAdminAsset::addCss($this, '@web/nlp/JSAjaxFileUploader/JQuery.JSAjaxFileUploader.css');
    $this->params['breadcrumbs'] = [ 
                                    'items' => [
                                                    [
                                                    'label' => 'Home',
                                                    'url' => '',
                                                    'items' => [
                                                                [
                                                                    'label' => 'Demo',
                                                                    'url' => '/nlp/demo/index'
                                                                ],
                                                                [
                                                                    'label' => 'Dictionary',
                                                                    'url' => '/nlp/dict/index'
                                                                ],
                                                                [
                                                                    'label' => 'Tag',
                                                                    'url' => '/nlp/dict/tag'
                                                                ]
                                                            ]
                                                    ],
                                                    [
                                                    'label' => 'Tag',
                                                    'li_class' => 'current'
                                                    ]
                                                ]
                                ];

    $curPageUrl = Url::current();

 $dataListJs = <<<EOT
    //goToPage(1);
EOT;
$this->registerJs($dataListJs);

    $this->beginBlock('indexJs');
?>
    $('#dicFileUpload').JSAjaxFileUploader({
        uploadUrl:'/nlp/dict/save-dict',
        autoSubmit:false,
        uploadTest:'上传',
        formData:{_csrf: '<?= Yii::$app->request->getCsrfToken() ?>'},
        allowExt: 'xlsx',
        fileName:'excel',
        success:function(r){ $.alert(r.msg)},
        inputText:'选择词性文件'
    });

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
                if(json_data.code != '0')
                {
                    alert(json_data.msg);
                    return;
                }
                var _total = json_data.data.total
                pageCount = Math.ceil(_total / pageSize)
                makePagination(_pageNo, pageCount)//分页

                showDic(json_data.data.rows)//刷新数据

                if(json_data.data.rows.length > 0)
                {
                    var sOff = (_pageNo - 1 ) * pageSize + 1
                    var oOff = (_pageNo - 1 ) * pageSize + json_data.data.rows.length

                    var _summStr = "item" +  sOff + "-" +  oOff + " in " + json_data.data.total + " items"//summary
                    $(".nlp-pagination__text").text( _summStr );

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
            _pageContainer = $('.nlppc__page-nums')



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
                _paginationStr += '<div class="nlp-page-num sui-icon' + _activeStr + '" onclick="goToPage('+_i+')">' + _i + '</div>'
            }

            _paginationStr += '<div class="next"' + _strNext + '><i class="sui-icon icon-caret-right"></i></div>'
                            + '<div class="last"' + _strLast + '><i class="sui-icon icon-step-forward"></i></div>'
            //console.log(' _pos ' +  _pos + ' _pageCount ' + _pageCount + ' _pageNo ' + _pageNo + ' _startPage ' + _startPage + ' _endPage ' + _endPage)
            _pageContainer.html(_paginationStr);
    }

    /**
     * 显示计划任务数据
     * @param  array _rows 任务数组
     * @return
     */
    function showDic( _rows )
    {
        var _container = $('.dict_tag_tables'),
            _trStr = '',
            _trLen = _rows.length

        for(var _i = 0;_i < _trLen;_i++)
        {
            _trStr += '<tr data-id="'+_rows[_i]['id']+'"><td><span class="cell">'+ _rows[_i]['id'] +'</span>'+ '</td>'
                    + '<td><span class="cell">'+ _rows[_i]['tag'] +'</span>'+ '</td>'
                    + '<td><span class="cell">'+ _rows[_i]['parent'] +'</span>'+ '</td>'
                    + '</tr>';
        }
        _container.find('tr:gt(0)').remove();//remove greater than 0 row
        _container.find('tr:eq(0)').after(_trStr);
    }

<?php
$this->endBlock();
$this->registerJs($this->blocks['indexJs'], \yii\web\View::POS_END);
?>



<div id="dicFileUpload">

</div>
<div id="dictList" class="block clearfix">
    <div class="section clearfix">
    <span class="title-prefix-md">词性列表</span>
    </div>

    <div class="nlp-query-wrapper sui-form clearfix">
        <form id="filterFrm" method="POST">
        <div class="nlp-query">
            <div class="nlp-query__label">选择词性表</div>
            <div class="nlp-query__control">
                <span class="sui-dropdown dropdown-bordered select">
                        <span class="dropdown-inner">
                            <a role="button" data-toggle="dropdown" href="#" style="width: 181px;" class="dropdown-toggle">
                                <input value="" name="dic_name" type="hidden">
                                <i class="caret"></i><span>请选择</span>
                            </a>
                            <ul role="menu" class="sui-dropdown-menu">
                                <li role="presentation"> <a role="menuitem" tabindex="-1" href="javascript:void(0);" value="">请选择</a> </li>
                                <?php
                                    foreach ($dictList as $d) 
                                    {
                                        echo '<li role="presentation"> <a role="menuitem" tabindex="-1" href="javascript:void(0);" value="' . $d .'">' . $d .'</a> </li>';
                                    }
                                    
                                ?>
                            </ul>
                        </span>
                </span>
            </div>
        </div>
        <div class="nlp-query">
            <div class="nlp-query__label">词性</div>
            <div class="nlp-query__control">
                <input type="text" name="tag" class="input-medium">
            </div>
        </div>
        <button type="button" class="sui-btn btn-primary fl" style="margin-top: 33px;" onclick="javascript:goToPage(1);">搜索</button>

        <button type="button" class="sui-btn btn-primary fl" style="margin-left:10px;margin-top: 33px;" onclick="javascript:exportUnknown(1);">导出词性</button>
    </form>
    </div>

    <div class="nlp-table-wrapper">
        <table class="nlp-table dict_tag_tables">
            <tbody>
                <tr class="sl-table__header">
                    <th><span class="cell">ID</span></th>
                    <th><span class="cell">标签</span></th>
                    <th><span class="cell">上级标签</span></th>
                </tr>
            </tbody>
        </table>
        <div class="nlp-pagination">
            <div class="nlp-pagination__control">
                <div class="nlppc__page-nums clearfix fl"><div class="first"><i class="sui-icon icon-step-backward"></i></div><div class="prev"><i class="sui-icon icon-caret-left"></i></div><div class="nlp-page-num sui-icon is-active" onclick="goToPage(1)">1</div><div class="nlp-page-num sui-icon" onclick="goToPage(2)">2</div><div class="next" onclick="goToPage(2);"><i class="sui-icon icon-caret-right"></i></div><div class="last" onclick="goToPage(2);"><i class="sui-icon icon-step-forward"></i></div></div>
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
            <div class="nlp-pagination__text">item1-10 in 15 items</div><!-- item1-10 in 213 items -->
        </div>
    </div>
</div>

