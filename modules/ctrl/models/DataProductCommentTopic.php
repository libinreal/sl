<?php

namespace app\modules\ctrl\models;

use Yii;
use yii\data\ActiveDataProvider;

class DataProductCommentTopic extends \app\models\DataProductCommentTopic
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
            'id' => $this->id,
            'scheduler_id' => $this->scheduler_id,
        ]);

        $query->andFilterWhere(['like', 'product_code', $this->product_code])
            ->andFilterWhere(['like', 'product_brand', $this->product_brand])
            ->andFilterWhere(['like', 'product_name', $this->product_name])
            ->andFilterWhere(['like', 'product_title', $this->product_title])
            ->andFilterWhere(['like', 'keyword', $this->keyword])
            ->andFilterWhere(['like', 'product_cate1', $this->product_cate1])
            ->andFilterWhere(['like', 'product_cate2', $this->product_cate2])
            ->andFilterWhere(['like', 'product_cate3', $this->product_cate3])
            ->andFilterWhere(['like', 'product_attr1', $this->product_attr1])
            ->andFilterWhere(['like', 'product_attr2', $this->product_attr2])
            ->andFilterWhere(['like', 'product_attr3', $this->product_attr3])
            ->andFilterWhere(['like', 'product_addr', $this->product_addr]);

        return $dataProvider;
    }

}