<?php

namespace app\models\sl;

use Yii;

/**
 * This is the model class for table "sl_schedule_product_class".
 *
 * @property integer $id
 * @property string $name
 */
class SlScheduleProductClass extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sl_schedule_product_class';
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
            'id' => '抓取数据的分类id',
            'name' => '数据分类名',
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
    public function getProductBrand()
    {
        return $this->hasMany(SlScheduleProductBrand::className(), ['id' => 'brand_id'])
                    ->viaTable(SlScheduleProductClassBrand::tableName() . ' cb', ['class_id' => 'id'])
                    ->from([
                        'b' => SlScheduleProductBrand::tableName()
                        ]);
    }


}
