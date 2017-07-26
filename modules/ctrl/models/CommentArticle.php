<?php

namespace app\modules\ctrl\models;

use Yii;
use yii\data\ActiveDataProvider;
use app\components\helpers\DateHelper;

class CommentArticle extends \app\models\CommentArticle
{
    public static function getDb()
    {
        return Yii::$app->getModule('ctrl')->spiderMongodb;
    }

    /**
     * Creates data provider instance with search query applied
     * @return [type] [description]
     */
    public function search($params){
        $query = static::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'db'  => Yii::$app->getModule('ctrl')->spiderMongodb
        ]);

        $this->load($params);
        if (!$this->validate()) {
            $query->where('1=0');
            return $dataProvider;
        }

        $query->andFilterWhere([
            '_id' => $this->_id
        ]);

        $query_range = DateHelper::makeBetweenValue( $this->time_ranges );

        $query->andFilterWhere(['like', 'content', $this->content]);
        $query->andFilterWhere(['like', 'code', $this->code]);
        $query->andFilterWhere(['between', 'time', $query_range[0], $query_range[1] ]);

        return $dataProvider;
    }

}