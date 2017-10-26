<?php

namespace app\models\sl;

use Yii;

/**
 * This is the model class for table "sl_task_schedule_crontab_abnormal".
 *
 * @property integer $id
 * @property integer $cron_id
 * @property integer $sche_id
 * @property integer $abnormal_type
 * @property string $msg
 */
class SlTaskScheduleCrontabAbnormalConsole extends SlTaskScheduleCrontabAbnormal
{
    public static function getDb()
    {
        return Yii::$app->db;
    }
}
