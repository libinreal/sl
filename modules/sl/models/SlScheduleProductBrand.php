<?php

namespace app\modules\sl\models;

use Yii;

/**
 * This is the model class for table "sl_schedule_product_brand".
 *
 * @property integer $id
 * @property string $name
 */
class SlScheduleProductBrand extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sl_schedule_product_brand';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'string', 'max' => 200],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '抓取的品牌id',
            'name' => '抓取的品牌名',
        ];
    }

    public static function getDb()
    {
        return Yii::$app->getModule('sl')->db;
    }

    public function getProductClassId()
    {
        return $this->hasMany(SlScheduleProductClassBrand::className(), ['brand_id' => 'id']);
    }
}
