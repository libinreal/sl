<?php

namespace app\modules\sl\models;

use Yii;

/**
 * This is the model class for table "sl_task_item".
 *
 * @property integer $id
 * @property integer $sche_id
 * @property string $name
 * @property integer $pf_name
 * @property string $brand_name
 * @property string $class_name
 * @property integer $dt_category
 * @property string $key_words
 * @property integer $task_status
 * @property double $task_progress
 * @property integer $task_time
 * @property integer $update_time
 * @property integer $complete_time
 * @property double $data_number
 * @property string $cookie
 * @property string $user_agent
 * @property string $spider_name
 */
class SlTaskItemConsole extends SlTaskItem
{


    public static function getDb()
    {
        return Yii::$app->db;
    }
}
