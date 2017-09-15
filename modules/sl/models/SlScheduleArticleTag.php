<?php

namespace app\modules\sl\models;

use Yii;

/**
 * This is the model class for table "sl_schedule_article_tag".
 *
 * @property integer $id
 * @property string $name
 */
class SlScheduleArticleTag extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sl_schedule_article_tag';
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
            'name' => '标签名字',
        ];
    }
    
    public static function primaryKey()
    {
        return ['id'];
    }
        
    public static function getDb()
    {
        return Yii::$app->getModule('sl')->db;
    }    

    public function getArticleClass()
    {
        return $this->hasMany(SlScheduleArticleClass::className(), ['id' => 'class_id'])
                    ->viaTable(SlScheduleArticleClassTag::tableName() . ' tc', ['tag_id' => 'id'])
                    ->from([
                    'c' => SlScheduleArticleClass::tableName()
                    ]);
    }
}
