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
        return 'webspider.ws_task_scheduler';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['module_type'], 'required'],
            [['start_time', 'end_time'], 'safe'],
            [['status', 'group_id', 'rule_id'], 'integer'],
            [['module_type', 'name'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'scheduler_id' => Yii::t('app', '任务主键'),
            'module_type' => Yii::t('app', '模块类型'),
            'name' => Yii::t('app', '任务名称'),
            'start_time' => Yii::t('app', '开始时间'),
            'end_time' => Yii::t('app', '结束时间'),
            'status' => Yii::t('app', '当前状态(0:停止;1:采集)'),
            'group_id' => Yii::t('app', '分组id（新闻类，论坛类）'),
            'rule_id' => Yii::t('app', '规则id'),
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
