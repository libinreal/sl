<?php

namespace app\modules\sl\models;

use Yii;

/**
 * This is the model class for table "sl_schedule_product_class_brand".
 *
 * @property integer $class_id
 * @property integer $brand_id
 */
class SlScheduleProductClassBrand extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sl_schedule_product_class_brand';
    }

    public static function primaryKey()
    {
        return ['class_id', 'brand_id'];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['class_id', 'brand_id'], 'required'],
            [['class_id', 'brand_id'], 'integer'],
            [['class_id', 'brand_id'], 'unique', 'targetAttribute' => ['class_id', 'brand_id'], 'message' => 'The combination of 抓取的产品分类id and 抓取的产品品牌id has already been taken.'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'class_id' => '抓取的产品分类id',
            'brand_id' => '抓取的产品品牌id',
        ];
    }

    public static function getDb()
    {
        return Yii::$app->getModule('sl')->db;
    }

    /**
     * * 获取关联品牌名
     * @return [type] [description]
     */
    public function getProductBrand()
    {
        return $this->hasMany(SlScheduleProductBrand::className(), ['id' => 'brand_id'])->from(SlScheduleProductBrand::tableName() . ' b');
    }

    /**
     * 获取关联分类名
     * @return [type] [description]
     */
    public function getProductClass()
    {
        return $this->hasMany(SlScheduleProductClass::className(), ['id' => 'class_id'])->from(SlScheduleProductClass::tableName() . ' c');
    }
}
