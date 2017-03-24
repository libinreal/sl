<?php

namespace app\modules\ctrl\models;

use Yii;
use yii\web\IdentityInterface;
use yii\data\ActiveDataProvider;
use yii\rbac\Item;

class AuthItem extends \app\models\AuthItem
{
    public static function getDb()
    {
        return Yii::$app->getModule('ctrl')->spiderMysql;
    }
    /**
     * Creates data provider instance with search query applied
     * @return [type] [description]
     */
    public function searchRole($params){
        $query = static::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'db'  => Yii::$app->getModule('ctrl')->spiderMysql
        ]);

        $this->load($params);
        if (!$this->validate()) {
            $query->where('1=0');
            return $dataProvider;
        }

        $query->andFilterWhere([
            'rid' => $this->rid,
            'status' => $this->status,
            'auth_key' => $this->auth_key,
            'access_token' => $this->access_token,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'email', $this->email])
            ->andWhere([ 'type' => Item::TYPE_ROLE ]);

        return $dataProvider;
    }

    /**
     * Creates data provider instance with search query applied
     * @return [type] [description]
     */
    public function searchPermissions($params){
        $query = static::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'db'  => Yii::$app->getModule('ctrl')->spiderMysql
        ]);

        $this->load($params);
        if (!$this->validate()) {
            $query->where('1=0');
            return $dataProvider;
        }

        $query->andFilterWhere([
            'rid' => $this->rid,
            'status' => $this->status,
            'auth_key' => $this->auth_key,
            'access_token' => $this->access_token,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'email', $this->email])
            ->andWhere([ 'type' => Item::TYPE_PERMISSION ]);

        return $dataProvider;
    }
}