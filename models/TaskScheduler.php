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
            'scheduler_id' => Yii::t('app', 'scheduler id'),
            'module_type' => Yii::t('app', 'module type'),
            'name' => Yii::t('app', 'name'),
            'start_time' => Yii::t('app', 'start time'),
            'end_time' => Yii::t('app', 'end time'),
            'status' => Yii::t('app', 'status'),
            'group_id' => Yii::t('app', 'group id'),
            'rule_id' => Yii::t('app', 'rule id'),
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
