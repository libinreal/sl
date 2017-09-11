<?php

namespace app\modules\sl\models;

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
class SlTaskScheduleCrontabAbnormal extends \yii\db\ActiveRecord
{
    const ABNORMAL_TYPE_NONE = 0;
    const ABNORMAL_TYPE_DURATION = 1;
    const ABNORMAL_TYPE_NUM_LESS = 2;
    const ABNORMAL_TYPE_NUM_MORE = 4;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sl_task_schedule_crontab_abnormal';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['cron_id', 'sche_id', 'msg'], 'required'],
            [['cron_id', 'sche_id', 'abnormal_type'], 'integer'],
            [['msg'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '异常日志id',
            'cron_id' => '实际任务id',
            'sche_id' => '计划id',
            'abnormal_type' => '异常类型(0:正常1:爬取时间异常2:爬取数量过小4:爬取数量过大)',
            'msg' => '异常信息',
        ];
    }

    public static function getDb()
    {
        return Yii::$app->getModule('sl')->db;
    }

    public static function getDurationMsg($act_duration , $alert_duration)
    {
        $delay = (float)$act_duration - (float)$alert_duration;
        return "爬取持续时间为$act_duration小时，预警时间$alert_duration小时，超时$delay小时";
    }

    public static function getNumMinMsg($act_num, $alert_min)
    {
        return "抓取总计$act_num条数据，少于预警值$alert_min条";
    }

    public static function getNumMaxMsg($act_num, $alert_max)
    {
        return "抓取总计$act_num条数据，多于预警值$alert_max条";
    }
}
