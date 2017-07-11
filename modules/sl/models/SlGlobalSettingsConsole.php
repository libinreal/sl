<?php

namespace app\modules\sl\models;

use Yii;

/**
 * This is the model class for table "sl_global_settings".
 *
 * @property integer $id
 * @property integer $parent_id
 * @property string $code
 * @property string $type
 * @property string $value
 */
class SlGlobalSettingsConsole extends SlGlobalSettings
{
    public static function getDb()
    {
        return Yii::$app->db;
    }
}
