<?php
use yii\helpers\Html;

/* @var $this \yii\web\View */
/* @var $content string */
    app\assets\SLAdminAsset::register($this);
    $this->beginBlock('scheJs');
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
        var tempLoginCookie = <?php echo \Yii::$app->request->cookies->getValue('log_state', 0); ?>;
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

