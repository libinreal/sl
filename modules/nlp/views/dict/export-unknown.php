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
                                                    'url' => '/',
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
                    + '<td><span class="cell">'+ _rows[_i]['tag_zh'] +'</span>'+ '</td>'

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

<form class="sui-form form-horizontal">
    <div class="control-group">
        <label class="control-label v-top"><b style="color: #f00;">*</b> 
          任务名称：
        </label>
        <div class="controls">
            <span class="sui-dropdown dropdown-bordered select">
                <span class="dropdown-inner"><a id="drop4" role="button" data-toggle="dropdown" href="#" class="dropdown-toggle">
                    <input value="hz" name="city" type="hidden"><i class="caret"></i><span>杭州</span></a>
                    <ul id="menu4" role="menu" aria-labelledby="drop4" class="sui-dropdown-menu">
                    <li role="presentation"><a role="menuitem" tabindex="-1" href="javascript:void(0);" value="bj">北京</a></li>
                    <li role="presentation"><a role="menuitem" tabindex="-1" href="javascript:void(0);" value="sb">圣彼得堡</a></li>
                    <li role="presentation" class="divider"></li>
                    <li role="presentation" class="active"><a role="menuitem" tabindex="-1" href="javascript:void(0);" value="hz">杭州</a></li>
                    </ul>
                  </span>
            </span>
        </div>
    </div>

      <div class="control-group">
        <label class="control-label"><span style="padding:0 24px 0 0;">日</span> 
          期：
        </label>
        <div class="controls">
          <input type="text" value="" name="date" class="input-xxlarge">
        </div>
      </div>
</form>

