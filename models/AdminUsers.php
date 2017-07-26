<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%admin_users}}".
 *
 * @property integer $id
 * @property integer $rid
 * @property integer $status
 * @property string $auth_key
 * @property string $access_token
 * @property string $name
 * @property string $pwd
 * @property string $salt
 * @property string $email
 * @property string $last_login
 */
class AdminUsers extends \yii\db\ActiveRecord
{
    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%admin_users}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['rid'], 'integer'],
            ['status', 'in', 'range' => [self::STATUS_INACTIVE, self::STATUS_ACTIVE]],
            [['auth_key'], 'string', 'max' => 6 ],
            [['access_token'], 'string', 'max' => 43],
            [['name'], 'string', 'max' => 30],
            [['pwd'], 'string', 'max' => 32],
            [['salt'], 'string', 'max' => 6],
            [['email'], 'string', 'max' => 62],
            [['last_login'], 'string', 'max' => 10],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', '管理员id'),
            'rid' => Yii::t('app', '角色id'),
            'status' => Yii::t('app', '状态'),
            'name' => Yii::t('app', '管理员名字'),
            'pwd' => Yii::t('app', '帐号密码'),
            'salt' => Yii::t('app', '密码混淆值'),
            'email' => Yii::t('app', '管理员邮箱'),
            'last_login' => Yii::t('app', '最后登录时间'),
        ];
    }

    /**
     * @inheritdoc
     * @return AdminUsersQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new AdminUsersQuery(get_called_class());
    }
}
