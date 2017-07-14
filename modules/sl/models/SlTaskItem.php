<?php

namespace app\modules\sl\models;

use Yii;

/**
 * This is the model class for table "sl_task_item".
 *
 * @property integer $id
 * @property integer $sche_id
 * @property string $name
 * @property string $pf_name
 * @property string $brand_name
 * @property string $class_name
 * @property string $dt_category
 * @property string $key_words
 * @property integer $task_status
 * @property double $task_progress
 * @property integer $task_time
 * @property integer $update_time
 * @property integer $complete_time
 * @property double $data_number
 * @property string $cookie
 * @property string $user_agent
 * @property string $spider_name
 */
class SlTaskItem extends \yii\db\ActiveRecord
{

    const PAGING_NO = 0;
    const PAGING_YES = 1;

    const TASK_STATUS_CLOSE = 0;
    const TASK_STATUS_OPEN = 1;
    const TASK_STATUS_COMPLETE = 2;


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sl_task_item';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['sche_id', 'task_status', 'task_time', 'update_time', 'create_time', 'complete_time', 'paging'], 'integer'],
            ['task_status', 'in', 'range' => [self::TASK_STATUS_CLOSE, self::TASK_STATUS_OPEN, self::TASK_STATUS_COMPLETE]],
            ['paging', 'in', 'range' => [self::PAGING_NO, self::PAGING_YES]],
            [['name', 'cookie', 'user_agent', 'dt_category', 'pf_name'], 'string'],
            [['task_progress', 'data_number'], 'number'],
            [['brand_name', 'class_name', 'key_words'], 'string', 'max' => 200],
            [['spider_name'], 'string', 'max' => 255],
        ];
    }

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios['update'] = ['name', 'brand_name', 'class_name', 'cookie', 'user_agent'];
        return $scenarios;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '子任务id',
            'sche_id' => '主任务id',
            'name' => '任务名',
            'pf_name' => '渠道名称(京东，天猫)',
            'brand_name' => '品牌名',
            'class_name' => '分类名',
            'dt_category' => '数据类型(商品,评论等)',
            'key_words' => '关键字',
            'task_status' => '子任务状态(0:未启动1:已启动2:已完成)',
            'task_progress' => '任务进度,最小值0.0000,最大值1.0000',
            'task_time' => '开始时间',
            'create_time' => '创建时间',
            'update_time' => '最后更新时间',
            'complete_time' => '任务完成时间',
            'data_number' => '已经抓取数据总数',
            'cookie' => '渠道的cookie设置',
            'user_agent' => '渠道的User-Agent设置',
            'spider_name' => '抓取标识',
            'paging' => '分页状态（0: 未分页; 1:分页完成）',

        ];
    }

    public static function getDb()
    {
        return Yii::$app->getModule('sl')->db;
    }

    public function getSearchQuery()
    {
        $query = static::find();

        $this->load( Yii::$app->request->queryParams, '' );
        if (!$this->validate())
        {
            // var_dump( $this->getErrors());exit;
            return false;
        }

        if( isset( $post['task_time_s'] ) && !empty( $post['task_time_s'] ) )
        {
            $query->andFilterWhere(['>=', 'task_time', strtotime($post['task_time_s'])]);
        }
        else if( isset( $post['task_time_e'] ) && !empty( $post['task_time_e'] ) )
        {
            $query->andFilterWhere(['<=', 'task_time', strtotime($post['task_time_e'])]);
        }

        $query->andFilterWhere(['sche_id' => $this->sche_id])
                ->andFilterWhere(['like', 'brand_name', $this->brand_name])
                ->andFilterWhere(['like', 'key_words', $this->key_words])
                ->andFilterWhere(['like', 'task_status', $this->task_status])
                ->andFilterWhere(['like', 'dt_category', $this->dt_category])
                ->andFilterWhere(['like', 'pf_name', $this->pf_name])
                ->andFilterWhere(['like', 'class_name', $this->class_name])

                ->andFilterWhere(['like', 'name', $this->name]);


        /*$commandQuery = clone $query;
    echo $commandQuery->createCommand()->getRawSql();*/

        return $query;
    }
}
