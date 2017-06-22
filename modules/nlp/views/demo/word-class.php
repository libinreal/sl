<?php
    $this->title = '词性分析';
    $this->params['breadcrumbs'][] = 'NLP System';
    $this->params['breadcrumbs'][] = '分析结果';
    $this->params['breadcrumbs'][] = $this->title;

    $wcJs = <<<JS
    jQuery('form#stmts-form').on('submit', function (e) {
    	e.preventDefault();
        var wc = $(this);
        $.ajax({
        	crossDomain: true,
            url: wc.attr('action'),
            type: 'post',
            data: wc.serialize(),
            dataType: 'json',
            success: function (json_data) {
            	tags = json_data.result
            	var wc_ret = ''
            	for(var e in tags){
            		wc_ret += '<span class="wc-'+ tags[e]["tag"] +'">' + tags[e]["content"] + '</span>'
            	}
            	$('#wc-ret').html(wc_ret);
            }
        });
    })
JS;
    $this->registerJs($wcJs);
?>
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
                </div>
            </div>

        </div>