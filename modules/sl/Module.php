<?php

namespace app\modules\nlp;
use Yii;

/**
 * nlp module definition class
 */
class Module extends \yii\base\Module
{
    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'app\modules\nlp\controllers';

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
