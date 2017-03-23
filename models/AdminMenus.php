<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%admin_menus}}".
 *
 * @property integer $id
 * @property integer $pid
 * @property string $name
 * @property string $icon
 * @property string $url
 * @property string $auth_item_name
 * @property integer $order
 */
class AdminMenus extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%admin_menus}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['pid', 'order'], 'integer'],
            [['name', 'auth_item_name'], 'string', 'max' => 150],
            [['icon'], 'string', 'max' => 30],
            [['url'], 'string', 'max' => 100],
            [['pid'], 'exist', 'skipOnError' => true, 'targetClass' => AdminMenus::className(), 'targetAttribute' => ['pid' => 'id']],
            [['auth_item_name'], 'exist', 'skipOnError' => true, 'targetClass' => AuthItem::className(), 'targetAttribute' => ['auth_item_name' => 'name']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', '菜单id'),
            'pid' => Yii::t('app', '上级菜单id'),
            'name' => Yii::t('app', '显示名字'),
            'icon' => Yii::t('app', '菜单的icon'),
            'url' => Yii::t('app', '路由地址'),
            'auth_item_name' => Yii::t('app', '绑定到的权限名，关联auth_item表的name'),
            'order' => Yii::t('app', '菜单序号'),
        ];
    }

    /**
     * @inheritdoc
     * @return AdminMenusQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new AdminMenusQuery(get_called_class());
    }
}
