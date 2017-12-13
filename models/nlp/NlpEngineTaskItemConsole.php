<?php

namespace app\models\nlp;

use Yii;

/**
 * This is the model class for table "sl_global_settings".
 *
 * @property integer $id
 * @property integer $parent_id
 */
class NlpEngineTaskItemConsole extends NlpEngineTaskItem
{
    public static function getDb()
    {
        return Yii::$app->db;
    }
}
