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
        color: ['#be94bc', '#944d8f']

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

                    var dpWord = json_data.result,
                        dpHtml = '<div class="dp-item" id="dp--1">Root</div>'
                        /*sourceArr = [],
                        targetArr = []*/

                    console.log("json data "+JSON.stringify(dpWord));

                    // init sourceArr , targetArr
                    for (var _k in dpWord)
                    {
                        //sourceArr.push(String(_k));
                        //targetArr.push(String(dpWord[_k].head));
                        dpHtml += "<div class='dp-item' id='dp-"+ _k +"'>" + dpWord[_k].content + "</div>"
                    }

                    $('#plumb-ret').html(dpHtml)
                    $('#dp--1').css('left', 10);

                    for (var _k in dpWord)
                    {
                        $('#dp-'+_k).css('left', 140 * (parseInt(_k) + 1));
                    }

                    //jsPlumb.ready(function () {

                        var sourceAnchors = [
                            [ 0.45, 0, 0, -1, 0, 0 ],
                            [ 0.46, 0, 0, -1, 0, 0 ],
                            [ 0.47, 0, 0, -1, 0, 0 ],
                            [ 0.48, 0, 0, -1, 0, 0 ],
                            [ 0.49, 0, 0, -1, 0, 0 ]
                        ],
                        targetAnchors = [
                            [ 0.65, 0, 0, -1, 0, 0 ],
                            [ 0.64, 0, 0, -1, 0, 0 ],
                            [ 0.63, 0, 0, -1, 0, 0 ],
                            [ 0.62, 0, 0, -1, 0, 0 ],
                            [ 0.61, 0, 0, -1, 0, 0 ]
                        ]

                        var instance = window.jsp = jsPlumb.getInstance({
                            // the overlays to decorate each connection with.  note that the label overlay uses a function to generate the label text; in this
                            // case it returns the 'labelText' member that we set on each connection in the 'init' method below.
                            ConnectionOverlays: [
                                [ "Arrow", {
                                    location: 1,
                                    visible:true,
                                    width:11,
                                    length:11,
                                    id:"ARROW",
                                    events:{
                                        click:function() { console.log("you clicked on the arrow overlay")}
                                    }
                                } ],
                                [ "Label", {
                                    location: 0.9,
                                    id: "label",
                                    cssClass: "aLabel",
                                    events:{
                                        tap:function() { console.log("hey"); }
                                    }
                                }]
                            ],
                            Container: "plumb-ret"
                        });

                        var basicType = {
                            connector: "StateMachine",
                            paintStyle: { stroke: "red", strokeWidth: 4 },
                            hoverPaintStyle: { stroke: "#428bca" },
                            overlays: [
                                "Arrow"
                            ]
                        };
                        //instance.registerConnectionType("basic", basicType);

                        // this is the paint style for the connecting lines..
                        var connectorPaintStyle = {
                                strokeWidth: 1,
                                stroke: "#61B7CF",
                                joinstyle: "round",
                                outlineStroke: "white",
                                outlineWidth: 2
                            },
                        // .. and this is the hover style.
                        connectorHoverStyle = {
                            strokeWidth: 3,
                            stroke: "#216477",
                            outlineWidth: 5,
                            outlineStroke: "white"
                        },
                        endpointHoverStyle = {
                            fill: "#216477",
                            stroke: "#216477"
                        },
                        // the definition of source endpoints (the small blue ones)
                        sourceEndpoint = {
                            endpoint: "Dot",
                            paintStyle: {
                                stroke: "#7AB02C",
                                fill: "transparent",
                                radius: 1,
                                strokeWidth: 1
                            },
                            isSource: true,
                            connectorStyle: connectorPaintStyle,
                            anchor:["Top"],
                            hoverPaintStyle: endpointHoverStyle,
                            connectorHoverStyle: connectorHoverStyle,
                            dragOptions: {},
                            overlays: [
                                [ "Label", {
                                    location: [0.5, 1.5],
                                    label: "Drag",
                                    cssClass: "endpointSourceLabel",
                                    visible:false
                                } ]
                            ],
                            maxConnections: -1
                        },
                        // the definition of target endpoints (will appear when the user drags a connection)
                        //anchor:["Perimeter",{"shape":"Circle"}],
                        targetEndpoint = {
                            endpoint: "Dot",
                            paintStyle: { fill: "#7AB02C", radius: 1 },
                            hoverPaintStyle: endpointHoverStyle,
                            anchor:["Top"],
                            maxConnections: -1,
                            dropOptions: { hoverClass: "hover", activeClass: "active" },
                            isTarget: true,
                            overlays: [
                                [ "Label", { location: [0.5, -0.5], label: "Drop", cssClass: "endpointTargetLabel", visible:false } ]
                            ]
                        },
                        init = function (connection) {
                            var _k = parseInt(connection.targetId.substring(3));
                            //console.log(' init targetId ' + connection.targetId + '  ' + _k + '     ' + JSON.stringify(dpWord[_k]) );
                            connection.getOverlay("label").setLabel( dpWord[_k].relate );
                        };

                        var _addEndpoints = function (sourceId, toId) {

                                var sourceUUID = "s" + sourceId;
                                instance.addEndpoint("dp-" + sourceId, sourceEndpoint, {
                                    anchor: sourceAnchors, uuid: sourceUUID, connector: [ "Flowchart", { stub: 30 + 10 * Math.abs(parseInt(sourceId) - parseInt(toId)) , gap: 1, cornerRadius: 3, alwaysRespectStubs: true } ],
                                });
                                //console.log("sourceUUID "  + sourceUUID);


                                var targetUUID = "t" + toId;
                                instance.addEndpoint("dp-" + toId, targetEndpoint, { anchor: targetAnchors, uuid: targetUUID });
                                //console.log("targetUUID "  + targetUUID);

                        };

                        // suspend drawing and initialise.
                        instance.batch(function () {

                            for (var _k in dpWord)
                            {
                                _addEndpoints(String(dpWord[_k].head), String(_k));
                            }

                            // listen for new connections; initialise them the same way we initialise the connections at startup.
                            instance.bind("connection", function (connInfo, originalEvent) {
                                init(connInfo.connection);
                            });

                            // make all the window divs draggable
                            //instance.draggable(jsPlumb.getSelector(".flowchart-demo .window"), { grid: [20, 20] });
                            // THIS DEMO ONLY USES getSelector FOR CONVENIENCE. Use your library's appropriate selector
                            // method, or document.querySelectorAll:
                            //jsPlumb.draggable(document.querySelectorAll(".window"), { grid: [20, 20] });

                            // connect a few up
                            //instance.connect({uuids: ["Window2BottomCenter", "Window3TopCenter"], editable: true});
                            for (var _k in dpWord)
                            {
                                instance.connect({uuids: [ "s" + dpWord[_k].head, "t" + _k]});
                            }

                            //

                            //
                            // listen for clicks on connections, and offer to delete connections on click.
                            //
                            instance.bind("click", function (conn, originalEvent) {
                               // if (confirm("Delete connection from " + conn.sourceId + " to " + conn.targetId + "?"))
                                 //   instance.detach(conn);
                               //conn.toggleType("basic");
                            });

                            /*instance.bind("connectionDrag", function (connection) {
                                console.log("connection " + connection.id + " is being dragged. suspendedElement is ", connection.suspendedElement, " of type ", connection.suspendedElementType);
                            });

                            instance.bind("connectionDragStop", function (connection) {
                                console.log("connection " + connection.id + " was dragged");
                            });

                            instance.bind("connectionMoved", function (params) {
                                console.log("connection " + params.connection.id + " was moved");
                            });*/
                        });

                        //jsPlumb.fire("jsPlumbDemoLoaded", instance);
                    //});

                } else if ( stmtsActIndex == 3 ){//情感分析
                    jsPlumb.deleteEveryEndpoint();

                    var pv = json_data.result[0]['qp'];//正面
                    var nv = json_data.result[0]['qn'];//负面
                    saChart.setOption({
                        series:[{
                            data:[{value:pv, name:"正面情感"} , {value:nv, name:"负面情感"}]
                        }]
                    });
                }
            }
        });
    }
JS;
    app\assets\NLPAdminAsset::addScript($this, '@web/admin/js/echarts.common.min.js');
    app\assets\NLPAdminAsset::addScript($this, '@web/sl/lib/jsplumb/jsplumb.min.js');
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
            <div>负面情感值。</div>
            <div>正面指数：</div>
            <div>正面情感值。</div>
        </div>

    </div>
</div>