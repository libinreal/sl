<?php

namespace app\models\nlp;

use Yii;

/**
 * This is the model class for table "nlp_engine_log".
 *
 * @property integer $id
 * @property string $module
 * @property integer $status
 * @property string $cmd
 * @property string $params
 * @property string $msg
 * @property integer $add_time
 */
class NlpEngineLog extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'nlp_engine_log';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['module'], 'required'],
            [['status', 'add_time'], 'integer'],
            [['module'], 'string', 'max' => 30],
            [['cmd'], 'string', 'max' => 60],
            [['params', 'msg'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'module' => '执行的模块',
            'status' => '任务执行结果(0成功,其它值失败)',
            'cmd' => '模块下的命令',
            'params' => '命令参数',
            'msg' => '命令执行的返回信息',
            'add_time' => '添加任务的时间',
        ];
    }

    public static function getDb()
    {
        return Yii::$app->getModule('nlp')->db;
    }
}
