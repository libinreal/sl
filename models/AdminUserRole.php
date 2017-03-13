<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%admin_user_role}}".
 *
 * @property integer $id
 * @property string $name
 * @property string $privileges
 * @property integer $stat
 * @property string $remark
 */
class AdminUserRole extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%admin_user_role}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['privileges'], 'required'],
            [['privileges'], 'string'],
            [['stat'], 'integer'],
            [['name'], 'string', 'max' => 32],
            [['remark'], 'string', 'max' => 31],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', '管理员角色id'),
            'name' => Yii::t('app', '管理员角色名'),
            'privileges' => Yii::t('app', '管理员权限'),
            'stat' => Yii::t('app', '启用状态'),
            'remark' => Yii::t('app', '角色备注'),
        ];
    }

    /**
     * @inheritdoc
     * @return AdminUserRoleQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new AdminUserRoleQuery(get_called_class());
    }
}
