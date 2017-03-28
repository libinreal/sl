<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "webspider.ws_task_rule".
 *
 * @property integer $rule_id
 * @property string $site
 * @property string $type
 * @property integer $delay
 * @property string $encode
 * @property integer $auto_proxy
 * @property integer $db_id
 */
class TaskRule extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%task_rule}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['site'], 'required'],
            [['site'], 'string'],
            [['delay', 'auto_proxy', 'db_id'], 'integer'],
            [['type'], 'string', 'max' => 150],
            [['encode'], 'string', 'max' => 22],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'rule_id' => Yii::t('app', 'rule id'),
            'site' => Yii::t('app', 'site'),
            'type' => Yii::t('app', 'type'),
            'delay' => Yii::t('app', 'delay'),
            'encode' => Yii::t('app', 'encode'),
            'auto_proxy' => Yii::t('app', 'auto proxy'),
            'db_id' => Yii::t('app', 'Db ID'),
        ];
    }

    /**
     * @inheritdoc
     * @return TaskRuleQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new TaskRuleQuery(get_called_class());
    }
}
