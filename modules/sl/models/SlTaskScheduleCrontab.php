<?php

namespace app\modules\sl\models;

use Yii;

/**
 * This is the model class for table "sl_task_schedule_crontab".
 *
 * @property integer $id
 * @property string $name
 * @property string $start_time
 * @property integer $create_time
 * @property double $task_progress
 * @property integer $sche_id
 * @property integer $task_status
 * @property integer $control_status
 */
class SlTaskScheduleCrontab extends \yii\db\ActiveRecord
{
    const CONTROL_STOPPED = 0;
    const CONTROL_STARTED = 1;

    const TASK_STATUS_UNSTARTED = 0;
    const TASK_STATUS_EXECUTING = 1;
    const TASK_STATUS_COMPLETED = 2;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sl_task_schedule_crontab';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'string'],
            ['control_status', 'in', 'range' => [self::CONTROL_STOPPED, self::CONTROL_STARTED]],
            [['start_time'], 'safe'],
            [['task_progress'], 'number'],
            [['create_time'], 'integer'],
            [['sche_id', 'task_status', 'control_status'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '每日任务id，自增',
            'name' => '每日任务名',
            'start_time' => '任务开始的时刻',
            'create_time' => '任务生成时间戳',
            'task_progress' => '任务进度',
            'sche_id' => '计划id',
            'task_status' => '任务状态(0:未启动1:正在进行2:已完成)',
            'control_status' => '控制开关(0:停止1:运行)',
        ];
    }

    public static function getDb()
    {
        return Yii::$app->getModule('sl')->db;
    }

    public function getSchedule()
    {
        return $this->hasOne(SlTaskSchedule::className(), ['id' => 'sche_id'])->from(SlTaskSchedule::tableName() . ' sche');
    }

    public function getSearchQuery()
    {
        $query = static::find();
        $request = Yii::$app->request;

        $this->load( $request->queryParams, '' );
        if (!$this->validate())
        {
            // var_dump( $this->getErrors());exit;
            return false;
        }

        $query->alias('cron')->joinWith('schedule');

        if( $request->post('start_time_s', '') )
        {
            $query->andFilterWhere(['>=', 'cron.start_time', strtotime($request->post('start_time_s', ''))]);
        }
        else if( $request->post('start_time_e', '') )
        {
            $query->andFilterWhere(['<=', 'cron.start_time', strtotime($request->post('start_time_e', ''))]);
        }

        $query->andFilterWhere(['cron.sche_id' => $this->sche_id])
                ->andFilterWhere(['cron.id' => $this->id])
                ->andFilterWhere(['like', 'sche.brand_name', $request->post('brand_name', '')])
                ->andFilterWhere(['like', 'sche.key_words', $request->post('key_words', '')])
                ->andFilterWhere(['like', 'cron.task_status', $this->task_status])
                ->andFilterWhere(['like', 'sche.dt_category', $request->post('dt_category', '')])
                ->andFilterWhere(['like', 'sche.pf_name', $request->post('pf_name', '')])
                ->andFilterWhere(['like', 'sche.class_name', $request->post('class_name', '')])

                ->andFilterWhere(['like', 'cron.name', $this->name]);


        /*$commandQuery = clone $query;
    echo $commandQuery->createCommand()->getRawSql();*/

        return $query;
    }
}