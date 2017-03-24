<?php

namespace app\modules\ctrl;

use Yii;
/**
 * ctrl module definition class
 */
class Module extends \yii\base\Module
{
    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'app\modules\ctrl\controllers';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        Yii::configure($this, require(__DIR__ . '/config.php'));
        Yii::$app->language = 'zh-CN';
    }
}
