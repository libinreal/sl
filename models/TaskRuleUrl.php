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
        return '{{%task_rule_url}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['rule_id', 'auto_iteration', 'limit_top_num', 'limit_page_num'], 'integer'],
            [['url_entry', 'url_param', 'url_page_pattern', 'url_content_pattern', 'cookie', 'user_agent'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'rule_id' => Yii::t('app', 'rule id'),
            'url_entry' => Yii::t('app', 'url entry'),
            'url_param' => Yii::t('app', 'url param'),
            'url_page_pattern' => Yii::t('app', 'url page pattern'),
            'url_content_pattern' => Yii::t('app', 'url content pattern'),
            'cookie' => Yii::t('app', 'cookie'),
            'user_agent' => Yii::t('app', 'user agent'),
            'auto_iteration' => Yii::t('app', 'auto iteration'),
            'limit_top_num' => Yii::t('app', 'limit top num'),
            'limit_page_num' => Yii::t('app', 'limit page num'),
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
