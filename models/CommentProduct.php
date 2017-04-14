<?php

namespace app\models;

use Yii;

/**
 * This is the model class for collection "product".
 *
 * @property \MongoDB\BSON\ObjectID|string $_id
 * @property mixed $content
 * @property mixed $code
 * @property mixed $time
 */
class CommentProduct extends \yii\mongodb\ActiveRecord
{
    public $time_ranges;//time_ranges : search condition of `time` field
    /**
     * @inheritdoc
     */
    public static function collectionName()
    {
        return ['comment', 'product'];
    }

    /**
     * @return \yii\mongodb\Connection the MongoDB connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->getModule('ctrl')->spiderMongodb;
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
            '_id' => Yii::t('app/ctrl/spider_data_comment_product', 'ID'),
            'content' => Yii::t('app/ctrl/spider_data_comment_product', 'Content'),
            'code' => Yii::t('app/ctrl/spider_data_comment_product', 'Code'),
            'time' => Yii::t('app/ctrl/spider_data_comment_product', 'Time'),
        ];
    }
}
