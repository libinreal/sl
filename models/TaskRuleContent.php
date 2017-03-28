<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "webspider.ws_task_rule_content".
 *
 * @property integer $id
 * @property integer $rule_id
 * @property string $xpath
 * @property string $fetch_re
 * @property string $fetch_pos
 * @property string $removes
 * @property string $db_name
 * @property string $db_field
 */
class TaskRuleContent extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%task_rule_content}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'required'],
            [['id', 'rule_id'], 'integer'],
            [['xpath', 'fetch_re', 'fetch_pos', 'removes'], 'string'],
            [['db_name', 'db_field'], 'string', 'max' => 114],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Id',
            'rule_id' => Yii::t('app', 'rule id'),
            'xpath' => Yii::t('app', 'xpath'),
            'fetch_re' => Yii::t('app', 'fetch re'),
            'fetch_pos' => Yii::t('app', 'fetch pos'),
            'removes' => Yii::t('app', 'removes'),
            'db_name' => Yii::t('app', 'db name'),
            'db_field' => Yii::t('app', 'db field'),
        ];
    }

    /**
     * @inheritdoc
     * @return TaskRuleContentQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new TaskRuleContentQuery(get_called_class());
    }
}
