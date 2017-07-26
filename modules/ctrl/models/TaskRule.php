<?php

namespace app\modules\ctrl\models;

use Yii;
use yii\data\ActiveDataProvider;

class TaskRule extends \app\models\TaskRule
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
            'rule_id' => $this->rule_id,
            'site' => $this->site,
            'type' => $this->auth_key,
            'delay' => $this->access_token,
            'encode' => $this->access_token,
            'auto_proxy' => $this->access_token,
            'db_id' => $this->access_token,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'email', $this->email])
            ->andWhere([ 'type' => Item::TYPE_ROLE ]);

        return $dataProvider;
    }

}