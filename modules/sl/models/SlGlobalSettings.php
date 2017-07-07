<?php

namespace app\modules\sl\models;

use Yii;

/**
 * This is the model class for table "sl_global_settings".
 *
 * @property integer $id
 * @property integer $parent_id
 * @property string $code
 * @property string $type
 * @property string $value
 */
class SlGlobalSettings extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sl_global_settings';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['parent_id', 'sort_order'], 'integer'],
            [['value'], 'required', 'on' => 'update'],
            [['value'], 'string'],
            [['code', 'type'], 'string', 'max' => 100],
            [['code'], 'unique'],
        ];
    }

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios['update'] = ['value'];
        return $scenarios;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'parent_id' => 'Parent ID',
            'code' => '配置项唯一代码',
            'type' => 'Type',
            'value' => '配置项值',
            'sort_order' => '顺序大小',
        ];
    }

    /**
     * 获取父项下对应的子项
     * @return [type] [description]
     */
    public function getChildren()
    {
        return $this->hasMany(self::className(), ['parent_id' => 'id'])->from(self::tableName() . ' child_set');
    }

    public static function getDb()
    {
        return Yii::$app->getModule('sl')->db;
    }
}
