<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "webspider.ws_task_rule".
 *
 * @property integer $rule_id
 * @property integer $id
 * @property string $site
 * @property string $type
 * @property integer $delay
 * @property string $encode
 * @property integer $auto_proxy
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
            [['id', 'delay', 'auto_proxy'], 'integer'],
            [['site', 'type', 'encode'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'rule_id' => Yii::t('app', '规则id'),
            'id' => Yii::t('app', '代理设置id'),
            'site' => Yii::t('app', '站点名'),
            'type' => Yii::t('app', '网页类型（列表页/内容页）'),
            'delay' => Yii::t('app', '采集间隔'),
            'encode' => Yii::t('app', '网页编码'),
            'auto_proxy' => Yii::t('app', '是否启动ip代理（0：不启动，1：启动）'),
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
