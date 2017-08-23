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

    ?>
    <?php $this->beginPage() ?>
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
    <?php $this->beginBody() ?>


        <?= $this->render(
            'header.php'
        ) ?>

        <?= $this->render(
            'left.php'
        )
        ?>

        <?= $this->render(
            'content.php',
            ['content' => $content]
        ) ?>
    <?php $this->endBody() ?>
    </body>
    </html>
    <?php $this->endPage() ?>

