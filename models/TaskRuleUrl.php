<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "webspider.ws_task_rule_url".
 *
 * @property integer $rule_id
 * @property string $url_entry
 * @property string $url_param
 * @property string $url_page_pattern
 * @property string $url_content_pattern
 * @property string $cookie
 * @property string $user_agent
 * @property integer $auto_iteration
 * @property integer $limit_top_num
 * @property integer $limit_page_num
 */
class TaskRuleUrl extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'webspider.ws_task_rule_url';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['rule_id', 'auto_iteration', 'limit_top_num', 'limit_page_num'], 'integer'],
            [['url_entry', 'url_page_pattern', 'url_content_pattern', 'cookie', 'user_agent'], 'string', 'max' => 1024],
            [['url_param'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'rule_id' => Yii::t('app', '规则id'),
            'url_entry' => Yii::t('app', '入口url'),
            'url_param' => Yii::t('app', '关键字参数'),
            'url_page_pattern' => Yii::t('app', '分页url表达式'),
            'url_content_pattern' => Yii::t('app', '内容url表达式'),
            'cookie' => Yii::t('app', '网页cookie'),
            'user_agent' => Yii::t('app', '网页UA'),
            'auto_iteration' => Yii::t('app', '自动迭代下页（当列表页，1：是，0：否）'),
            'limit_top_num' => Yii::t('app', '最新top数量'),
            'limit_page_num' => Yii::t('app', '分页限制数'),
        ];
    }

    /**
     * @inheritdoc
     * @return TaskRuleUrlQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new TaskRuleUrlQuery(get_called_class());
    }
}
