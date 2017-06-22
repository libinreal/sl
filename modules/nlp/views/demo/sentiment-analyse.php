<?php
    $this->title = '情感分析';
    $this->params['breadcrumbs'][] = 'NLP System';
    $this->params['breadcrumbs'][] = '分析结果';
    $this->params['breadcrumbs'][] = $this->title;

    $wcJs = <<<JS
    jQuery('form#stmts-form').on('submit', function (e) {
    	e.preventDefault();
        var sa = $(this);
        $.ajax({
        	crossDomain: true,
            url: sa.attr('action'),
            type: 'post',
            data: sa.serialize(),
            dataType: 'json',
            success: function (json_data) {
            	val = json_data.result

            }
        });
    })
JS;
    $this->registerJs($wcJs);
?>
<div class="basic-block sa-panel">
        <span class="title-prefix-md">情感分析</span>

            <div class="sa-dl clearfix">
                <div id="wc-ret">

                </div>
            </div>

        </div>

        <div class="basic-block sa-example">
        <ul class="sa-tab"><li>通用</li><li>骑车</li><li>厨具</li><li>餐饮</li><li>新闻</li><li>微博</li></ul>

            <div class="sa-dr clearfix">
                <span>正面</span>
                <span>负面</span>
                负面指数：
                正值判断为负面。
                负值判断为正面。
                负面指数：
                正值判断为负面。
                负值判断为正面。
            </div>

        </div>