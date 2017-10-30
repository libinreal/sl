<?php

namespace app\models\sl;

use Yii;

/**
 * This is the model class for table "sl_schedule_product_class".
 *
 * @property integer $id
 * @property string $name
 */
class SlScheduleProductClassConsole extends SlScheduleProductClass
{
    public static function getDb()
    {
        return Yii::$app->db;
    }

}
