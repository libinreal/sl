<?php

namespace app\modules\ctrl\models;

use Yii;
use yii\data\ActiveDataProvider;

class TaskScheduler extends \app\models\TaskScheduler
{
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
            'scheduler_id' => $this->scheduler_id,
            'module_type' => $this->text,
            'name' => $this->name,
            'status' => $this->status,
            'group_id' => $this->group_id,
            'rule_id' => $this->rule_id,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name]);

        return $dataProvider;
    }

}