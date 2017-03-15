<?php

namespace app\modules\resource;

/**
 * resource module definition class
 */
class Module extends \yii\base\Module
{
    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'app\modules\resource\controllers';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        \Yii::configure($this, require(__DIR__ . '/config.php'));
    }
}
