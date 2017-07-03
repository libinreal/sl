<?php
    $this->title = '实体识别';
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
            	tags = json_data.result
            	var ner_ret = ''
            	for(var e in tags){
            		ner_ret += '<span class="ner-'+ tags[e]["tag"] +'">' + tags[e]["content"] + '</span>'
            	}
            	$('#ner-ret').html(ner_ret);
            }
        });
    })
JS;
    $this->registerJs($wcJs);
?>
<div id="apiResult-1">
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