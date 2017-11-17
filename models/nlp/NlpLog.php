<?php

namespace app\models\nlp;

use Yii;

/**
 * This is the model class for table "sl_global_settings".
 *
 * @property integer $id
 * @property integer $parent_id
 */
class NlpLog extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'nlp_log';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['taged'], 'integer'],
            [['ws_data_table'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'ws_data_table' => '抓取的数据存放表名',
            'taged' => '是否已经打标签'
        ];
    }

    public static function getDb()
    {
        return Yii::$app->getModule('nlp')->db;
    }
}
