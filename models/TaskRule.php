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
    const WEB_TYPE_UNKNOWN = 0;
    const WEB_TYPE_LIST = 1;
    const WEB_TYPE_CONTENT = 2;

    const PROXY_OPENED = 1;
    const PROXY_CLOSED = 0;

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
            'rule_id' => Yii::t('app/ctrl/task_rule', 'rule id'),
            'site' => Yii::t('app/ctrl/task_rule', 'site'),
            'type' => Yii::t('app/ctrl/task_rule', 'type'),
            'delay' => Yii::t('app/ctrl/task_rule', 'delay'),
            'encode' => Yii::t('app/ctrl/task_rule', 'encode'),
            'auto_proxy' => Yii::t('app/ctrl/task_rule', 'auto proxy'),
            'db_id' => Yii::t('app/ctrl/task_rule', 'Db ID'),
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
