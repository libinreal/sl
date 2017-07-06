<?php

namespace app\modules\sl\models;

use Yii;

/**
 * This is the model class for table "sl_task_schedule".
 *
 * @property integer $id
 * @property string $name
 * @property string $pf_name
 * @property string $brand_name
 * @property string $class_name
 * @property string $key_words
 * @property integer $sche_status
 * @property double $sche_progress
 * @property integer $sche_type
 * @property string $sche_time
 * @property string $dt_category
 * @property integer $update_time
 * @property double $data_number
 * @property integer $task_number
 * @property string $cookie
 * @property string $user_agent
 */
class SlTaskSchedule extends \yii\db\ActiveRecord
{

    const SCHE_STATUS_CLOSE = 0;
    const SCHE_STATUS_OPEN = 1;
    const SCHE_STATUS_COMPLETE = 2;

    const SCHE_TYPE_NONE = 0;
    const SCHE_TYPE_ONCE = 1;
    const SCHE_TYPE_DAY = 2;
    const SCHE_TYPE_MONTH = 3;
    const SCHE_TYPE_WEEK = 4;


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sl_task_schedule';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'brand_name', 'class_name', 'cookie', 'user_agent'], 'required', 'on' => 'update'],
            // [['name', 'brand_name', 'class_name', 'cookie', 'user_agent'], 'required', 'on' = 'update'],
            [['name', 'brand_name', 'class_name', 'cookie', 'user_agent'], 'string'],
            [['sche_status', 'sche_type', 'update_time', 'task_number'], 'integer'],
            ['sche_status', 'in', 'range' => [self::SCHE_STATUS_CLOSE, self::SCHE_STATUS_OPEN, self::SCHE_STATUS_COMPLETE]],
            ['sche_type', 'in', 'range' => [self::SCHE_TYPE_NONE, self::SCHE_TYPE_ONCE, self::SCHE_TYPE_DAY, self::SCHE_TYPE_MONTH, self::SCHE_TYPE_WEEK]],
            [['sche_progress', 'data_number'], 'number'],
            [['pf_name', 'dt_category'], 'string', 'max' => 100],
            [['key_words'], 'string', 'max' => 200],
            [['sche_time'], 'string', 'max' => 8],
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
            'id' => '主任务id',
            'name' => '主任务名',
            'pf_name' => '渠道id(0:tmall 1:jd )',
            'brand_name' => '品牌名',
            'class_name' => '分类名',
            'key_words' => '关键字',
            'sche_status' => '任务状态(0:未启动1:已启动2:已完成)',
            'sche_progress' => '任务进度,最小值0.0000,最大值1.0000',
            'sche_type' => '计划执行分类(0不执行1一次2按日3按月4按周)',
            'sche_time' => '计划每次执行的时间(00:00:00)',
            'dt_category' => '数据类型(商品,评论等)',
            'update_time' => '最后更新时间',
            'data_number' => '已经抓取数据总数',
            'task_number' => '已生成的子任务数量',
            'cookie' => '渠道的cookie设置',
            'user_agent' => '渠道的User-Agent设置',
        ];
    }

    public static function getDb()
    {
        return Yii::$app->getModule('sl')->db;
    }

    public function getSearchQuery()
    {
        $query = static::find();

        $this->load( Yii::$app->request->queryParams );
        if (!$this->validate())
        {
            // var_dump( $this->getErrors());exit;
            return false;
        }

        $post = Yii::$app->request->post();
        if( isset( $post['update_time_s'] ) && !empty( $post['update_time_s'] ) )
        {
            $query->andFilterWhere(['>=', 'update_time', strtotime($post['update_time_s'])]);
        }
        else if( isset( $post['update_time_e'] ) && !empty( $post['update_time_e'] ) )
        {
            $query->andFilterWhere(['<=', 'update_time', strtotime($post['update_time_e'])]);
        }

        $query->andFilterWhere([
            'brand_name' => $this->brand_name,
            'key_words' => $this->key_words,
            'sche_status' => $this->sche_status,
            'dt_category' => $this->dt_category,
            'pf_name' => $this->pf_name,
            'class_name' => $this->class_name,
        ]);

        $query->andFilterWhere(['like', 'key_words', $this->key_words])
            ->andFilterWhere(['like', 'name', $this->name]);

        return $query;
    }
}
