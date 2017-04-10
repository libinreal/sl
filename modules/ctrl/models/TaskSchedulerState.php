<?php

namespace app\modules\ctrl\models;

use Yii;
use yii\data\ActiveDataProvider;

class TaskSchedulerState extends \app\models\TaskSchedulerState
{
    public function rules()
    {
        return array_merge( [
            [['name', 'state'], 'safe'],
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
        $module = Yii::$app->getModule('ctrl');
        $query = static::find();

        $schedule_table = \app\models\TaskScheduler::tableName();
        $state_table = parent::tableName();

        //获取name, end_time等字段
        $query->joinWith('taskScheduler');
        $query->select( "$state_table.*, $schedule_table.name, $schedule_table.start_time, $schedule_table.end_time");

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'db'  => Yii::$app->getModule('ctrl')->sourceDb
        ]);

        $this->load($params);
        if (!$this->validate()) {
            $query->where('1=0');
            return $dataProvider;
        }

        $delay = $module->params['taskScheduler.stateDelay'];

        if( $this->state === self::STATE_STOPPED ){
            $query->where( 'update_time<:max_delay', [':max_delay' => time() - $delay]  );
        }else if( $this->state === self::STATE_RUNNING ){
            $query->where( 'update_time>=:max_delay', [':max_delay' => time() - $delay]  );
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