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
    public $parent_name;
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
            [['pid'], 'filterParent', 'when' => function() {
                return !$this->isNewRecord;
            }],
            [['auth_item_name'], 'exist', 'skipOnError' => true, 'targetClass' => AuthItem::className(), 'targetAttribute' => ['auth_item_name' => 'name']],
        ];
    }

    /**
     * Use to loop detected.
     */
    public function filterParent()
    {
        $parent = $this->pid;
        $db = static::getDb();
        $query = (new Query)->select(['pid'])
            ->from(static::tableName())
            ->where('[[id]]=:id');
        while ($parent) {
            if ($this->id == $parent) {
                $this->addError('parent_name', 'Loop detected.');
                return;
            }
            $parent = $query->params([':id' => $parent])->scalar($db);
        }
    }

    /**
     * Get menu parent
     * @return \yii\db\ActiveQuery
     */
    public function getMenuParent()
    {
        return $this->hasOne(AdminMenus::className(), ['id' => 'pid']);
    }

    /**
     * Get menu children
     * @return \yii\db\ActiveQuery
     */
    public function getMenus()
    {
        return $this->hasMany(AdminMenus::className(), ['pid' => 'id']);
    }

    /**
     * Get related permission
     * @return \yii\db\ActiveQuery
     */
    public function getAuthItem()
    {
        return $this->hasOne(AuthItem::className(), ['name' => 'auth_item_name']);
    }

    public static function getMenuSource()
    {
        $tableName = static::tableName();
        return (new \yii\db\Query())
                ->select(['m.id', 'm.name', 'm.url', 'parent_name' => 'p.name'])
                ->from(['m' => $tableName])
                ->leftJoin(['p' => $tableName], '[[m.pid]]=[[p.id]]')
                ->all(static::getDb());
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app/ctrl/admin_menus', 'Id'),
            'pid' => Yii::t('app/ctrl/admin_menus', 'pid'),
            'name' => Yii::t('app/ctrl/admin_menus', 'name'),
            'icon' => Yii::t('app/ctrl/admin_menus', 'icon'),
            'url' => Yii::t('app/ctrl/admin_menus', 'url'),
            'auth_item_name' => Yii::t('app/ctrl/admin_menus', 'auth_item_name'),
            'order' => Yii::t('app/ctrl/admin_menus', 'order'),
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
