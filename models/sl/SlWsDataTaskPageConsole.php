<?php

namespace app\models\sl;

use Yii;

/**
 * This is the model class for table "sl_ws_data_task_page".
 *
 * @property integer $id
 * @property integer $task_id
 * @property integer $schedule_id
 * @property string $task_name
 * @property string $page_url
 * @property string $skuids
 * @property string $spider_name
 * @property string $spider_ip
 * @property string $cookie
 * @property string $brand1
 * @property string $cate1
 * @property string $cate2
 * @property string $cate3
 * @property integer $state
 * @property string $table_name
 * @property string $add_time
 * @property string $finsh_time
 */
class SlWsDataTaskPageConsole extends SlWsDataTaskPage
{
    public static function getDb()
    {
        return Yii::$app->db;
    }
}