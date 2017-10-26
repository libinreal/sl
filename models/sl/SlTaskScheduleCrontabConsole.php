<?php

namespace app\models\sl;

use Yii;

/**
 * This is the model class for table "sl_task_schedule".
 *
 * @property integer $id
 * @property string $name
 * @property string $pf_name
 * @property string $brand_name
 * @property string $class_name
 * @property string $key_words
 * @property integer $sche_status
 * @property double $sche_progress
 * @property integer $sche_type
 * @property string $sche_time
 * @property string $dt_category
 * @property integer $update_time
 * @property double $data_number
 * @property integer $task_number
 * @property string $cookie
 * @property string $user_agent
 */
class SlTaskScheduleCrontabConsole extends SlTaskScheduleCrontab
{
    public static function getDb()
    {
        return Yii::$app->db;
    }

    public function getSchedule()
    {
        return $this->hasOne(SlTaskScheduleConsole::className(), ['id' => 'sche_id'])->from(SlTaskScheduleConsole::tableName() . ' sche');
    }
}
