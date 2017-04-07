<?php

namespace app\modules\ctrl\models;

use Yii;
use yii\data\ActiveDataProvider;

class TaskSchedulerState extends \app\models\TaskSchedulerState
{
    public function rules()
    {
        return array_merge( [
            ['name', 'safe'],
        ], parent::rules() );
    }

    public static function getDb()
    {
        return Yii::$app->getModule('ctrl')->sourceDb;
    }
    /**
     * Creates data provider instance with search query applied
     * @return [type] [description]
     */
    public function search($params){
        $query = static::find();

        //获取name, end_time等字段
        $query->joinWith('task_scheduler');
        $query->select('task_scheduler_state.*, task_scheduler.name, task_scheduler.start_time,task_scheduler.end_time, task_scheduler.update_time');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'db'  => Yii::$app->getModule('ctrl')->sourceDb
        ]);

        $this->load($params);
        if (!$this->validate()) {
            $query->where('1=0');
            return $dataProvider;
        }

        $query->andFilterWhere([
            'schedule_id' => $this->schedule_id,
            'error_log' => $this->error_log,
            'name' => $this->name,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name]);

        return $dataProvider;
    }

}