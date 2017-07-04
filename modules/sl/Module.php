<?php

namespace app\modules\sl;
use Yii;

/**
 * sl module definition class
 */
class Module extends \yii\base\Module
{
    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'app\modules\sl\controllers';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        // custom initialization code goes here
        Yii::configure($this, require(__DIR__ . '/config.php'));
        Yii::$app->language = 'zh-CN';

        /*$this->authManager->db = $this->mysql;
        $this->authManager->cache = $this->mongodb;*/
    }
}
