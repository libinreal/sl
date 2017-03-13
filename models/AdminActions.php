<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%admin_actions}}".
 *
 * @property integer $id
 * @property string $name
 * @property string $zname
 */
class AdminActions extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%admin_actions}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'zname'], 'string', 'max' => 68],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', '功能id'),
            'name' => Yii::t('app', '功能名称'),
            'zname' => Yii::t('app', '中文名称'),
        ];
    }

    /**
     * @inheritdoc
     * @return AdminActionsQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new AdminActionsQuery(get_called_class());
    }
}
