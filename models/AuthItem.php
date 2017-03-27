<?php

namespace app\models;

use Yii;
use yii\rbac\Item;

/**
 * This is the model class for table "{{%auth_item}}".
 *
 * @property string $name
 * @property integer $type
 * @property string $description
 * @property string $rule_name
 * @property resource $data
 * @property integer $created_at
 * @property integer $updated_at
 */
class AuthItem extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%auth_item}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['type', 'in', 'range' => [Item::TYPE_ROLE, Item::TYPE_PERMISSION]],
            [['name', 'type'], 'required'],
            [['type', 'created_at', 'updated_at'], 'integer'],
            [['description', 'data'], 'string'],
            [['name', 'rule_name'], 'string', 'max' => 150],
            [['rule_name'], 'exist', 'skipOnError' => true, 'targetClass' => AuthRule::className(), 'targetAttribute' => ['rule_name' => 'name']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name' => Yii::t('app/ctrl/auth_item', 'name'),
            'type' => Yii::t('app/ctrl/auth_item', 'type'),
            'description' => Yii::t('app/ctrl/auth_item', 'description'),
            'rule_name' => Yii::t('app/ctrl/auth_item', 'rule_name'),
            'data' => Yii::t('app/ctrl/auth_item', 'data'),
            'created_at' => Yii::t('app/ctrl/auth_item', 'created_at'),
            'updated_at' => Yii::t('app/ctrl/auth_item', 'updated_at'),
        ];
    }

    /**
     * @inheritdoc
     * @return AuthItemQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new AuthItemQuery(get_called_class());
    }
}
