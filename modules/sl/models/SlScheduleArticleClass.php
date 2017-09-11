<?php

namespace app\modules\sl\models;

use Yii;

/**
 * This is the model class for table "sl_schedule_article_class".
 *
 * @property integer $id
 * @property string $name
 */
class SlScheduleArticleClass extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sl_schedule_article_class';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => '计划爬取的标签(关键字)分类',
        ];
    }

    public static function getDb()
    {
        return Yii::$app->getModule('sl')->db;
    }

    /**
     * 获取类对应的品牌
     * @return
     */
    public function getArticleTag()
    {
        return $this->hasMany(SlScheduleArticleTag::className(), ['id' => 'tag_id'])
                    ->viaTable(SlScheduleArticleClassTag::tableName() . ' ct', ['class_id' => 'id'])
                    ->from([
                        't' => SlScheduleArticleTag::tableName()
                        ]);
    }
}
