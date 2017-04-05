<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "webspider.ws_task_scheduler".
 *
 * @property integer $scheduler_id
 * @property string $module_type
 * @property string $name
 * @property string $start_time
 * @property string $end_time
 * @property integer $status
 * @property integer $group_id
 * @property integer $rule_id
 */
class TaskScheduler extends \yii\db\ActiveRecord
{
    const STATUS_STOPPED = 0;
    const STATUS_RUNNING = 1;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%task_scheduler}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['module_type'], 'required'],
            [['module_type'], 'string'],
            [['start_time', 'end_time'], 'safe'],
            [['status', 'group_id', 'rule_id'], 'integer'],
            [['name'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'scheduler_id' => Yii::t('app/ctrl/task_scheduler', 'scheduler id'),
            'module_type' => Yii::t('app/ctrl/task_scheduler', 'module type'),
            'name' => Yii::t('app/ctrl/task_scheduler', 'name'),
            'start_time' => Yii::t('app/ctrl/task_scheduler', 'start time'),
            'end_time' => Yii::t('app/ctrl/task_scheduler', 'end time'),
            'status' => Yii::t('app/ctrl/task_scheduler', 'status'),
            'group_id' => Yii::t('app/ctrl/task_scheduler', 'group id'),
            'rule_id' => Yii::t('app/ctrl/task_scheduler', 'rule id'),
        ];
    }

    /**
     * @inheritdoc
     * @return TaskSchedulerQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new TaskSchedulerQuery(get_called_class());
    }
}
