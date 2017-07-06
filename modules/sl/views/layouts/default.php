<?php
use yii\helpers\Html;

/* @var $this \yii\web\View */
/* @var $content string */
    app\assets\SLAdminAsset::register($this);


    app\assets\SLAdminAsset::addScript($this, '@web/admin/lib/selectify/jquery.selectify.js');
    app\assets\SLAdminAsset::addScript($this, '@web/sl/lib/sui/sui.js');
    $this->beginBlock('scheJs');
?>
        $( ".prs__select" ).selectify({
            btnText: '',
            classes: {
                container: "prs__select sl-container"
            }
        });
        $( ".select" ).each(function(i){
            var className = $(this).attr('class');
            $(this).selectify({
                btnText: '',
                classes: {
                    container: className+ ' sl-container'
                }
            });
        })
        $( "select" ).on( "change", function ( ) {
            console.log( "Yes, these events work as they did on the native UI!" );
        });
<?php
    $this->endBlock();
    $this->registerJs($this->blocks['scheJs'], \yii\web\View::POS_END);
    $this->beginPage()
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

