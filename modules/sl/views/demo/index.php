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

    $scheJs = <<<JS

JS;
    // app\assets\SLAdminAsset::addScript($this, '@web/admin/js/echarts.common.min.js');
    $this->registerJs($scheJs);
?>
<div>^_^</div>