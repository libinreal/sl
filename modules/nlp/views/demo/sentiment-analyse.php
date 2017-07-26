<?php
    $this->title = '情感分析';
    $this->params['breadcrumbs'][] = 'NLP System';
    $this->params['breadcrumbs'][] = '分析结果';
    $this->params['breadcrumbs'][] = $this->title;

    $wcJs = <<<JS
    jQuery('form#stmts-form').on('submit', function (e) {
    	e.preventDefault();
        var stmtsForm = $(this);
        $.ajax({
        	crossDomain: true,
            url: stmtsForm.attr('action'),
            type: 'post',
            data: stmtsForm.serialize(),
            dataType: 'json',
            success: function (json_data) {
            	val = json_data.result

            }
        });
    })

    $(".sa-tab>li").on('click', function(e){
        e.preventDefault();
        $(e.target).parent().children('li').attr('class', '');
        $(e.target).attr('class', 'active');
    });

    var saChart = echarts.init(document.getElementById('sa-ret'));

    saChart.setOption({
        tooltip: {
            trigger: 'item',
            formatter: "{b} {c}"
        },
        series: [
            {
                type:'pie',
                radius: ['46%', '70%'],
                avoidLabelOverlap: false,
                label: {
                    normal: {
                        show: false,
                        position: 'center'
                    }
                },
                data:[ {value:335, name:'直接访问'},
                {value:310, name:'邮件营销'}
                ]
            }
        ]


    });

JS;
    app\assets\PRSAdminAsset::addScript($this, '@web/admin/js/echarts.simple.min.js');
    $this->registerJs($wcJs);
?>
<div class="basic-block sa-panel">
    <span class="title-prefix-md">情感分析</span>

    <div class="sa-dl clearfix">
        <div id="sa-ret" style="width: 330px;height: 330px;">

        </div>
    </div>

</div>

<div class="basic-block sa-example">
    <ul class="sa-tab clearfix"><li class="active">通用</li><li>骑车</li><li>厨具</li><li>餐饮</li><li>新闻</li><li>微博</li></ul>

    <div class="sa-dr clearfix">
        <div class="sa-tag sa-tag-p clearfix">正面</div>
        <div  class="sa-tag clearfix">负面</div>
        <div>负面指数：</div>
        <div>负值判断为负面。</div>
        <div>正值判断为正面。</div>
    </div>

</div>