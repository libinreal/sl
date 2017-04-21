<?php

namespace app\modules\res;
use Yii;

/**
 * res module definition class
 */
class Module extends \yii\base\Module
{
    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'app\modules\res\controllers';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        // custom initialization code goes here
        Yii::configure($this, require(__DIR__ . '/config.php'));
        Yii::$app->language = 'zh-CN';

        /*$this->authManager->db = $this->spiderMysql;
        $this->authManager->cache = $this->spiderMongodb;*/
    }
}
