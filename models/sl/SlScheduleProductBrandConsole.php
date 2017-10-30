<?php

namespace app\models\sl;

use Yii;

/**
 * This is the model class for table "sl_schedule_product_brand".
 *
 * @property integer $id
 * @property string $name
 */
class SlScheduleProductBrandConsole extends SlScheduleProductBrand
{
   

    public static function getDb()
    {
        return Yii::$app->db;
    }


}
