<?php
use app\models\sl\SlTaskSchedule;
use yii\helpers\Url;
use yii\helpers\Json;

$this->title = 'Add WeChat Task Schedule';

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
                                                    'label' => 'Add WeChat Task Schedule',
                                                    'li_class' => 'current'
                                                    ]
                                                ]
                                ];

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

var curClsMapId,
	categoryJsonData,

	//PHP data
	curCategoryDefault = <?php if(!empty($articleClassArr)): echo Json::encode($articleClassArr);else: echo '{}';endif;?>,//分类列表
	curCategoryMapDefault = <?php if(!empty($curCategoryMap)): echo Json::encode($curCategoryMap);else:echo '{}';endif;?>,//分类 关联标签
	curTagDefault = <?php if(!empty($articleTagArr)): echo Json::encode($articleTagArr);else:echo '{}';endif;?>,//关键字列表

	//JS data
	curCategory = {},//分类列表
	curCategoryMap = {},//分类 关联标签
	curTag = {},//关键字列表	

	modal1 = null,
	modal2 = null,
	clsTagClsArr = [],
	clsTagTagArr = [];

	//添加分类
	function addCategory(_c)
	{
		if(_c)
		{
			_c = _c.replace(/(^\s*)|(\s*$)/g,"")
			for(var _i in curCategory)
			{
				if(curCategory[_i] == _c)
				{
					$.alert('分类已存在')
					return;
					
				}
			}

			$.ajax({
	        url: '/sl/schedule/add-article-class',
	        type: 'post',
	        data: {n:_c, _csrf:csrfToken},
	        dataType: 'json',
	        success: function (json_data) {
		        	if(json_data.code == '0')
		        	{
		        		$('#ctc').append('<div onclick="getClassMap('+json_data.data+');" class="sl-list__item" data-id="'+json_data.data+'"><div>'+ _c+'</div></div>');
		        		$('#ctc').parent().children('input').remove();

		        		clsTagClsArr.push({
	        				value:_c,
	        				data:json_data.data
	        			});

	        			if(!curCategoryMap[json_data.data])
	        			{
	        				curCategoryMap[json_data.data] = [];
	        				article_class_stat[json_data.data] = false;
	        			}



	        			curCategory[json_data.data] = _c

						$('#ctc-suggest').autocomplete( 'setOptions', {lookup:clsTagClsArr})
		        	}

		        }
		    });
		}
	}

	//显示类下的标签
	function getClassMap(_cid)
	{
		_cid = String(_cid)
		curClsMapId = _cid;

		$('#ctc').children('div').each(function(){
			var t = $(this)

			t.children('div:eq(1)').remove();
				t.removeClass('is-active')

			if(t.attr('data-id')==_cid)
			{
				t.addClass('is-active');
				t.append('<div class="ctc-del"></div>')

				$('.ctc-del').on('click', {id:_cid}, delClass)
			}
		})

		var bDom = ''

		for(var _k in curCategoryMap[_cid])
		{

			bDom += '<div class="sl-list__item" data-id="' +	curCategoryMap[_cid][_k]  	+	'"	'
					+	'	onclick="removeCategory('+	curCategoryMap[_cid][_k]	+');">'
					+	curTag[curCategoryMap[_cid][_k]]
					+	'</div>'

		}

		$('#ct').html(bDom);
	}
	//把标签加到对应类下
	function addClassMap(_tid)
	{
		_tid = String(_tid)

		//删除标签按钮
		$('#ctt').children('div').each(function(){
			var t = $(this)

			t.children('div:eq(1)').remove();
				t.removeClass('is-active')

			if(t.attr('data-id')==_tid)
			{
				t.addClass('is-active');
				t.append('<div class="ctt-del"></div>')

				$('.ctt-del').on('click', {id:_tid}, delTag)
			}
		})

		if(curClsMapId)//已选择分类
		{
			if($.inArray(_tid, curCategoryMap[curClsMapId]) > -1)
			{
				return;//已存在该标签
			}
			curCategoryMap[curClsMapId].push(_tid)

			$('#ct').children('div').each(function(){ $(this).removeClass('is-active');});

			$('#ct').append('<div class="sl-list__item is-active" data-id="' +	_tid  	+	'"	'
						+	'	onclick="removeCategory('+	_tid	+');">'
						+	curTag[_tid]
						+	'</div>');
		}

	}

	//从分类下移除标签
	function removeCategory(_tid)
	{
		_tid = String(_tid)
		var _pos = $.inArray(_tid, curCategoryMap[curClsMapId])
		curCategoryMap[curClsMapId].splice(_pos, 1);

		$('#ct').find('div').each(function(){
			if($(this).attr('data-id') == _tid)
			{
				$(this).remove();
			}
		});
	}

	//删除分类
	function delClass(e)
	{
		e.preventDefault();
		_cid = String(e.data.id)

		$.confirm({
			title:'提示',
			body:'是否删除分类和关联标签？',
			okHide:function(){
				delete curCategory[_cid];
				delete curCategoryMap[_cid];

				$('#ctc').children('div').each(function(){
					if($(this).attr('data-id') == _cid)
					{
						$(this).remove();
					}
				})

				$('#ct').empty()
				if(_cid == curClsMapId)
					curClsMapId = null;

				var _kDel
    			for(var _k in clsTagClsArr)
    			{
    				if( String(clsTagClsArr[_k].data) == _cid)
    				{
    					_kDel = _k
    					break;
    				}
    			}

    			if(_kDel)
    				clsTagClsArr.splice(_kDel, 1);

				$('#ctc-suggest').autocomplete('setOptions', {lookup:clsTagClsArr})
			}
		})

		return false;
	}

	function editTag()
	{
		
		$.ajax({
        url: '/sl/schedule/class-tag-manage',
        type: 'post',
        data: {_csrf:csrfToken},
        dataType: 'json',
        success: function (json_data) {
	        	if(json_data.code == '0')
	        	{
	        		curCategory = {}
	        		curTag = {}
	        		curCategoryMap = {}
	        		categoryJsonData = json_data.data

	        		clsTagClsArr = []
					clsTagTagArr = []

	        		for( var _k in categoryJsonData.ct)
	        		{
	        			//Init curCategory
	        			curCategory[categoryJsonData.ct[_k].id] = categoryJsonData.ct[_k].class_name

	        			//Init curCategoryMap
	        			if(!curCategoryMap[categoryJsonData.ct[_k].id])
	        				curCategoryMap[categoryJsonData.ct[_k].id] = []

	        			for(var _m in categoryJsonData.ct[_k].articleTag)
	        			{
	        				curCategoryMap[categoryJsonData.ct[_k].id].push(categoryJsonData.ct[_k].articleTag[_m].id)
	        			}

	        			clsTagClsArr.push({
	        				value:categoryJsonData.ct[_k].class_name,
	        				data:categoryJsonData.ct[_k].id
	        			});


	        		}

					for( var _k in categoryJsonData.t)
	        		{
	        			//Init curTag
	        			curTag[categoryJsonData.t[_k].id] = categoryJsonData.t[_k].name

	        			clsTagTagArr.push({
	        				value:categoryJsonData.t[_k].name,
	        				data:categoryJsonData.t[_k].id
	        			});
	        		}

        			new ModalBuilder('template1', {
						title: '新增分类',
						shown: function(){
							//搜索框
							$('#ctc-suggest').autocomplete({
								lookup:clsTagClsArr,
								minChars:0,
							    onSelect: function(s) {
							    	getClassMap(s.data)
							    }
							});

							$('#ctt-suggest').autocomplete({
								lookup:clsTagTagArr,
								minChars:0,
							    onSelect: function(s) {
							    	addClassMap(s.data)
							    }
							});

							//添加分类
							$('.ctc-add').on('click', function(){
								if($('#ctc').parent().children('input').length > 0)
									return;
								$('#ctc').after('<input type="text" onblur="addCategory(this.value);" class="input-medium" placeholder="新分类" style="margin-left: 9px;width: 180px;box-sizing: border-box;height: 34px;">');
							})

							//添加标签
							$('.ctt-add').on('click', function(){
								if($('#ctt').parent().children('input').length > 0)
									return;
								$('#ctt').after('<input type="text" onblur="addTag(this.value);" class="input-medium" placeholder="新标签" style="margin-left: 9px;width: 180px;box-sizing: border-box;height: 34px;">');
							})

							//默认选中第一分类
							for(var _i in curCategory)
							{
								getClassMap(_i);
								break;
							}
						},
						okHide: function(){
							$.ajax({
						        url: '/sl/schedule/save-article-class-tag',
						        type: 'post',
						        data: {c:curCategory, m:curCategoryMap, t:curTag, _csrf:csrfToken},
						        dataType: 'json',
						        success: function(json_data){
						        	if(json_data.code == '0')
						        	{
						        		//增加的分类
						        		for(var _k in curCategory)
						        		{
						        			if(!curCategoryDefault[_k])
						        			{
						        				article_class_stat[_k] = false
						        			}	
						        		}
						        		//减少的分类
						        		for(var _k in curCategoryDefault)
						        		{
						        			if(!curCategory[_k])
						        			{
						        				delete article_class_stat[_k]
						        			}
						        		}

						        		var _inputValue, _tagSelectIndex
						        		//减少的标签
						        		for(var _k in curTagDefault)
						        		{
						        			if(!curTag[_k])
						        			{
						        				_tagSelectIndex = $.inArray(curTag[_k], tag_name_select)
						        				if(_tagSelectIndex > -1)
						        				{
						        					tag_name_select.splice(_tagSelectIndex, 1)
						        					tag_select.splice(_tagSelectIndex, 1)
						        				}
						        			}
						        		}

						        		curCategoryMapDefault = curCategoryMap
						        		curCategoryDefault = curCategory
						        		curTagDefault = curTag

						        		refreshArticleTag();
						        	}
						        }
							});
						},
						cancelHide: function(){

						},
					}, json_data.data);
	        	}

	        }
	    });

	}

	//添加标签(页面添加)
	function addArticleTag()
	{
		var _t = $('#input_key_words').val()
		if(_t)
		{
			_t = _t.replace(/(^\s*)|(\s*$)/g,"")
			for(var _i in curTagDefault)
			{
				if(curTagDefault[_i] == _t)
				{
					$.alert('标签已存在')
					return;
					
				}
			}

			$.ajax({
	        url: '/sl/schedule/add-article-tag',
	        type: 'post',
	        data: {n:_t, _csrf:csrfToken},
	        dataType: 'json',
	        success: function (json_data) {
		        	if(json_data.code == '0')
		        	{
	        			curTagDefault[json_data.data] = _t
	        			curCategoryMapDefault['0'].push(json_data.data)

	        			//默认勾选
	        			tag_name_select.push( _t )
						tag_select.push( json_data.data )

	        			refreshArticleTag();
		        	}

		        }
		    });
		}
	}

	//刷新页面标签
	function refreshArticleTag()
	{
	//console.log('refreshArticleTag  ------  tag_select ' +  JSON.stringify(tag_select) + ' -------  tag_name_select ' + JSON.stringify(tag_name_select));
		var ctDom = $('#article_class_tags'),
			tDom = $('#article_tags'),
			ctStr = '',
			tStr = '',
			clStr = '',
			tlStr = '',
			tCheck = '',
			cCheck = '';

		//init '未分类' 标签 ------开始-------
		if(!curCategoryMapDefault['0'])
		{
			curCategoryDefault['0'] = '未分类'
			var _classedTags = [], _noClassedTags = []

			for(var _k in curCategoryMapDefault)
			{
				_classedTags = _classedTags.concat( curCategoryMapDefault[_k] );
			}

			for(var _i in curTagDefault)
			{
				if($.inArray(_i, _classedTags ) == -1)
					_noClassedTags.push(_i)
			}

			curCategoryMapDefault['0'] = _noClassedTags;
		}
		//init '未分类' 标签 ------结束-------

		for(var _k in curCategoryMapDefault)
		{

			cCheck = ''
			if(article_class_stat[_k])
				cCheck = 'checked'

			//class label
			clStr = '<div class="tc-div"><label class="checkbox-pretty inline-block cbcl '+ cCheck +'"><input value="' + curCategoryDefault[_k] +'" name="class_name[]" type="checkbox" data-rules="required" ' + (cCheck ? ('checked="'+ cCheck +'"') : '' )+ '/><span>'+ curCategoryDefault[_k] +'</span></label></div>';
			
			//tags label
			tlStr = '<div class="tct-div">'
			for(var _i in curCategoryMapDefault[_k])
			{
				tCheck = ''
				
				if($.inArray(curCategoryMapDefault[_k][_i], tag_select) > -1 )
				{
					tCheck = 'checked'
					
				}

				tlStr += '<label class="checkbox-pretty inline-block cbtl '+ tCheck +'" data-id="'+ curCategoryMapDefault[_k][_i] +'"><input value="' + curTagDefault[curCategoryMapDefault[_k][_i]] + '" name="key_words[]" type="checkbox" data-rules="required" ' +(tCheck ? ('checked="'+ tCheck +'"') : '' ) +'/><span>'+ curTagDefault[curCategoryMapDefault[_k][_i]] +'</span></label>'
			}
			tlStr += '</div>'

			//merge class and label string
			ctStr += '<div data-id="'+ _k +'">' + clStr + tlStr +'</div>';

		}

		//render `article_class_tags` html
		ctDom.html(ctStr)

		//render `article_tags` html
		for(var _i in tag_name_select)
		{
			tStr += '<div>' + tag_name_select[_i] + '</div>'
		}
		tDom.html(tStr)

		//add events
		$('.tc-div').on('click', 'input', function(e){
			var _cs, _stat, _cn = e.currentTarget.value, _tDom = $(this).parent().parent().parent().children('.tct-div')

			for(var _k in curCategoryDefault)
			{
				if(_cn == curCategoryDefault[_k])
					_cs = _k//cur click category id
			}

			article_class_stat[_cs] = !article_class_stat[_cs]//update cur category checkbox's stat
			_stat = article_class_stat[_cs]//get cur category checkbox's stat

			//cur category click 
			if(_stat)
			{
				$(e.currentTarget).parent().addClass('checked');
				$(e.currentTarget).attr('checked', true);
			}
			else
			{
				$(e.currentTarget).parent().removeClass('checked');
				$(e.currentTarget).attr('checked', false);
			}

			var _inputValue, _tagSelectIndex

			_tDom.find('.checkbox-pretty').each(function(){
				_e = $(this)
				if(_stat && !_e.hasClass('checked'))//to select all
				{
					_e.addClass('checked');
					_e.find('input').attr('checked', true);
				}
				else if(!_stat && _e.hasClass('checked'))
				{
					_e.removeClass('checked');
					_e.find('input').attr('checked', false);
				}

				//_e.click();
				_inputValue = _e.find('input').attr('value')
				_tagSelectIndex = $.inArray( _inputValue, tag_name_select)

				if(_stat && _tagSelectIndex == -1)
				{
					tag_name_select.push( _inputValue )
					tag_select.push( _e.attr('data-id') )
				}

				if(!_stat && _tagSelectIndex > -1)
				{
					tag_name_select.splice( _tagSelectIndex, 1 )
					tag_select.splice( _tagSelectIndex, 1 )
				}
			})

			refreshArticleTag();

		})

		$('.tct-div').on('click', '.checkbox-pretty', function(e){
			var _inputValue = $(e.currentTarget).children('input').val(),
				_tagSelectIndex = $.inArray( _inputValue, tag_name_select),
				_tagSelectId = $(e.currentTarget).attr('data-id')
				
			if(_tagSelectIndex == -1)
			{
				tag_name_select.push(_inputValue);
				tag_select.push(_tagSelectId)
			}

			else
			{
				tag_name_select.splice( _tagSelectIndex, 1 )
				tag_select.splice(_tagSelectIndex, 1)
			}
			refreshArticleTag();
		})
	}	

	//添加标签(对话框添加)
	function addTag(_t)
	{
		if(_t)
		{
			_t = _t.replace(/(^\s*)|(\s*$)/g,"")
			for(var _i in curTag)
			{
				if(curTag[_i] == _t)
				{
					$.alert('标签已存在')
					return;
				}
			}
			


			$.ajax({
	        url: '/sl/schedule/add-article-tag',
	        type: 'post',
	        data: {n:_t, _csrf:csrfToken},
	        dataType: 'json',
	        success: function (json_data) {
		        	if(json_data.code == '0')
		        	{
		        		$('#ctt').append('<div onclick="addClassMap('+json_data.data+');" class="sl-list__item" data-id="'+json_data.data+'"><div>'+ _t+'</div></div>');
		        		$('#ctt').parent().children('input').remove();

		        		clsTagTagArr.push({
	        				value:_t,
	        				data:json_data.data
	        			});

	        			curTag[json_data.data] = _t

						$('#ctt-suggest').autocomplete('setOptions', {lookup:clsTagTagArr})
		        	}

		        }
		    });
		}
	}

	//删除标签
	function delTag(e)
	{
		e.preventDefault();
		_tid = String(e.data.id)

		$.confirm({
			title:'提示',
			body:'是否删除标签和关联分类？',
			okHide:function(){
				delete curTag[_tid];

				$('#ctt').children('div').each(function(){
					if($(this).attr('data-id') == _tid)
					{
						$(this).remove();
					}
				})

				var _pos
				for(var _cid in curCategoryMap)
				{

					_pos = $.inArray(_tid, curCategoryMap[_cid])

					if( _pos > -1 )
					{
						curCategoryMap[_cid].splice(_pos, 1);//delete tag in maps array

						if(curClsMapId == _cid)// delete tag in the map div.
						{
							$("#ct").children("div").each(function(){
								
								var t = $(this)
								
								if(t.attr('data-id') == _tid)
								{
									t.remove();								
								}

							})
						}
					}

				}


    			var _kDel
    			for(var _k in clsTagTagArr)
    			{
    				if( String(clsTagTagArr[_k].data) == _tid)
    				{
    					_kDel = _k
    					break;
    				}
    			}

    			if(_kDel)
    				clsTagTagArr.splice(_kDel, 1);

				$('#ctt-suggest').autocomplete('setOptions', {lookup:clsTagTagArr})
			}
		})

		return false;
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
        url: '/sl/schedule/update-schedule-settings',
        type: 'post',
        data: pfData,
        dataType: 'json',
        success: function (json_data) {
        	if(json_data.code == '0')
        	{
        		$.alert('保存成功');
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

var article_class_stat = [],
	tag_name_select = <?php if(!empty($scheEditData)  && !empty($scheEditData["key_words"]) ): echo Json::encode($scheEditData["key_words"]);else: echo '[]';endif;?>,
	tag_select = <?php if(!empty($kwSelectIds)): echo Json::encode($kwSelectIds); else: echo '[]'; endif;?>,
		//编辑-渠道设置-初始化
	pf_select = <?php if(!empty($scheEditData)  && !empty($scheEditData["pf_name"]) ):echo $scheEditData["pf_name"];else: echo '[]';endif;?>,
	ua_set = <?php if(!empty($scheEditData)  && !empty($scheEditData["user_agent"]) ): echo Json::encode($scheEditData["user_agent"]);else: echo '[]';endif;?>,
	cookie_set = <?php if(!empty($scheEditData) && !empty($scheEditData["cookie"]) ): echo Json::encode($scheEditData["cookie"]);else: echo '[]';endif;?>,
	dt_select = <?php if(!empty($scheEditData) && !empty($scheEditData["dt_category"]) ): echo $scheEditData["dt_category"];else: echo '[]';endif;?>,
	alert_params = <?php if(!empty($scheEditData) && !empty($scheEditData["alert_params"]) ): echo Json::encode($scheEditData["alert_params"]);else: echo '[]';endif;?>,
	sche_type_set = <?php if(!empty($scheEditData) && !empty($scheEditData["sche_type"]) ): echo $scheEditData["sche_type"];else: echo 1;endif;?>,
	sche_time_set = <?php if(!empty($scheEditData) && !empty($scheEditData["sche_time"]) ): /*var_dump($scheEditData);exit;*/echo "'".$scheEditData["sche_time"]."'";else: echo "\"\"";endif;?>,
	week_days_set = <?php if(!empty($scheEditData)  && !empty($scheEditData["week_days"]) ): echo "'".$scheEditData["week_days"]."'";else: echo "\"\"";endif;?>,
	month_days_set = <?php if(!empty($scheEditData)  && !empty($scheEditData["month_days"]) ): echo "'".$scheEditData["month_days"]."'";else: echo "\"\"";endif;?>;

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

function submitAddFrm(_confirmUpdate){
	if(tempLoginCookie == 0)
	{
		return;
	}

	var sche_time,
		sche_id = <?php if(!empty($scheEditData)): echo $scheEditData["id"];else: echo "''";endif;?>,
		data_type = <?php echo  "'". Yii::$app->request->get('data_type','') . "'"; ?>,
		sche_type_repeat = $("input[name='sche_type_repeat']:checked").val(),
	 	sche_type = $("input[name='sche_type']").val();

	if(sche_id)
	{
		if(!_confirmUpdate)
		{
			$.confirm({
				title:'提示',
				body:'保存后该计划今日已完成的采集数据将会丢失，确定要保存吗？',
				okHide:function(){
					_confirmUpdate = true;
					submitAddFrm(true);
				}
			});
			return false;
		}
	}

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

	frmData['data_type'] = data_type

	if(sche_id)
	{
		frmData['id'] = sche_id;
	}

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
        		$.alert('保存成功');
        	}
        	else
        	{
        		$.alert(json_data.msg);
        	}

        }
    });
}
<?php
$this->endBlock();
$this->registerJs($this->blocks['addScheJs'], \yii\web\View::POS_END);
app\assets\SLAdminAsset::addScript($this, '@web/sl/lib/template/template.js');

$readyJs =<<<EOT
	//可用标签-渲染
	refreshArticleTag();
	//已选分类
	for(var _k in curCategoryMap)
	{
		article_class_stat[_k] = false
	}
	//已选标签
	for(var _k in curTag)
	{
		article_tag_stat[_k] = false
	}


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

	//预警参数初始化
	if( alert_params['duration'] )
		$("input[name='alert_duration']").val( alert_params['duration'] );

	if( alert_params['total_num_min'] )
		$("input[name='alert_total_num_min']").val( alert_params['total_num_min'] );

	if( alert_params['total_num_max'] )
		$("input[name='alert_total_num_max']").val( alert_params['total_num_max'] );

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
				<form id="addFrm" class="sui-validate sui-form" method="POST" action="/sl/schedule/add-schedule" onsubmit="javascript:submitAddFrm();return false;">
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
							<!--div class="control-group mb1">
								<label class="control-label" style="min-width: 68px;">关键字</label>
								<div class="controls" style="width: 100%;">
									<input type="text" name="key_words" value="" class="input-xxlarge"
										placeholder="在此输入关键字"
										style="width: 100%; box-sizing: border-box;height: 34px;">
								</div>
							</div-->
					
							<div class="control-group mb1">
								<label class="control-label" style="min-width: 68px;vertical-align: top;">已选标签(关键字)</label>
								<div class="controls controls--special" style="width: 100%;">
									<div class="sl-checkbox-group" id="article_tags" style="width: 100%; box-sizing: border-box;">
									</div>
								</div>
							</div>

							<!--div class="sl-row--normal clearfix">
								<div class="fl row__left-label ">添加标签</div>
								<div class="controls controls--special" style="padding: 0px 7px;">
										<input name="" value="" type="text" id="input_key_words" class="input-medium" style="height: 24px;width:274px;" /> 
										<button class="sui-btn btn-bordered btn-xlarge btn-primary" type="button" id="btn_add_key_words" onclick="addArticleTag();" >确定</button>
								</div>
							</div-->
							<div class="control-group mb1">
								<label class="control-label" style="min-width: 68px;vertical-align: top;">添加标签</label>
								<div class="controls controls--special">
										<input name="" value="" type="text" id="input_key_words" class="input-medium" style="height: 24px;width:274px;" /> 
										<button class="sui-btn btn-bordered btn-xlarge btn-primary" type="button" id="btn_add_key_words" onclick="addArticleTag();" >确定</button>
								</div>
							</div>

							<div class="sl-row--normal clearfix">
								<!--div class="fl row__left-label"></div-->
								<button type="button" class="sui-btn btn-primary fr top-radius" onclick="editTag()">标签维护</button>
							</div>
							<div class="control-group mb1">
								<label class="control-label" style="min-width: 68px;vertical-align: top;">备选标签</label>
								<div class="controls controls--special" style="width: 100%;">
									<div class="sl-checkbox-group" id="article_class_tags" style="width: 100%; box-sizing: border-box;">
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
										<input value="文章" name="dt_category[]" type="checkbox" data-rules="required"><span>文章</span>
									</label>
									
								</div>
							</div>
							<div class="control-group" style="margin-bottom: 15px;">
								<label class="control-label v-top" style="min-width: 68px;padding-right: 10px;">执行时间</label>
								<div class="controls">
									<div>
										<label data-toggle="radio" class="radio-pretty inline-block checked" style="margin-bottom: 0;line-height: 34px;">
											<input type="radio" name="sche_type_repeat" checked="checked" value="0"><span>定时</span>
										</label>
										<input name="sche_start_time" type="text"
											data-toggle='datepicker' data-date-timepicker='true'
											value="" style="height: 24px;width: 274px;" data-rules="required">
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
										<input type="text"
											data-toggle='timepicker' name="sche_start_time" value="" style="height: 24px;width:193px;margin-left: 8px;">
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
							<!-- 异常预警 START -->
							<div class="control-group" style="margin-bottom: 15px;">
								<label class="control-label v-top" style="min-width: 68px;padding-right: 10px;">预警参数</label>
								<div class="controls">
									<div>
										<lable class="label79" style="margin-bottom: 0;line-height: 34px;">预警时间</lable>
										<input type="text" name="alert_duration" value=""
											placeholder="输入最长时间 单位(h)" class="input-medium"
											style="height: 24px;width: 274px;">
									</div>
									<div>
										<lable class="label79" style="margin-bottom: 0;line-height: 34px;">预警值</lable>
										从
										<input type="text" name="alert_total_num_min" value=""
											placeholder="输入最小值"
											style="height: 24px;width:112px;">
										到
										<input type="text" name="alert_total_num_max" value=""
											placeholder="输入最大值"
											style="height: 24px;width:112px;">
									</div>
								</div>
							</div>
							<!-- 异常预警 END -->
						</div>
					</div>
			</div>
				<div class="sl-btns-wrapper">
					<div class="sl-btns clearfix">
						<button type="submit" class="sui-btn btn-primary btn-borderadius fl sl-btn--md">提交</button>
						<button type="button" class="sui-btn btn-borderadius fr sl-btn--md" onclick="javascript:history.back(-1);">返回</button>
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
									<div class="sl-icon--add fr ctc-add"></div>
								</div>
								<input id="ctc-suggest" type="text" class="input-large" placeholder="搜索" />
								<div class="sl-list-block">
									<div class="sl-list sl-list--category" id="ctc">
										{{each ct as cv}}
											<div onclick="getClassMap({{cv.id}});" class="sl-list__item" data-id="{{cv.id}}"><div>{{cv.class_name}}</div></div>
										{{/each}}
									</div>
								</div>

							</div>
							<div class="sl-category__right fl">
								<div class="sl-transfer clearfix">
									<div class="sl-transfer__left fl">
										<div class="cl-title clearfix" style="padding-bottom: 46px;">
											<div class="cl-title__text fl">关联标签</div>
										</div>
										<div class="sl-list-block">
											<div class="sl-list sl-list--linkedtag"  id="ct">
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
											<div class="cl-title__text fl">标签列表</div>
											<div class="sl-icon--add fr ctt-add"></div>
										</div>
										<input id="ctt-suggest" type="text" class="input-large" placeholder="搜索" />
										<div class="sl-list-block">
											<div class="sl-list sl-list--tag" id="ctt">
												{{each t as tv}}
												<div onclick="addClassMap({{tv.id}});" class="sl-list__item" data-id="{{tv.id}}"><div>{{tv.name}}</div></div>
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