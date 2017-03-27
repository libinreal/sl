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
        return 'webspider.ws_task_rule_content';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'db_name', 'db_field'], 'required'],
            [['id', 'rule_id'], 'integer'],
            [['xpath', 'fetch_re'], 'string', 'max' => 200],
            [['fetch_pos', 'removes'], 'string', 'max' => 255],
            [['db_name', 'db_field'], 'string', 'max' => 20],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', '内容规则id'),
            'rule_id' => Yii::t('app', '规则id'),
            'xpath' => Yii::t('app', '元素提取表达式'),
            'fetch_re' => Yii::t('app', '提取正则表达式'),
            'fetch_pos' => Yii::t('app', '提取位置（$1:第一个 $2：第二个）'),
            'removes' => Yii::t('app', '替换无效字符或字符串'),
            'db_name' => Yii::t('app', '数据库表名'),
            'db_field' => Yii::t('app', '数据库表字段'),
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
