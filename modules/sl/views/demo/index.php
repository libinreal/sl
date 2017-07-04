<?php
    $this->title = '任务控制';
    $this->params['breadcrumbs'][] = 'SL System';
    $this->params['breadcrumbs'][] = '计划任务列表';
    $this->params['breadcrumbs'][] = $this->title;

    $scheJs = <<<JS

JS;
    // app\assets\SLAdminAsset::addScript($this, '@web/admin/js/echarts.common.min.js');
    $this->registerJs($scheJs);
?>
<div>^_^</div>