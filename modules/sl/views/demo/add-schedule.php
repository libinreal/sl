<?php
use app\modules\sl\models\SlTaskSchedule;
use yii\helpers\Url;
use yii\helpers\Json;

$this->title = '新增计划任务';
$this->beginBlock("addScheJs");
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
				minHeight: 0.2	,
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

var modal1 = null,
	modal2 = null;
	function editCategory(){

		$.ajax({
        url: '/sl/demo/class-brand-manage',
        type: 'post',
        data: {_csrf:csrfToken},
        dataType: 'json',
        success: function (json_data) {
	        	if(json_data.code == '0')
	        	{
        			new ModalBuilder('template1', {
						title: '新增分类',
						shown: function(){

						},
						okHide: function(){

						},
						cancelHide: function(){

						},
					}, json_data.data);
	        	}

	        }
	    });


	}
	function editBrand(){
		$.ajax({
        url: '/sl/demo/brand-class-manage',
        type: 'post',
        data: {_csrf:csrfToken},
        dataType: 'json',
        success: function (json_data) {
	        	if(json_data.code == '0')
	        	{
        			new ModalBuilder('template2', {
						title: '新增分类',
						shown: function(){

						},
						okHide: function(){

						},
						cancelHide: function(){

						},
					}, json_data.data);
	        	}

	        }
	    });
	}
	$(".sl-icon-trash").on('click',function(){
		$.confirm({
			title: '弹框',
			body: '是否删除?',
			okHide: function(){
				$.alert({
					title: '提示弹框',
					body: '点击了确定',
				});
			},
			cancelHide: function(){
				$.alert('点击了取消')
			}
		})
	})



/**
 * 保存渠道设置
 * @param  string pk渠道键名
 * @return void
 */
function savePf( pk ){
	var pfData = $("#"+pk).find("input").serializeObject();
	pfData = $.extend({}, pfData, {pk:pk, _csrf:csrfToken});

	$.ajax({
        url: '/sl/demo/update-schedule-settings',
        type: 'post',
        data: pfData,
        dataType: 'json',
        success: function (json_data) {
        	if(json_data.code == '0')
        	{
        		alert('保存成功');
        	}

        }
    });
}

/**
 * 更改重复执行的周期触发
 * @param _e input表单元素
 * @return {[type]} [description]
 */
function onChangeRepeat(_e)
{
	var repeat_type = $(_e).val()
	if(repeat_type == 2)//2 每天
	{
		$('#sche_week_tags').hide();
		$('#sche_month_tags').hide();
	}
	else if(repeat_type == 3)//3 每月
	{
		$('#sche_week_tags').hide();
		$('#sche_month_tags').show();
	}
	else if(repeat_type == 4)//4 每周
	{
		$('#sche_week_tags').show();
		$('#sche_month_tags').hide();
	}
}

/**
 * 获取最新的产品分类列表
 * @return void
 */
function getProductClass()
{
	$.ajax({
        url: '/sl/demo/get-product-class',
        type: 'post',
        data: {_csrf:csrfToken},
        dataType: 'json',
        success: function (json_data) {
        	if(json_data.code == '0')
        	{
        		var items = json_data.data;
        		var items_len = items.length;
        		var html_str = '';

        		for(var i = 0;i < items_len;i++)
        		{
        			html_str += '<label class="checkbox-pretty inline-block"><input data-index="'+ items[i]['id'] +'" onclick="onCheckProductClass(\''+ items[i]['id'] +'\', this);" value="'+ items[i]['name'] +'" name="class_name[]" type="checkbox"><span>'+ items[i]['name'] +'</span></label>';
        		}
        		$('#product_brand_tags').html(html_str);
        	}

        }
    });
}

/**
 * 选择产品分类触发
 * @param  cid 分类id
 * @param  _input 分类选框
 * @return
 */
function onCheckProductClass(cid, _input)
{
	_input = $(_input)
	var stat = $.inArray(cid, class_select) < 0//不在列表中 值为true

	getProductBrand(cid, function(){
		//console.log(' getProductBrand stat aft '+ stat);
		if( stat )
		{
			$('#product_brand_tags').children('#brand_cid_'+cid).find(".checkbox-pretty").each(function(){

				_e = $(this)
				_e.addClass('checked')
				_e.find('input').attr('checked', true)

			});

			class_select.push(cid);
			_input.attr('checked', true)
			_input.parent().hasClass('checked') || _input.parent().addClass('checked')
		}
		else
		{
			var _i = $.inArray(cid, class_select)
			class_select.splice(_i, 1);
			$('#brand_cid_'+cid).html('');

			_input.attr('checked', false)
			_input.parent().hasClass('checked') && _input.parent().removeClass('checked')
		}

	});

}

/**
 * 获取最新的产品品牌列表
 * @param class_id class_id
 * @return void
 */
function getProductBrand(class_id, func )
{
	$.ajax({
        url: '/sl/demo/get-product-brand',
        type: 'post',
        data: {_csrf:csrfToken, class_id:class_id},
        dataType: 'json',
        success: function (json_data) {
        	if(json_data.code == '0')
        	{
        		var items = json_data.data;
        		var items_len = items.length;
        		var html_str = '';

    			for(var j = 0;j< items_len;j++)//品牌
    			{
    				html_str += '<label class="checkbox-pretty inline-block"><input value="'+ items[j]['name'] +'" name="brand_name[]" type="checkbox"><span>'+ items[j]['name'] +'</span></label>';
    			}
    			$('#product_brand_tags').children('#brand_cid_'+class_id).html(html_str);

        		if(func) func();

        	}

        }
    });
}

var class_stat = [true,true],
	brand_stat = [true,true],
	class_select = <?php if(!empty($classSelectIds)): echo Json::encode($classSelectIds); else: echo '[]'; endif;?>,
	brand_select = <?php if(!empty($brandSelectIds)): echo Json::encode($brandSelectIds); else: echo '[]'; endif;?>,
	class_map = <?php if(!empty($classMap)): echo Json::encode($classMap);else:echo '[]';endif;?>,
		//编辑-渠道设置-初始化
	pf_select = <?php if(!empty($scheEditData)):echo $scheEditData["pf_name"];else: echo '[]';endif;?>,
	ua_set = <?php if(!empty($scheEditData)): echo Json::encode($scheEditData["user_agent"]);else: echo '[]';endif;?>,
	cookie_set = <?php if(!empty($scheEditData)): echo Json::encode($scheEditData["cookie"]);else: echo '[]';endif;?>,
	dt_select = <?php if(!empty($scheEditData)): echo $scheEditData["dt_category"];else: echo '[]';endif;?>,
	sche_type_set = <?php if(!empty($scheEditData)): echo $scheEditData["sche_type"];else: echo 1;endif;?>,
	sche_time_set = <?php if(!empty($scheEditData)): /*var_dump($scheEditData);exit;*/echo "'".$scheEditData["sche_time"]."'";else: echo "\"\"";endif;?>,
	week_days_set = <?php if(!empty($scheEditData)): echo "'".$scheEditData["week_days"]."'";else: echo "\"\"";endif;?>,
	month_days_set = <?php if(!empty($scheEditData)): echo "'".$scheEditData["month_days"]."'";else: echo "\"\"";endif;?>;

//全选
$('#class_all_select').on('click', 'input', function(e){
	var stat = class_stat[0]

	if(stat)
		$(this).addClass('checked');
	else
		$(this).removeClass('checked');

	$('#product_class_tags').find('.checkbox-pretty').each(function(){

		_e = $(this)
		var _ei = $.inArray(_e.find('input').attr('data-index'), class_select)

		if(_ei < 0 )
		{
			_e.addClass('checked');
			var _input = _e.find('input')
			_input.attr('checked', true);

			onCheckProductClass(_input.attr('data-index'), _input);
		}
		else
		{
			_e.removeClass('checked');
			var _input = _e.find('input')
			_input.attr('checked', false);

			onCheckProductClass(_input.attr('data-index'), _input);
		}
	})

	class_stat[0] = !stat
})
//反选
$('#class_all_cancel').on('click', 'input',  function(e){

	var stat = class_stat[1]

	if(stat)
		$(this).addClass('checked');
	else
		$(this).removeClass('checked');

	$('#product_class_tags').find('.checkbox-pretty').each(function(){
		_e = $(this)

		if(!_e.hasClass('checked'))
		{
			_e.addClass('checked');
			var _input = _e.find('input')
			_input.attr('checked', true);

			onCheckProductClass(_input.attr('data-index'), _input);
		}
		else
		{
			_e.removeClass('checked');
			var _input = _e.find('input')
			_input.attr('checked', false);

			onCheckProductClass(_input.attr('data-index'), _input);
		}
	})

	class_stat[1] = !stat
})

//全选
$('#brand_all_select').on('click', 'input', function(e){

	var stat = brand_stat[0]

	$('#product_brand_tags').find('.checkbox-pretty').each(function(){
		_e = $(this)
		if(!_e.hasClass('checked'))
		{
			_e.addClass('checked');
			_e.find('input').attr('checked', true);
		}
		else
		{
			_e.removeClass('checked');
			_e.find('input').attr('checked', false);
		}
	})
	brand_stat[0] = !stat
})
//反选
$('#brand_all_cancel').on('click', 'input', function(e){
	var stat = brand_stat[1]

	$('#product_brand_tags').find('.checkbox-pretty').each(function(){
		_e = $(this)
		if(_e.hasClass('checked')){
			_e.removeClass('checked');
			_e.find('input').attr('checked', false);
		}
		else if(!_e.hasClass('checked'))
		{
			_e.addClass('checked');
			_e.find('input').attr('checked', true);
		}
	})
	brand_stat[1] = !stat
})


//周点击
$("#sche_week_tags").on("click", "li", function(_e){
	var _w = $(_e.currentTarget);
	var week_days = $("input[name='week_days']").val();
	var _i, arr = (week_days && week_days.split(',')) || [];

	_i = $.inArray(_w.attr('data-index'), arr)
	if( _i >= 0 )
	{
		arr.splice(_i, 1)
		$("input[name='week_days']").first().val(arr.join(','))
		_w.removeClass('tag-selected');
	}
	else
	{
		arr.push(_w.attr('data-index'))
		arr.sort(function(a, b){ return a - b;})
		$("input[name='week_days']").first().val(arr.join(','))
		_w.addClass('tag-selected');
	}
})

//月点击
$("#sche_month_tags").on("click", "li", function(_e){
	var _m = $(_e.currentTarget);
	var month_days = $("input[name='month_days']").val();
	var _i, arr = (month_days && month_days.split(',')) || [];

	_i = $.inArray(_m.attr('data-index'), arr)
	if( _i >= 0 )
	{
		arr.splice(_i, 1)
		$("input[name='month_days']").first().val(arr.join(','))
		_m.removeClass('tag-selected');
	}
	else
	{
		arr.push(_m.attr('data-index'))
		arr.sort(function(a, b){ return a - b;})
		$("input[name='month_days']").first().val(arr.join(','))
		_m.addClass('tag-selected');
	}
})

function submitAddFrm(){
	var sche_time,
		sche_id = <?php if(!empty($scheEditData)): echo $scheEditData["id"];else: echo "''";endif;?>,
		sche_type_repeat = $("input[name='sche_type_repeat']:checked").val(),
	 	sche_type = $("input[name='sche_type']").val();
	if(sche_type_repeat == 0)//Only once
	{
		sche_time = $("input[name='sche_start_time']:eq(0)").val()
		sche_type = 1
	}
	else//Repeat
	{
		sche_time = $("input[name='sche_start_time']:eq(1)").val()
	}

	var frmData = $('#addFrm').serializeObject();

	frmData['_csrf'] = csrfToken
	frmData['sche_time'] = sche_time
	frmData['sche_type'] = sche_type

	if(sche_id) frmData['id'] = sche_id;

	//console.log(JSON.stringify(frmData));
	//return;
	$.ajax({
        url: $('#addFrm').attr('action'),
        type: 'post',
        data: frmData,
        dataType: 'json',
        success: function (json_data) {
        	if(json_data.code == '0')
        	{
        		alert(json_data.msg);
        	}
        	else
        	{
        		alert(json_data.msg);
        	}

        }
    });
}
<?php
$this->endBlock();
$this->registerJs($this->blocks['addScheJs'], \yii\web\View::POS_END);
app\assets\SLAdminAsset::addScript($this, '@web/sl/lib/template/template.js');

$readyJs =<<<EOT
	//编辑-品牌-初始化
	$('#product_class_tags').find("input").each(function(){
		var _e = $(this)
		var cid = _e.attr('data-index')
		var cbStr//类下的品牌
		if( $.inArray(cid, class_select) > -1)
		{
			_e.attr('checked', true)
			_e.parent().addClass('checked');

			cbObj = {};
			for(var cb in class_map)
			{
				if( class_map[cb]['id'] == cid && $.inArray(class_map[cb]['brand_id'], brand_select) > -1 )
				{
					if( cbObj[cid] )
						cbObj[cid] += '<label class="checkbox-pretty inline-block checked"><input value="'+ class_map[cb]['name'] +'" name="brand_name[]" type="checkbox" data-rules="required" checked="true"><span>'+ class_map[cb]['name'] +'</span></label>';
					else
						cbObj[cid] = '<label class="checkbox-pretty inline-block checked"><input value="'+ class_map[cb]['name'] +'" name="brand_name[]" type="checkbox" data-rules="required" checked="true"><span>'+ class_map[cb]['name'] +'</span></label>';
				}
			}

			for(var b in  cbObj)//品牌
			{
				$('#brand_cid_'+b).html(cbObj[b])
			}
		}
	})

	//渠道-初始化
	$(".tab-pane").find("input[name='pf_name[]']").each(function(){
		var _pfCookie,
			_pfUa = '',
			_e = $(this),
			_pfDiv = _e.parent().parent(),
			_pfId = _pfDiv.attr('id')


		if( $.inArray(_e.val(), pf_select ) > -1 )
		{
			_e.attr('checked', true);
			_e.parent().addClass('checked');

			_pfCookie = _pfId + '_cookie'

			$("input[name='" + _pfCookie +"']").val(cookie_set[_pfCookie])

			$("input[name='" + _pfCookie +"']").val(cookie_set[_pfCookie])

		}

		if( ua_set[_pfId+'_ua'] !== undefined )
		{
			for(var uak in ua_set[_pfId+'_ua'] )
			{
				_pfUa += '<div class="sl-param clearfix"><div class="param__key fl">'
				+'<input type="text" name="pf_jd_ua_uak[]" value="'+ uak +'" class="input-medium input-key" placeholder="名"/></div>'
				+'<div class="param__value fl"><input type="text" name="pf_jd_ua_uav[]" value="'+ ua_set[_pfId+'_ua'][uak] +'" class="input-xlarge input-value" placeholder="值"/>'
				+'<div class="sl-icon-trash"></div></div></div>'
			}

			_pfDiv.find(".sl-params:eq(1)").html( _pfUa )

		}

	});

	//内容复选框 初始化
	for(var d in dt_select)
	{
		$("input[name='dt_category[]'][value='"+ dt_select[d] +"']").click();
	}

	var sche_type_repeat_set = ( sche_type_set == 1 ) ? 0 : 1
	$("input[name='sche_type_repeat']").each(function(){
		var _radio = $(this)
		if(_radio.val() == sche_type_repeat_set)
		{
			_radio.click();
		}
		else
		{
			_radio.attr('checked', false);
			_radio.parent().removeClass('checked');
		}
	})

	if( sche_type_repeat_set == 0 )//定时
	{
		$("input[name='sche_start_time']:eq(0)").val(sche_time_set)
	}
	else//重复
	{
		$('#repeat_options a').each(function(){
			var _o = $(this)
			if(_o.attr('value') == sche_type_set)
			{
				$(this).click();
			}
		});
		$("input[name='sche_start_time']:eq(1)").val(sche_time_set)

		// arr.push(_m.attr('data-index'))
		// arr.sort(function(a, b){ return a - b;})
		// $("input[name='month_days']").first().val(arr.join(','))
		// _m.addClass('tag-selected');

		// arr.push(_w.attr('data-index'))
		// arr.sort(function(a, b){ return a - b;})
		// $("input[name='week_days']").first().val(arr.join(','))
		// _w.addClass('tag-selected');

		$("input[name='week_days']").attr('value', week_days_set);
		$("input[name='month_days']").attr('value', month_days_set);
	}

	//周和月初始化
	var week_days_set_arr = (week_days_set && week_days_set.split(',') ) || [];
	var month_days_set_arr = (month_days_set && month_days_set.split(',') ) || [];
	if(week_days_set_arr.length > 0)
	{
		var w
		for(var i in week_days_set_arr)
		{
			w = $("#sche_week_tags li").eq(week_days_set_arr[i]-1);
			w.addClass('tag-selected');
		}
	}

	if(month_days_set_arr.length > 0)
	{
		var m
		for(var i in month_days_set_arr)
		{
			m = $("#sche_month_tags li").eq(month_days_set_arr[i]-1);
			m.addClass('tag-selected');
		}
	}
EOT;
$this->registerJs($readyJs);
?>
<div class="block clearfix">
				<form id="addFrm" class="sui-validate sui-form" method="POST" action="/sl/demo/add-schedule" onsubmit="javascript:submitAddFrm();return false;">
				<div class="section clearfix">
					<span class="title-prefix-md">新增计划任务</span>
				</div>
				<div class="clearfix">
					<div class="sl-left-half">

						<div class="sui-form form-horizontal">
							<div class="control-group mb1">
								<label class="control-label" style="min-width: 68px;">任务名</label>
								<div class="controls" style="width: 100%;">
									<input type="text" name="name" value="<?php if(isset($scheEditData)): echo $scheEditData["name"];endif; ?>" class="input-xxlarge"
										placeholder="在此输入任务名称"
										style="width: 100%; box-sizing: border-box;height: 34px;" data-rules="required">
								</div>
							</div>
							<div class="control-group mb1">
								<label class="control-label" style="min-width: 68px;">关键字</label>
								<div class="controls" style="width: 100%;">
									<input type="text" name="key_words" value="<?php if(isset($scheEditData)): echo $scheEditData["key_words"]; endif;?>" class="input-xxlarge"
										placeholder="在此输入关键字"
										style="width: 100%; box-sizing: border-box;height: 34px;">
								</div>
							</div>
							<div class="sl-row--normal clearfix">
								<div class="fl row__left-label">分类</div>
								<button type="button" class="sui-btn btn-primary fr top-radius" onclick="editCategory()">分类维护</button>
							</div>
							<div class="control-group mb1">
								<label class="control-label sl-label-special" style="min-width: 76px;">
									<label id="class_all_select" class="checkbox-pretty ">
										<input type="checkbox"><span>全选</span>
									</label>
									<label id="class_all_cancel" class="checkbox-pretty ">
										<input type="checkbox"><span>反选</span>
									</label>
								</label>
								<div class="controls controls--special" style="width: 100%;">
									<div class="sl-checkbox-group" id="product_class_tags" style="width: 100%; box-sizing: border-box;">
										<?php
											foreach ($productClassArr as $k => $v) {
												echo '<label class="checkbox-pretty inline-block"><input data-index="'. $v['id'] . '" onclick="onCheckProductClass(\''. $v['id'] . '\', this);" name="class_name[]" type="checkbox" value="'.$v['name'].'"  data-rules="required"><span>'.$v['name'].'</span></label>';
											}
										?>
									</div>
								</div>
							</div>

							<div class="sl-row--normal clearfix">
								<div class="fl row__left-label">品牌</div>
								<button type="button" class="sui-btn btn-primary fr top-radius" onclick="editBrand()">品牌维护</button>
							</div>
							<div class="control-group mb1">
								<label class="control-label sl-label-special" style="min-width: 76px;">
									<label  id="brand_all_select" class="checkbox-pretty ">
										<input type="checkbox"><span>全选</span>
									</label>
									<label  id="brand_all_cancel" class="checkbox-pretty ">
										<input type="checkbox"><span>反选</span>
									</label>
								</label>
								<div class="controls controls--special" style="width: 100%;">
									<div class="sl-checkbox-group" id="product_brand_tags" style="width: 100%; box-sizing: border-box;">
										<?php
											foreach ($productClassArr as $k => $v) {
												echo '<div id="brand_cid_'. $v['id'] .'"></div>';
											}
										?>
									</div>
								</div>
							</div>

						</div>

					</div>
					<div class="sl-right-half">
						<div class="sui-form form-horizontal label-left-align">
							<div class="control-group" style="margin-bottom: 22px;">
								<label class="control-label v-top" style="min-width: 40px;">渠道</label>
								<div class="controls" style="width: 100%;">
									<ul class="sui-nav nav-tabs nav-large">
										<?php
											$i = 0;
											$pfList = Yii::$app->getModule('sl')->params['PLATFORM_LIST'];
											foreach ($pfSettings as $pk => $pv) {
												if($i == 0)
													$_cls_str = ' class="active"';
												else
													$_cls_str = '';
													echo '<li'.$_cls_str.'><a href="#'.$pk.'" data-toggle="tab">'.$pfList[$pk].'</a></li>';

												$i++;
											}
										?>
									</ul>
									<div class="tab-content">

									<!-- tab-pane Start -->
									<?php
										$i = 0;
										foreach ($pfSettings as $pk => $pv) {
											if($i == 0)
												$_cls_str = ' active';
											else
												$_cls_str = '';
											echo '<div class="tab-pane'.$_cls_str.'" id="'.$pk.'">
												<label class="checkbox-pretty inline-block">
													<input name="pf_name[]" value="'.$pfList[$pk].'" type="checkbox" data-rules="required"><span>'.$pfList[$pk].'</span>
												</label>
												<div class="sl-channel-wrapper">
													<div class="sl-channel-title">设置渠道: <span>'.$pfList[$pk].'</span></div>
													<div class="sl-channel-params">
														<div class="set-param clearfix">
															<div class="set-param__text fl">设置Cookie:</div>
															<div class="set-param__add fr"><a href="javascript:" class="a--primary">添加更多</a></div>
														</div>
														<div class="sl-params params--cookie clearfix">
															<div class="sl-param clearfix">
																<div class="param__key fl">
																	<input type="text" name="'.$pk.'_cookie" value= "'. $pv[$pk.'_cookie'].'" class="input-xlarge input-value" placeholder="值"/>
																	<div class="sl-i2con-trash"></div>
																</div>
															</div>
														</div>
														<div class="set-param clearfix">
															<div class="set-param__text fl">设置其他请求参数:</div>
															<div class="set-param__add fr"><a href="javascript:" class="a--primary">添加更多</a></div>
														</div>
														<div class="sl-params params--cookie clearfix">
															<div class="sl-param clearfix">
																<div class="param__key fl">
																	<input type="text" name="'.$pk.'_ua_uak[]" class="input-medium input-key" placeholder="名"/>
																</div>
																<div class="param__value fl">
																	<input type="text" name="'.$pk.'_ua_uav[]" value="'. $pv[$pk.'_ua'].'" class="input-xlarge input-value" placeholder="值"/>
																	<div class="sl-icon-trash"></div>
																</div>
															</div>
														</div>
													</div>
													<div class="channel__btn-center">
														<button type="button" onclick="javascript:savePf(\''.$pk.'\');" class="sui-btn btn-bordered btn-xlarge btn-primary">确定</button>
													</div>
												</div>
										</div>';
										$i++;
									}
									?>
									<!-- tab-pane End -->
									</div>
								</div>
							</div>
							<div class="control-group" style="margin-bottom: 15px;">
								<label class="control-label" style="min-width: 68px;padding-right: 10px;">抓取内容</label>
								<div class="controls">
									<label class="checkbox-pretty inline-block" style="margin-bottom: 0;line-height: 34px;">
										<input value="商品" name="dt_category[]" type="checkbox" data-rules="required"><span>商品</span>
									</label>
									<!--label class="checkbox-pretty inline-block" style="margin-bottom: 0;">
										<input value="评论" name="dt_category[]" type="checkbox"><span>评论</span>
									</label-->
								</div>
							</div>
							<div class="control-group" style="margin-bottom: 15px;">
								<label class="control-label v-top" style="min-width: 68px;padding-right: 10px;">执行时间</label>
								<div class="controls">
									<div>
										<label data-toggle="radio" class="radio-pretty inline-block checked" style="margin-bottom: 0;line-height: 34px;">
											<input type="radio" name="sche_type_repeat" checked="checked" value="0"><span>定时</span>
										</label>
										<input name="sche_start_time" type="text" class="input-large"
											data-toggle='datepicker' data-date-timepicker='true'
											value="" style="height: 24px;" data-rules="required">
									</div>
									<div>
										<label data-toggle="radio" class="radio-pretty inline-block" style="margin-bottom: 0;line-height: 34px;">
											<input type="radio" name="sche_type_repeat" value="1"><span>重复</span>
										</label>
										<span class="sui-dropdown dropdown-bordered select--xsm select">
											<span class="dropdown-inner">
												<a role="button" data-toggle="dropdown" href="#" class="dropdown-toggle">
													<input value="2" name="sche_type"  onchange="onChangeRepeat(this);" type="hidden">
													<i class="caret"></i><span>每天</span>
												</a>
												<ul id="repeat_options" role="menu" class="sui-dropdown-menu">
													<li role="presentation"> <a role="menuitem" tabindex="-1" href="javascript:void(0);" value="2">每天</a> </li>
													<li role="presentation"> <a role="menuitem" tabindex="-1" href="javascript:void(0);" value="4">每周</a> </li>
													<li role="presentation"> <a role="menuitem" tabindex="-1" href="javascript:void(0);" value="3">每月</a> </li>
												</ul>
											</span>
										</span>
										<input type="text" class="input-medium"
											data-toggle='timepicker' name="sche_start_time" value="" style="height: 24px;margin-left: 8px;">
									</div>
									<div class="sl-tags-wrapper" id="sche_week_tags" style="display: none;">
										<input value="" name="week_days" type="hidden"/>
										<ul class="sui-tag tag-selected">
										  <li data-index="1">周一</li>
										  <li data-index="2">周二</li>
										  <li data-index="3">周三</li>
										  <li data-index="4">周四</li>
										  <li data-index="5">周五</li>
										  <li data-index="6">周六</li>
										  <li data-index="7">周日</li>
										</ul>
									</div>
									<div class="sl-tags-wrapper" id="sche_month_tags" style="display: none;">
										<input value="" name="month_days" type="hidden"/>
										<ul class="sui-tag tag-selected">
										<?php
											for($i = 1; $i < 32;$i++)
										  		echo '<li data-index="'.$i.'">'.$i.'</li>';
										?>
										</ul>
									</div>
								</div>
							</div>
						</div>
					</div>
			</div>
				<div class="sl-btns-wrapper">
					<div class="sl-btns clearfix">
						<button type="submit" class="sui-btn btn-primary btn-borderadius fl sl-btn--md">提交</button>
						<button type="button" class="sui-btn btn-borderadius fr sl-btn--md">返回</button>
					</div>
				</div>
				</form>
			</div>
<script id="template1" type="text/html">
			<div id="myModal1" tabindex="-1" role="dialog" data-hasfoot="false" class="sui-modal hide fade">
			  <div class="modal-dialog">
				<div class="modal-content">
				  <div class="modal-header">
					<h4 id="myModalLabel" class="modal-title">Modal title111</h4>
				  </div>
				  <div class="modal-body">
						<div class="sl-category-wrapper sui-form clearfix">
							<div class="sl-category__left fl" >
								<div class="cl-title clearfix">
									<div class="cl-title__text fl">分类列表</div>
									<div class="sl-icon--add fr"></div>
								</div>
								<input id="cls_m_cls_search" type="text" class="input-large" placeholder="搜索" />
								<div class="sl-list-block">
									<div class="sl-list sl-list--category">
										{{each cb as cv}}
											<div onclick="getBrandByCid({{cv.id}});" class="sl-list__item" data-id="{{cv.id}}">{{cv.class_name}}</div>
										{{/each}}
									</div>
									<input type="text" class="input-medium" placeholder="新选项"
											style="margin-left: 9px;width: 180px;box-sizing: border-box;height: 34px;"/>
								</div>

							</div>
							<div class="sl-category__right fl">
								<div class="sl-transfer clearfix">
									<div class="sl-transfer__left fl">
										<div class="cl-title clearfix" style="padding-bottom: 46px;">
											<div class="cl-title__text fl">关联品牌</div>
										</div>
										<div class="sl-list-block">
											<div class="sl-list sl-list--linkedBrand">
											</div>
										</div>
									</div>
									<div class="sl-transfer__btns fl">
										<div class="slt-btns">
											<div class="slt-btn"><i class="sui-icon icon-arrow-fat-right sl-icon--arrow"></i></div>
											<div class="slt-btn" style="margin-top: 5px;"><i class="sui-icon icon-arrow-fat-left sl-icon--arrow"></i></div>
										</div>

									</div>
									<div class="sl-transfer__right fl">
										<div class="cl-title clearfix">
											<div class="cl-title__text fl">品牌列表</div>
										</div>
										<input id="cls_m_brand_search" type="text" class="input-large" placeholder="搜索" />
										<div class="sl-list-block">
											<div class="sl-list sl-list--brand">
												{{each b as bv}}
												<div onclick="addBrandToCurClass({{bv.id}});" class="sl-list__item" data-id="{{bv.id}}">{{bv.name}}</div>
												{{/each}}
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
				  </div>
				  <div class="modal-footer">
					<button type="button" data-ok="modal" class="sui-btn btn-primary btn-borderadius sl-btn--md">提交</button>
					<button type="button" data-dismiss="modal" class="sui-btn btn-borderadius sl-btn--md" style="margin-left: 80px;">取消</button>
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
						<div class="sl-category-wrapper sui-form clearfix">
							<div class="sl-category__left fl" >
								<div class="cl-title clearfix">
									<div class="cl-title__text fl">品牌列表</div>
									<div class="sl-icon--add fr"></div>
								</div>
								<input type="text" class="input-large" placeholder="搜索" />
								<div class="sl-list-block">
									<div class="sl-list sl-list--brand">
										<div class="sl-list__item">资生堂</div>
										<div class="sl-list__item is-active">妮维雅</div>
										<div class="sl-list__item">欧莱雅</div>
									</div>

									<input type="text" class="input-medium" placeholder="新选项"
											style="margin-left: 9px;width: 180px;box-sizing: border-box;height: 34px;"/>
								</div>

							</div>
							<div class="sl-category__right fl">
								<div class="sl-transfer clearfix">
									<div class="sl-transfer__left fl">
										<div class="cl-title clearfix" style="padding-bottom: 46px;">
											<div class="cl-title__text fl">关联分类</div>
										</div>
										<div class="sl-list-block">
											<div class="sl-list sl-list--linkedCategory">
												<div class="sl-list__item">家电</div>
												<div class="sl-list__item is-active">影音</div>
												<div class="sl-list__item">钟表</div>
											</div>
										</div>
									</div>
									<div class="sl-transfer__btns fl">
										<div class="slt-btns">
											<div class="slt-btn"><i class="sui-icon icon-arrow-fat-right sl-icon--arrow"></i></div>
											<div class="slt-btn" style="margin-top: 5px;"><i class="sui-icon icon-arrow-fat-left sl-icon--arrow"></i></div>
										</div>

									</div>
									<div class="sl-transfer__right fl">
										<div class="cl-title clearfix">
											<div class="cl-title__text fl">分类列表</div>
										</div>
										<input type="text" class="input-large" placeholder="搜索" />
										<div class="sl-list-block">
											<div class="sl-list sl-list--category">
												<div class="sl-list__item">手机</div>
												<div class="sl-list__item is-active">食品</div>
												<div class="sl-list__item">服饰</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
				  </div>
				  <div class="modal-footer">
					<button type="button" data-ok="modal" class="sui-btn btn-primary btn-borderadius sl-btn--md">提交</button>
					<button type="button" data-dismiss="modal" class="sui-btn btn-borderadius sl-btn--md" style="margin-left: 80px;">取消</button>
				  </div>
				</div>
			  </div>
			</div>
		</script>