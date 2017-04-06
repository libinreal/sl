<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "webspider.ws_task_scheduler_state".
 *
 * @property integer $schedule_id
 * @property integer $getting_number
 * @property integer $total_number
 * @property double $getting_percent
 * @property string $error_log
 * @property integer $error_time
 * @property integer $update_time
 */
class TaskSchedulerState extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%task_scheduler_state}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['schedule_id', 'getting_number', 'total_number', 'error_time', 'update_time'], 'integer'],
            [['getting_percent'], 'number'],
            [['error_log'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'schedule_id' => Yii::t('app', '任务id'),
            'getting_number' => Yii::t('app', '已经抓取的数量'),
            'total_number' => Yii::t('app', '要抓取的数量'),
            'getting_percent' => Yii::t('app', '进度，已抓取/要抓取'),
            'error_log' => Yii::t('app', '出错日志'),
            'error_time' => Yii::t('app', '出错次数'),
            'update_time' => Yii::t('app', '最后一次更新的时间戳'),
        ];
    }

    /**
     * 获取关联task_scheduler表中的记录
     * @return ActiveQuery
     */
    public function getTaskScheduler()
    {
        return $this->hasOne(TaskScheduler::className(), ['scheduler_id' => 'schedule_id' ]);
    }

    /**
     * @inheritdoc
     * @return TaskSchedulerStateQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new TaskSchedulerStateQuery(get_called_class());
    }
}
