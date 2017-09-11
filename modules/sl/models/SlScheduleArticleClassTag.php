<?php

namespace app\modules\sl\models;

use Yii;

/**
 * This is the model class for table "sl_schedule_article_class_tag".
 *
 * @property integer $tag_id
 * @property integer $class_id
 */
class SlScheduleArticleClassTag extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sl_schedule_article_class_tag';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['tag_id', 'class_id'], 'integer'],
            [['tag_id', 'class_id'], 'unique', 'targetAttribute' => ['tag_id', 'class_id'], 'message' => 'The combination of 抓取的文章标签(关键字)id and 抓取的文章标签(关键字)分类id has already been taken.'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'tag_id' => '抓取的文章标签(关键字)id',
            'class_id' => '抓取的文章标签(关键字)分类id',
        ];
    }
    
    public static function getDb()
    {
        return Yii::$app->getModule('sl')->db;
    }    
}
