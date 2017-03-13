<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%admin_privileges}}".
 *
 * @property integer $id
 * @property string $name
 * @property integer $action_id
 */
class AdminPrivileges extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%admin_privileges}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['action_id'], 'integer'],
            [['name'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', '权限id'),
            'name' => Yii::t('app', '权限名'),
            'action_id' => Yii::t('app', '关联功能表ID'),
        ];
    }

    /**
     * @inheritdoc
     * @return AdminPrivilegesQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new AdminPrivilegesQuery(get_called_class());
    }
}
