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

        // Yii::configure($this, require(__DIR__ . '/config.php'));
        Yii::configure($this, require(__DIR__ . '/config.work.php'));
        Yii::$app->language = 'zh-CN';

        $this->authManager->db = $this->spiderMysql;
        $this->authManager->cache = $this->spiderMongodb;
    }
}
