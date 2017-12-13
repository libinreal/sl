<?php
use yii\helpers\Url;
use yii\helpers\Json;
use app\models\nlp\NlpEngineTaskItem;

    $this->title = '任务列表';
    $this->params['breadcrumbs'][] = 'NLP System';
    $this->params['breadcrumbs'][] = '任务列表';
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
                                                                ],
                                                                [
                                                                    'label' => 'Task',
                                                                    'url' => '/nlp/dict/task'
                                                                ]
                                                            ]
                                                    ],
                                                    [
                                                    'label' => 'Task',
                                                    'li_class' => 'current'
                                                    ]
                                                ]
                                ];

    $curPageUrl = Url::current();

 $dataListJs = <<<EOT
    goToPage(1);
EOT;
$this->registerJs($dataListJs);

    $this->beginBlock('taskJs');
?>
    (function($){
    var ModalBuilder = function(selector, options, data){
        this._currentZIndex = 1000;
        this._modalClass = null;
        this.selector = selector;
        this.data = data;
        this.options = {
                title:'提示',//标题
                titlebgColor:'',//标题背景颜色
                containerWidth:'',//容器宽度百分比
                maxWidth : 0.7,
                minWidth : 0.2,
                contentheight:'',//内容高度
                maxHeight : 0.8,
                minHeight: 0.2  ,
        };
        $.extend(true, this.options, options);
        this.modal();
    }
    ModalBuilder.prototype = {
        modal:function(){
            this._creatElemHtml();
            $('.'+ this._modalClass).modal();
            var that = this;
            if(this.options.shown){
                $('.'+ this._modalClass).on('shown', function(e){
                    that.options.shown();
                });
            }
            if(this.options.okHide){
                $('.'+ this._modalClass).on('okHide', function(e){
                    that.options.okHide();
                });
            }
            if(this.options.cancelHide){
                $('.'+ this._modalClass).on('cancelHide', function(e){
                    that.options.cancelHide();
                });
            }

            if(this.options.hidden){
                $('.'+ this._modalClass).on('hidden', function(e){
                    that.options.hidden();
                    $(this).remove();
                });
            }else{
                $('.'+ this._modalClass).on('hidden', function(e){
                    $(this).remove();
                });
            }

            if(!this.options.containerWidth){//弹层自定义宽度展示后获取宽度值
                $('.'+ this._modalClass).css('width','auto');
                var objwidth = $('.'+ this._modalClass).width();
                $('.'+ this._modalClass).css({'margin-left':'-'+(objwidth/2) + 'px','left':'50%'});
            }
            /** 拖拽模态框*/
            this._drapModal();
            $(".sui-modal-backdrop:last").css("z-index",this._currentZIndex);
            this._currentZIndex++;
            $(".sui-modal:last").css("z-index",this._currentZIndex);
            this._currentZIndex++;
        },
        _creatElemHtml : function(){
            this._modalClass='modal_'+new Date().valueOf();
            var bodyWidth = document.documentElement.clientWidth;
            var bodyHeight = document.documentElement.clientHeight;
            $('body').append(template(this.selector, this.data));
            $('body div[role="dialog"]:last').addClass(this._modalClass);
            $('.'+ this._modalClass + " .modal-body").css({'max-width':this.options.maxWidth*bodyWidth + 'px','min-width':this.options.minWidth*bodyWidth +'px'});
            $('.'+ this._modalClass + " .modal-header h4").text(this.options.title);
            $('.'+ this._modalClass + " .modal-header").css('background',this.options.titlebgColor);
            $('.'+ this._modalClass + " .modal-body").css({'max-height':this.options.maxHeight*bodyHeight+'px','min-height':this.options.minHeight*bodyHeight + 'px'});
            if(this.options.contentheight && this.options.contentheight !='auto'){
                if(this.options.contentheight > this.options.maxHeight){
                    $('.'+ this._modalClass + " .modal-body").css({'height':this.options.maxHeight*bodyHeight});
                }else{
                    $('.'+ this._modalClass + " .modal-body").css({'height':this.options.contentheight*bodyHeight});
                }

            }else{
                $('.'+ this._modalClass + " .modal-body").css({'height':'auto'});
            }
            if(this.options.containerWidth && this.options.containerWidth !='auto'){
                if(this.options.containerWidth > this.options.maxWidth){
                    $('.'+ this._modalClass).css({'width':this.options.maxWidth*bodyWidth + 'px','margin-left':'-'+(this.options.maxWidth*bodyWidth/2) + 'px','left':'50%'});
                }else{
                    $('.'+ this._modalClass).css({'margin-left':'-'+(this.options.containerWidth*bodyWidth/2) + 'px','width':this.options.containerWidth*bodyWidth + 'px','left':'50%'});
                }
            }

        },
        _drapModal:function(){
            var p={};
            function getXY(eve) {
                var scrollTop = document.documentElement.scrollTop || document.body.scrollTop;
                var scrollLeft = document.documentElement.scrollLeft || document.body.scrollLeft;
                return {x : scrollLeft + eve.clientX,y : scrollTop + eve.clientY };
            }

            $(document).on("mouseup",function(ev){
                p={};
                $(document).off("mousemove");
            });

            $(".modal-header:last").on("mousedown",function(ev){
                document.body.onselectstart=document.body.ondrag=function(){
                    return false;
                }
                p.y = ev.pageY - $(this).parents(".sui-modal")[0].offsetTop;
                p.x = ev.pageX - $(this).parents(".sui-modal")[0].offsetLeft;

                $(document).on("mousemove",function(ev){
                    var oEvent = ev || event;
                    var pos = getXY(oEvent);
                    $(".sui-modal:last").css({left:(pos.x-p.x) + "px",top:(pos.y-p.y) + "px","margin-left":"10px","margin-top":"10px"});
                });
            });
            $(document).on('hidden.bs.modal','.modal',function(e){
                $('.modal-dialog').css({'top': '0px','left': '0px'})
                document.body.onselectstart=document.body.ondrag=null;
            });
        },
        resize:function(){
            var w = 0-$('.'+ this._modalClass).width()/2;
            var h = 0-$('.'+ this._modalClass).height()/2;
            $('.'+ this._modalClass).css({"margin-top":h+"px","margin-left":w+"px"});
        }
    }
    if ( typeof module != 'undefined' && module.exports ) {
        module.exports = treeBuilder;
    } else {
        window.ModalBuilder = ModalBuilder;
    }
})(jQuery)

    $('#dicFileUpload').JSAjaxFileUploader({
        uploadUrl:'/nlp/dict/save-filter-char',
        autoSubmit:false,
        uploadTest:'上传',
        formData:{_csrf: '<?= Yii::$app->request->getCsrfToken() ?>'},
        allowExt: 'txt',
        fileName:'txt',
        success:function(r){ $.alert(r.msg)},
        inputText:'选择过滤字符文件'
    });

    var pageNo = 1, pageSize = 10, pageCount = 0,
        paginationLen = 5, refreshUrl = "<?= $curPageUrl ?>";

    var taskItemStatArr =<?php echo Json::encode([
        NlpEngineTaskItem::STATUS_READY => '待执行',
        NlpEngineTaskItem::STATUS_EXECUTING => '执行中',
        NlpEngineTaskItem::STATUS_COMPLETE => '执行完毕',
    ]) ?>;

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
                    $.alert(json_data.msg);
                    return;
                }
                var _total = json_data.data.total
                pageCount = Math.ceil(_total / pageSize)
                makePagination(_pageNo, pageCount)//分页

                showTaskItem(json_data.data.rows)//刷新数据

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
     * 新增nlp任务
     * @return
     */
    function addTask()
    {
        new ModalBuilder('template1', {
                        contentheight:0.3,
                        title: '新增nlp任务',
                        
                        okHide: function(){

                            var _data = $("form[name='add_task']").find("input").serializeObject();
                            _data['_csrf'] = csrfToken;

                            $.ajax({
                                url: '/nlp/dict/add-task-item',
                                type: 'post',
                                data: _data,
                                dataType: 'json',
                                success: function(json_data){
                                    $.alert(json_data.msg);
                                    
                                    if(json_data.code == '0')
                                    {
                                        goToPage(pageNo);
                                    }
                                    
                                }
                            });
                        }
        });

    }

    /**
     * 显示nlp引擎任务数据
     * @param  array _rows 任务数组
     * @return
     */
    function showTaskItem( _rows )
    {
        var _container = $('.dict_tag_tables'),
            _trStr = '',
            _trLen = _rows.length

        for(var _i = 0;_i < _trLen;_i++)
        {
            _trStr += '<tr data-id="'+_rows[_i]['id']+'"><td><span class="cell">'+ _rows[_i]['id'] +'</span>'+ '</td>'
                    + '<td><span class="cell">'+ _rows[_i]['cmd_name'] +'</span>'+ '</td>'
                    + '<td><span class="cell">'+ _rows[_i]['param_list'].substr(0, 62) +'</span>'+ '</td>'
                    + '<td><span class="cell">'+ taskItemStatArr[_rows[_i]['status']] +'</span>'+ '</td>'
                    + '<td><span class="cell">'+ _rows[_i]['update_time'] +'</span>'+ '</td>'
                    + '</tr>';
        }
        _container.find('tr:gt(0)').remove();//remove greater than 0 row
        _container.find('tr:eq(0)').after(_trStr);
    }

/**
 * 导出词库
 * @param 
 */
function exportTag()
{
    new ModalBuilder('template3', {
        title: '导出分词结果',
        okHide: function(){
            var _o = $("form[name='export_unknown_excel']").find("input[name='modal_offset']").val()
            var _l = $("form[name='export_unknown_excel']").find("input[name='modal_limit']").val()
            var _date = $("form[name='export_unknown_excel']").find("input[name='start_date']").val()
            var _n = $("form[name='export_unknown_excel']").find("input[name='name']").val()
            location.href = '/nlp/dict/export-unknown?start_date='+ _date + '&name=' + _n +'&o=' + _o + '&l=' + _l;
        }
    });

}

function exportSpiderDict()
{
    new ModalBuilder('template2', {
        title: '导出采集词库',
        okHide: function(){
            var _o = $("form[name='export_spider_dict_excel']").find("input[name='modal_offset']").val()
            var _l = $("form[name='export_spider_dict_excel']").find("input[name='modal_limit']").val()
            location.href = '/nlp/dict/export-spider-word?o=' + _o + '&l=' + _l;
        }
    });
}

<?php
$this->endBlock();
$this->registerJs($this->blocks['taskJs'], \yii\web\View::POS_END);
app\assets\NLPAdminAsset::addScript($this, '@web/sl/lib/template/template.js');
?>



<div id="dicFileUpload">

</div>
<div id="dictList" class="block clearfix">
    <div class="section clearfix">
    <span class="title-prefix-md">任务列表</span>
    <div class="nlp-add-text fr" onclick="javascript:addTask();">添加nlp任务</div>
    </div>

    <div class="nlp-query-wrapper sui-form clearfix">
        <form id="filterFrm" method="POST">
        <div class="nlp-query">
            <div class="nlp-query__label">任务参数</div>
            <div class="nlp-query__control">
                <input type="text" name="param_list" class="input-medium">
            </div>
        </div>
        <div class="nlp-query">
            <div class="nlp-query__label">任务类型</div>
            <div class="nlp-query__control">
                <span class="sui-dropdown dropdown-bordered select">
                        <span class="dropdown-inner">
                            <a role="button" data-toggle="dropdown" href="#" style="width: 181px;" class="dropdown-toggle">
                                <input value="" name="cmd" type="hidden">
                                <i class="caret"></i><span>全部</span>
                            </a>
                            <ul role="menu" class="sui-dropdown-menu">
                                <li role="presentation"> <a role="menuitem" tabindex="-1" href="javascript:void(0);" value="">全部</a> </li>
                                <li role="presentation"> <a role="menuitem" tabindex="-1" href="javascript:void(0);" value="import-mysql">词库灌入</a> </li>
                                <li role="presentation"> <a role="menuitem" tabindex="-1" href="javascript:void(0);" value="tag">标题分词</a> </li>
                            </ul>
                        </span>
                </span>
            </div>
        </div>
        <div class="nlp-query">
            <div class="nlp-query__label">任务状态</div>
            <div class="nlp-query__control">
                <span class="sui-dropdown dropdown-bordered select">
                        <span class="dropdown-inner">
                            <a role="button" data-toggle="dropdown" href="#" style="width: 79px;" class="dropdown-toggle">
                                <input value="" name="status" type="hidden">
                                <i class="caret"></i><span>全部</span>
                            </a>
                            <ul role="menu" class="sui-dropdown-menu">
                                <li role="presentation"> <a role="menuitem" tabindex="-1" href="javascript:void(0);" value="">全部</a> </li>
                                <li role="presentation"> <a role="menuitem" tabindex="-1" href="javascript:void(0);" value="0">待执行</a> </li>
                                <li role="presentation"> <a role="menuitem" tabindex="-1" href="javascript:void(0);" value="1">执行中</a> </li>
                                <li role="presentation"> <a role="menuitem" tabindex="-1" href="javascript:void(0);" value="2">已完成</a> </li>
                            </ul>
                        </span>
                </span>
            </div>
        </div>
        <div class="nlp-query input-daterange" data-toggle="datepicker">
            <div class="nlp-query__label">更新时间</div>
            <div class="nlp-query__control">
                <input type="text" name="update_time_s" class="input-medium input-date"><span>-</span>
                <input type="text" name="update_time_e" class="input-medium input-date">
            </div>
        </div>
        
        <button type="button" class="sui-btn btn-primary fl" style="margin-top: 33px;" onclick="javascript:goToPage(1);">搜索</button>

        <button type="button" class="sui-btn btn-primary fl" style="margin-left:10px;margin-top: 33px;" onclick="javascript:exportSpiderDict();">导出采集词库</button>

        <button type="button" class="sui-btn btn-primary fl" style="margin-left:10px;margin-top: 33px;" onclick="javascript:exportTag();">导出分词结果</button>
    </form>
    </div>

    <div class="nlp-table-wrapper">
        <table class="nlp-table dict_tag_tables">
            <tbody>
                <tr class="sl-table__header">
                    <th><span class="cell">ID</span></th>
                    <th><span class="cell">任务类型</span></th>
                    <th><span class="cell">相关参数</span></th>
                    <th><span class="cell">任务状态</span></th>
                    <th><span class="cell">更新时间</span></th>
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

<script id="template1" type="text/html">
    <div id="myModal1" tabindex="-1" role="dialog" data-hasfoot="false" class="sui-modal hide fade">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h4 id="myModalLabel" class="modal-title">Modal title111</h4>
          </div>
          <div class="modal-body">
                <form name="add_task">
                <div class="nlp-category-wrapper sui-form clearfix">
                    <div class="nlp-query">
                        <div class="nlp-query__label">任务类型</div>
                        <div class="nlp-query__control">
                            <span class="sui-dropdown dropdown-bordered select">
                                <span class="dropdown-inner">
                                    <a role="button" data-toggle="dropdown" href="#" style="width: 181px;" class="dropdown-toggle">
                                        <input value="" name="cmd" type="hidden">
                                        <i class="caret"></i><span>全部</span>
                                    </a>
                                    <ul role="menu" class="sui-dropdown-menu">
                                        <li role="presentation"> <a role="menuitem" tabindex="-1" href="javascript:void(0);" value="">全部</a> </li>
                                        <li role="presentation"> <a role="menuitem" tabindex="-1" href="javascript:void(0);" value="import-mysql">词库灌入</a> </li>
                                        <li role="presentation"> <a role="menuitem" tabindex="-1" href="javascript:void(0);" value="tag">标题分词</a> </li>
                                    </ul>
                                </span>
                            </span>
                        </div>
                    </div>
                    <div class="nlp-query">
                        <div class="nlp-query__label">任务参数</div>
                        <div class="nlp-query__control">
                            <input type="text" name="params" class="input-medium">
                        </div>
                    </div>
                </div>
                </form>
          </div>
          <div class="modal-footer">
            <button type="button" data-ok="modal" class="sui-btn btn-primary btn-borderadius nlp-btn--md">提交</button>
            <button type="button" data-dismiss="modal" class="sui-btn btn-borderadius nlp-btn--md" style="margin-left: 80px;">取消</button>
          </div>
        </div>
      </div>
    </div>
</script>

<script id="template2" type="text/html">
    <div id="myModal2" tabindex="-1" role="dialog" data-hasfoot="false" class="sui-modal hide fade">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h4 id="myModalLabel" class="modal-title">Modal title111</h4>
          </div>
          <div class="modal-body">
                <form name="export_spider_dict_excel">
                    <div class="nlp-category-wrapper sui-form clearfix">
                        <div class="nlp-query">
                            <div class="nlp-query__label">起始值</div>
                            <div class="nlp-query__control">
                                <input type="text" name="modal_offset" class="input-medium">
                            </div>
                        </div>
                        <div class="nlp-query">
                            <div class="nlp-query__label">总数量</div>
                            <div class="nlp-query__control">
                                <input type="text" name="modal_limit" class="input-medium">
                            </div>
                        </div>
                    </div>
                </form>
          </div>
          <div class="modal-footer">
            <button type="button" data-ok="modal" class="sui-btn btn-primary btn-borderadius nlp-btn--md">确定</button>
            <button type="button" data-dismiss="modal" class="sui-btn btn-borderadius nlp-btn--md" style="margin-left: 80px;">取消</button>
          </div>
        </div>
      </div>
    </div>
</script>

<script id="template3" type="text/html">
    <div id="myModal3" tabindex="-1" role="dialog" data-hasfoot="false" class="sui-modal hide fade">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h4 id="myModalLabel" class="modal-title">Modal title111</h4>
          </div>
          <div class="modal-body">
                <form name="export_unknown_excel">
                    <div class="nlp-category-wrapper sui-form clearfix">
                        <div class="nlp-query">
                            <div class="nlp-query__label">采集任务名</div>
                            <div class="nlp-query__control">
                                <input type="text" name="name" class="input-medium">
                            </div>
                        </div>
                        <div class="nlp-query input-daterange" data-toggle="datepicker">
                            <div class="nlp-query__label">采集开始时间</div>
                            <div class="nlp-query__control">
                                <input type="text" name="start_date" class="input-medium input-date">
                            </div>
                        </div>
                        <div class="nlp-query">
                            <div class="nlp-query__label">起始值</div>
                            <div class="nlp-query__control">
                                <input type="text" name="modal_offset" class="input-medium">
                            </div>
                        </div>
                        <div class="nlp-query">
                            <div class="nlp-query__label">总数量</div>
                            <div class="nlp-query__control">
                                <input type="text" name="modal_limit" class="input-medium">
                            </div>
                        </div>
                    </div>
                </form>
          </div>
          <div class="modal-footer">
            <button type="button" data-ok="modal" class="sui-btn btn-primary btn-borderadius nlp-btn--md">确定</button>
            <button type="button" data-dismiss="modal" class="sui-btn btn-borderadius nlp-btn--md" style="margin-left: 80px;">取消</button>
          </div>
        </div>
      </div>
    </div>
</script>