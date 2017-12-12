<?php

namespace app\models\nlp;

use Yii;

/**
 * This is the model class for table "nlp_engine_task_item".
 *
 * @property integer $id
 * @property integer $update_time
 * @property string $cmd
 * @property string $module
 * @property string $param_list
 * @property integer $status
 */
class NlpEngineTaskItem extends \yii\db\ActiveRecord
{

    const STATUS_READY = 0;//待执行
    const STATUS_EXECUTING = 1;//执行中
    const STATUS_COMPLETE = 2;//执行完毕

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'nlp_engine_task_item';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['update_time'], 'integer'],
            ['status', 'in', 'range' => [self::STATUS_READY, self::STATUS_EXECUTING, self::STATUS_COMPLETE]],
            [['cmd', 'module'], 'string', 'max' => 30],
            [['param_list'], 'string', 'max' => 100],
            [['param_list'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'update_time' => '更新时间',
            'cmd' => '执行的后台模块的命令',
            'module' => '执行的后台模块',
            'param_list' => '执行的后台命令对应的参数',
            'status' => '该命令的执行状态',
        ];
    }

    public static function getDb()
    {
        return Yii::$app->getModule('nlp')->db;
    }

    public function getSearchQuery()
    {
        $query = static::find();

        $this->load( Yii::$app->request->post(), '' );
        if (!$this->validate())
        {
            return false;
        }

        $post = Yii::$app->request->post();
        if( isset( $post['update_time_s'] ) && !empty( $post['update_time_s'] ) )
        {
            $query->andFilterWhere(['>=', 'update_time', strtotime($post['update_time_s'])]);
        }
        if( isset( $post['update_time_e'] ) && !empty( $post['update_time_e'] ) )
        {
            $query->andFilterWhere(['<=', 'update_time', strtotime($post['update_time_e'])]);
        }

        $query->andFilterWhere(['like', 'param_list', $this->param_list])
            ->andFilterWhere(['status' => $this->status])
            ->andFilterWhere(['cmd' => $this->cmd]);

        return $query;
    }
}
