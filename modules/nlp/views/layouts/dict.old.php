<?php
use app\components\helpers\HtmlHelper;

$breadcrumbsJs = <<<EOT
//面包屑 START 

$('.xbreadcrumbs').xBreadcrumbs();

//面包屑  END

EOT;
$this->registerJs($breadcrumbsJs);

$dictCss = <<<EOT
    /** jsUploadControl **/
    .JSUploadForm .JSFileChoos, .startJSuploadButton{
        background-color: #2B3282;
    }
    #dicFileUpload {
        margin-bottom: 12px;
        /* height: 300px; */
        
    }
    .JSUploadForm {
        border-radius: 5px; 
        
    }
EOT;
$this->registerCss($dictCss);

?>
<div class="content">
    <div class="nav-path">
        <div class="np-prs">NLP</div>
        <div class="prs-left">
           <?php echo HtmlHelper::renderBreadcrumbs( $this->params['breadcrumbs'], 'xbreadcrumbs' ); ?>
        </div>
        <div class="prs-right">
            <div class="icon icon-mail"></div>
            <div class="icon icon-star"></div>
        </div>
    </div>

    <?= $content ?>

</div>    