<?php

namespace app\models;

use Yii;

/**
 * This is the model class for collection "article".
 *
 * @property \MongoDB\BSON\ObjectID|string $_id
 * @property mixed $content
 * @property mixed $code
 * @property mixed $time
 */
class CommentArticle extends \yii\mongodb\ActiveRecord
{
    public $time_ranges;//time_ranges : search condition of `time` field
    /**
     * @inheritdoc
     */
    public static function collectionName()
    {
        return ['comment', 'article'];
    }

    /**
     * @return \yii\mongodb\Connection the MongoDB connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('spiderMongodb');
    }

    /**
     * @inheritdoc
     */
    public function attributes()
    {
        return [
            '_id',
            'content',
            'code',
            'time',
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['content', 'code', 'time', 'time_ranges'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            '_id' => Yii::t('app/ctrl/spider_data_comment_article', 'Id'),
            'content' => Yii::t('app/ctrl/spider_data_comment_article', 'Content'),
            'code' => Yii::t('app/ctrl/spider_data_comment_article', 'Code'),
            'time' => Yii::t('app/ctrl/spider_data_comment_article', 'Time'),
        ];
    }
}
