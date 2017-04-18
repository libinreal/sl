<?php

namespace app\modules\ctrl\models;

use Yii;
use yii\data\ActiveDataProvider;
use app\components\helpers\DateHelper;

class DataArticleTopic extends \app\models\DataArticleTopic
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
            'area' => $this->area,
        ]);

        $query_range = DateHelper::makeBetweenValue( $this->time_ranges );

        $query->andFilterWhere(['like', 'article_code', $this->article_code])
            ->andFilterWhere(['like', 'site_name', $this->site_name])
            ->andFilterWhere(['like', 'url', $this->url])
            ->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like', 'keyword', $this->keyword])
            ->andFilterWhere(['like', 'author', $this->author])
            ->andFilterWhere(['between', 'createtime', $query_range[0], $query_range[1]]);

        return $dataProvider;
    }

}