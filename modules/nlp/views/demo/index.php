<?php
    $this->title = '词性分析';
    $this->params['breadcrumbs'][] = 'NLP System';
    $this->params['breadcrumbs'][] = '分析结果';
    $this->params['breadcrumbs'][] = $this->title;

    $stmtsActApi = str_replace('\\/', '/', urldecode(
        json_encode( array(
            array('name'=>urlencode('词性分析'), 'api'=>Yii::$app->getModule('nlp')->params['API.NLP_WORD_CLASS_ANALYSE']),
            array('name'=>urlencode('实体识别'), 'api'=>Yii::$app->getModule('nlp')->params['API.NLP_NAME_ENTITY_RECOGNIZE']),
            array('name'=>urlencode('依存文法'), 'api'=>Yii::$app->getModule('nlp')->params['API.NLP_PARSE']),
            array('name'=>urlencode('情感分析'), 'api'=>Yii::$app->getModule('nlp')->params['API.NLP_SENTIMENT_ANALYSE'])
        ) )
    ));

    $wcJs = <<<JS
    var stmtsActIndex = 0;
    var stmtsActApi = $stmtsActApi;

    $(".bb-nav ul>li").each(
        function(act_index){
            $(this).on('click',function(){

                $(this).parent().children().each(function(_i,_e){
                    if( _i == act_index )
                        $(_e).attr('class', 'active');
                    else
                        $(_e).attr('class', '');
                });

                stmtsActIndex = act_index;
                showResult();
            });
        }
    );

    $('form#stmts-form').on('submit', function (e) {
        e.preventDefault();
        showResult();
    });

    $(".sa-tab>li").on('click', function(e){
        e.preventDefault();
        var curNavigation = $(e.target);
        curNavigation.parent().children('li').attr('class', '');
        curNavigation.attr('class', 'active');
    });

    var saChart = echarts.init(document.getElementById('sa-ret'));

    saChart.setOption({
        tooltip: {
            show: true,
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
                data:[]
            }
        ],
        color: ['#8084B4', '#2B3282']

    });

    function showResult(){
        for(var ci = 0; ci < stmtsActApi.length; ci++){
            $('#apiResult-'+ci).hide();
        }
        $('#stmts-kw').hide();

        $('#apiResult-'+stmtsActIndex).show();
        if( stmtsActIndex == 3){
            $('#stmts-kw').show();
        }


        var stmtsForm = $('form#stmts-form');
        var intextTextarea = stmtsForm.find('textarea[name="intext"]')[0];
        // alert(stmtsForm.find('input[name="kw"]')[0].val());
        //文本框为空
        if( stmtsForm.find('textarea[name="intext"]')[0].value == '' )
            return ;
        //关键字为空
        kwInput = stmtsForm.find('input[name="kw"]')[0];
        if( stmtsActIndex == 3 ){
            if( kwInput.value == '' )
                return ;
        }

        $.ajax({
            crossDomain: true,
            url: stmtsActApi[stmtsActIndex]['api'],
            type: 'post',
            data: stmtsForm.serialize(),
            dataType: 'json',
            success: function (json_data) {

                if( stmtsActIndex == 0 ){//词性分析
                    jsPlumb.deleteEveryEndpoint();

                    tags = json_data.result
                    var wc_ret = ''
                    for(var e in tags){
                        wc_ret += '<span class="wc-'+ tags[e]["tag"] +'">' + tags[e]["content"] + '</span>'
                    }
                    $('#wc-ret').html(wc_ret);
                } else if ( stmtsActIndex == 1 ){//实体识别
                    jsPlumb.deleteEveryEndpoint();

                    tags = json_data.result
                    var ner_ret = ''
                    for(var e in tags){
                        ner_ret += '<span class="ner-'+ tags[e]["tag"] +'">' + tags[e]["content"] + '</span>'
                    }
                    $('#ner-ret').html(ner_ret);
                } else if ( stmtsActIndex == 2 ){//依存文法
                    jsPlumb.deleteEveryEndpoint();

                    var drawConfig = {
                        words: json_data.result,
                        plumb: jsPlumb,
                        container: "plumb-ret",
                        left: 10,
                        margin: 100,
                        sourceAnchors: [
                            [ 0.2, 0, 0, -1, 0, 0 ],
                            [ 0.3, 0, 0, -1, 0, 0 ],
                            [ 0.4, 0, 0, -1, 0, 0 ],
                            [ 0.5, 0, 0, -1, 0, 0 ]
                        ],
                        targetAnchors: [
                            [ 0.6, 0, 0, -1, 0, 0 ],
                            [ 0.7, 0, 0, -1, 0, 0 ],
                            [ 0.8, 0, 0, -1, 0, 0 ],
                            [ 0.9, 0, 0, -1, 0, 0 ],
                        ]
                    };

                    var dp = new Dp( drawConfig );
                    dp.drawFlowChart();

                } else if ( stmtsActIndex == 3 ){//情感分析
                    jsPlumb.deleteEveryEndpoint();

                    var pv = json_data.result[0]['qn'] + json_data.result[0]['qp'] != 0 ? parseFloat( json_data.result[0]['qp'] / ( json_data.result[0]['qn'] + json_data.result[0]['qp'] ) ).toFixed(4) : 0.0000;//正面
                    var nv = json_data.result[0]['qn'] + json_data.result[0]['qp'] != 0 ? parseFloat( json_data.result[0]['qn'] / ( json_data.result[0]['qn'] + json_data.result[0]['qp'] ) ).toFixed(4) : 0.0000;//负面
                    saChart.setOption({
                        series:[{
                            data:[{value:pv, name:"正面指数"} , {value:nv, name:"负面指数"}]
                        }]
                    });
                }
            }
        });
    }
JS;
    app\assets\NLPAdminAsset::addScript($this, '@web/admin/js/echarts.common.min.js');
    app\assets\NLPAdminAsset::addScript($this, '@web/sl/lib/jsplumb/jsplumb.min.js');
    app\assets\NLPAdminAsset::addScript($this, '@web/nlp/js/dp.js');
    // app\assets\PRSAdminAsset::addScript($this, '@web/admin/js/echarts.js');
    // app\assets\PRSAdminAsset::addScript($this, '@web/admin/js/echarts.simple.min.js');
    $this->registerJs($wcJs);
?>
<div id="apiResult-0">
    <div class="basic-block ei-panel">
        <span class="title-prefix-md">词性分析</span>

        <div class="ei-dl clearfix">
            <div id="wc-ret">

            </div>
        </div>
    </div>
    <div class="basic-block ei-example">
        <span class="title-prefix-md">词性类别图示</span>

        <div class="ei-dr clearfix">
            <div class="color-palette">
                <?php
                    $word_class_set = array_flip( array_flip( Yii::$app->getModule('nlp')->params['WORD_CLASS_TAG_SET'] ));
                    foreach($word_class_set as $tk => $tv){
                        echo '<span class="wc-' . $tk . '">' . $tv . '</span>';
                    }
                ?>
                <span class="wc-other">其他</span>
            </div>
        </div>
    </div>
</div>

<div id="apiResult-1" style="display: none;">
    <div class="basic-block ei-panel">
        <span class="title-prefix-md">实体识别</span>

        <div class="ei-dl clearfix">
            <div id="ner-ret">

            </div>
        </div>

    </div>

    <div class="basic-block ei-example">
        <span class="title-prefix-md">实体识别图示</span>

        <div class="ei-dr clearfix">
            <div class="color-palette">
                <?php
                    $word_class_set = array_flip( array_flip( Yii::$app->getModule('nlp')->params['NAME_ENTITY_RECOGNIZE_SET'] ));
                    foreach($word_class_set as $tk => $tv){
                        echo '<span class="ner-' . $tk . '">' . $tv . '</span>';
                    }
                ?>
                <span class="ner-other">其他</span>
            </div>
        </div>

    </div>
</div>

<div id="apiResult-2" style="display: none;">
    <div class="basic-block ei-panel dp-panel">
        <div class="clearfix">
        <span class="title-prefix-md">依存文法</span>
        </div>

        <div class="dp-dl clearfix">
            <div id="plumb-ret"></div>
            <div class="dp-readme">
                <h3>依存句法关系说明</h3>
                <table border="1" class="docutils">
                    <colgroup>
                        <col width="16%">
                        <col width="7%">
                        <col width="38%">
                        <col width="38%">
                    </colgroup>
                    <thead valign="bottom">
                    <tr class="row-odd">
                        <th class="head">关系类型</th>
                        <th class="head">Tag</th>
                        <th class="head">Description</th>
                        <th class="head">Example</th>
                    </tr>
                    </thead>
                    <tbody valign="top">
                        <tr class="row-even">
                            <td>主谓关系</td>
                            <td>SBV</td>
                            <td>subject-verb</td>
                            <td>我送她一束花 (我 &lt;– 送)</td>
                        </tr>
                        <tr class="row-odd">
                            <td>动宾关系</td>
                            <td>VOB</td>
                            <td>直接宾语，verb-object</td>
                            <td>我送她一束花 (送 –&gt; 花)</td>
                        </tr>
                        <tr class="row-even">
                            <td>间宾关系</td>
                            <td>IOB</td>
                            <td>间接宾语，indirect-object</td>
                            <td>我送她一束花 (送 –&gt; 她)</td>
                        </tr>
                        <tr class="row-odd">
                            <td>前置宾语</td>
                            <td>FOB</td>
                            <td>前置宾语，fronting-object</td>
                            <td>他什么书都读 (书 &lt;– 读)</td>
                        </tr>
                        <tr class="row-even">
                            <td>兼语</td>
                            <td>DBL</td>
                            <td>double</td>
                            <td>他请我吃饭 (请 –&gt; 我)</td>
                        </tr>
                        <tr class="row-odd">
                            <td>定中关系</td>
                            <td>ATT</td>
                            <td>attribute</td>
                            <td>红苹果 (红 &lt;– 苹果)</td>
                        </tr>
                        <tr class="row-even">
                            <td>状中结构</td>
                            <td>ADV</td>
                            <td>adverbial</td>
                            <td>非常美丽 (非常 &lt;– 美丽)</td>
                        </tr>
                        <tr class="row-odd">
                            <td>动补结构</td>
                            <td>CMP</td>
                            <td>complement</td>
                            <td>做完了作业 (做 –&gt; 完)</td>
                        </tr>
                        <tr class="row-even">
                            <td>并列关系</td>
                            <td>COO</td>
                            <td>coordinate</td>
                            <td>大山和大海 (大山 –&gt; 大海)</td>
                        </tr>
                        <tr class="row-odd">
                            <td>介宾关系</td>
                            <td>POB</td>
                            <td>preposition-object</td>
                            <td>在贸易区内 (在 –&gt; 内)</td>
                        </tr>
                        <tr class="row-even">
                            <td>左附加关系</td>
                            <td>LAD</td>
                            <td>left adjunct</td>
                            <td>大山和大海 (和 &lt;– 大海)</td>
                        </tr>
                        <tr class="row-odd">
                            <td>右附加关系</td>
                            <td>RAD</td>
                            <td>right adjunct</td>
                            <td>孩子们 (孩子 –&gt; 们)</td>
                        </tr>
                        <tr class="row-even">
                            <td>独立结构</td>
                            <td>IS</td>
                            <td>independent structure</td>
                            <td>两个单句在结构上彼此独立</td>
                        </tr>
                        <tr class="row-odd">
                            <td>核心关系</td>
                            <td>HED</td>
                            <td>head</td>
                            <td>指整个句子的核心</td>
                        </tr>
                    </tbody>
                    </table>
            </div>
        </div>

    </div>
</div>

<div id="apiResult-3" style="display: none;">
    <div class="basic-block sa-panel">
        <span class="title-prefix-md">情感分析</span>

        <div class="sa-dl clearfix">
            <div id="sa-ret" style="width: 330px;height: 330px;"></div>
        </div>

    </div>

    <div class="basic-block sa-example">
        <!--
        <ul class="sa-tab clearfix"><li class="active">通用</li><li>骑车</li><li>厨具</li><li>餐饮</li><li>新闻</li><li>微博</li></ul>
        -->
        <div class="sa-dr clearfix">
            <div class="sa-tag sa-tag-p clearfix">正面</div>
            <div  class="sa-tag clearfix">负面</div>
            <div>负面指数：</div>
            <div>0-1之间的小数，统计语句中的与关键词相关的贬义词，单独计算所有句子消极情感的分值，表示负向情感值，并计算该值占总体情感绝对值的百分比。</div>
            <div>正面指数：</div>
            <div>0-1之间的小数，统计语句中的与关键词相关的褒义词，单独计算所有句子积极情感的分值，表示正向情感值，并计算该值占总体情感绝对值的百分比。</div>
        </div>

    </div>
</div>