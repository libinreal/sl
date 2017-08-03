<?php
$this->title = '测试页面';
$this->beginBlock('test');
?>

$(function(){
	$.ajax({
        url: '/sl/demo/class-brand-manage',
        type: 'post',
        data: {_csrf:csrfToken},
        dataType: 'json',
        success: function (json_data) {
        	if(json_data.code == '0')
        	{
				var clsBrandClsArr = []


				for( var _k in json_data.data.cb)
				{
					clsBrandClsArr.push({
						value:json_data.data.cb[_k].class_name,
						data:json_data.data.cb[_k].id
						//data:{id: json_data.data.cb[_k].id}
					});
				}
				console.log(JSON.stringify(clsBrandClsArr))
				$('#cbc-suggest').autocomplete({
					lookup:clsBrandClsArr,
					minChars:0,
				    onSelect: function(e) {

				      //console.log(' auto 1 ' + JSON.stringify(e))
				    }
				});
			}
		}
	});
})
var st = '测试'
console.log(' indexOf ： ' + st.indexOf('测试')!== -1);
<?php
$this->endBlock();
$this->registerJs($this->blocks['test'], \yii\web\View::POS_END);
?>
<input id="cbc-suggest" type="text" class="input-large" placeholder="搜索" />