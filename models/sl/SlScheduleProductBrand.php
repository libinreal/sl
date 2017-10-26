<?php

namespace app\models\sl;

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

    public static function primaryKey()
    {
        return ['id'];
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

    public function getProductClass()
    {
        return $this->hasMany(SlScheduleProductClass::className(), ['id' => 'class_id'])
                    ->viaTable(SlScheduleProductClassBrand::tableName() . ' bc', ['brand_id' => 'id'])
                    ->from([
                    'c' => SlScheduleProductClass::tableName()
                    ]);
    }
}
