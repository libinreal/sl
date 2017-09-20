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

    const RESOLVE_TYPE_UNRESOLVED = 0;
    const RESOLVE_TYPE_RESOLVED = 0;
    const RESOLVE_TYPE_IGNORED = 0;

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
            [['cron_id', 'sche_id', 'abnormal_type', 'resolve_stat', 'add_time'], 'integer'],
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
            'resolve_stat' => '解决状态(0:未解决1:已解决2:忽略)',
            'add_time' => '添加时间',
        ];
    }

    public static function getDb()
    {
        return Yii::$app->getModule('sl')->db;
    }

    public static function getDurationMsg($act_duration , $alert_duration)
    {
        $delay = (float)$act_duration - (float)$alert_duration;
        return "抓取时间{$act_duration}h，预警时间{$alert_duration}h，超时{$delay}h";
    }

    public static function getNumMinMsg($act_num, $alert_min)
    {
        $distance = $alert_min - $act_num;
        return "抓取共{$act_num}条，预警值{$alert_min}条，缺少{$diff}条";
    }

    public static function getNumMaxMsg($act_num, $alert_max)
    {
        $distance = $alert_max - $act_num;
        return "抓取总计{$act_num}条数据，多于预警值{$alert_max}条";
    }
}
