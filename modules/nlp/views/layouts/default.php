<?php
use yii\helpers\Html;

/* @var $this \yii\web\View */
/* @var $content string */
    app\assets\NLPAdminAsset::register($this);
    $prsColor = ".keys-wrapper{background-color:#8084B4}.left-menu{background-color:#7f387a}.title-prefix-md:before{background-color:#2B3282}.top-nav{color:#fff}.btn-keys{background-color:#8084B4}a:visited,a:link{color:#fff}";
    $this->registerCss($prsColor);

    app\assets\NLPAdminAsset::addScript($this, '@web/admin/lib/selectify/jquery.selectify.js');
    $nlpJs = <<<JS
        $( "select" ).selectify({
                btnText: '',
            });
            $( "select" ).on( "change", function ( ) {
                console.log( "Yes, these events work as they did on the native UI!" );
            });
JS;
    $this->registerJs($nlpJs);

    $this->beginBlock('defaultJs');
    ?>

    $.fn.serializeObject = function(){
            var o = {};
            var a = this.serializeArray();
            $.each(a, function() {

                if (o[this.name] !== undefined) {
                    if (!o[this.name].push) {
                        o[this.name] = [o[this.name]];
                    }
                    o[this.name].push(this.value || '');
                } else {
                    o[this.name] = this.value || '';
                }
            });
            return o;
        }

        var csrfToken = $('meta[name="csrf-token"]').attr("content");

 



    <?php 
    $this->endBlock();
    $this->registerJs($this->blocks['defaultJs'], \yii\web\View::POS_END);
    $this->beginPage();
    ?>
    <!DOCTYPE html>
    <html lang="<?= Yii::$app->language ?>">
    <head>
        <meta charset="<?= Yii::$app->charset ?>"/>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <?= Html::csrfMetaTags() ?>
        <title><?= Html::encode($this->title) ?></title>
        <?php $this->head() ?>
    </head>
    <body>
    <?php 
    $this->beginBody();
    if($this->context->id == 'dict')
        $viewFile = 'dict.php';
    else
        $viewFile = 'content.php';
    ?>


        <?= $this->render(
            'header.php'
        ) ?>

        <?= $this->render(
            'left.php'
        )
        ?>

        <?= $this->render(
            $viewFile,
            ['content' => $content]
        ) ?>
    <?php $this->endBody() ?>
    </body>
    </html>
    <?php $this->endPage() ?>

