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
    public $name;//task_scheduler.name
    private $_state;//running , stopped

    const STATE_RUNNING = 1;
    const STATE_STOPPED = 0;
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
            'schedule_id' => Yii::t('app/ctrl/task_scheduler_state', 'Schedule id'),
            'getting_number' => Yii::t('app/ctrl/task_scheduler_state', 'Getting number'),
            'total_number' => Yii::t('app/ctrl/task_scheduler_state', 'Total number'),
            'getting_percent' => Yii::t('app/ctrl/task_scheduler_state', 'Getting percent'),
            'error_log' => Yii::t('app/ctrl/task_scheduler_state', 'Error log'),
            'error_time' => Yii::t('app/ctrl/task_scheduler_state', 'Error time'),
            'update_time' => Yii::t('app/ctrl/task_scheduler_state', 'Update time'),
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
     * 获取状态
     * @return string
     */
    public function getState()
    {
        $module = Yii::$app->getModule('ctrl');
        $delay = $module->params['taskScheduler.stateDelay'];
        if( $this->update_time + $delay >= time() ){
            return Yii::t('app/ctrl/task_scheduler_state', 'Running');
        }else{
            return Yii::t('app/ctrl/task_scheduler_state', 'Stopped');
        }
    }

    /**
     * 设置状态
     * @return string
     */
    public function setState( $value )
    {
        $this->_state = intval( $value ) === 1 ? 1 : 0 ;
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
