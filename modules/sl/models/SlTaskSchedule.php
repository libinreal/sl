<?php

namespace app\modules\sl\models;

use Yii;
use yii\helpers\Json;
use app\modules\sl\components\SettingHelper;
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
            [['name', 'brand_name', 'class_name', 'sche_type', 'pf_name', 'sche_time', 'dt_category' ], 'required', 'on' => 'update'],
            [['name', 'brand_name', 'class_name', 'cookie', 'user_agent', 'week_days', 'month_days'], 'string'],
            [['sche_status', 'sche_type', 'update_time', 'task_number'], 'integer'],
            ['sche_status', 'in', 'range' => [self::SCHE_STATUS_CLOSE, self::SCHE_STATUS_OPEN, self::SCHE_STATUS_COMPLETE]],
            ['sche_type', 'in', 'range' => [self::SCHE_TYPE_NONE, self::SCHE_TYPE_ONCE, self::SCHE_TYPE_DAY, self::SCHE_TYPE_MONTH, self::SCHE_TYPE_WEEK]],
            [['sche_progress', 'data_number'], 'number'],
            [['pf_name', 'dt_category', 'month_days'], 'string', 'max' => 100],
            [['key_words'], 'string', 'max' => 200],
            [['sche_time'], 'string', 'max' => 20],
        ];
    }

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios['update'] = ['name', 'brand_name', 'class_name', 'sche_type', 'pf_name', 'sche_time', 'dt_category'];

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
            'week_days' => '按周执行的配置',
            'month_days' => '按月执行的配置',
            'sche_time' => '计划每次执行的时间(0000-00-00 00:00:00)',
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

    public function beforeValidate()
    {
        $post = Yii::$app->request->post();

        if( is_array( $this->class_name ) )
            $this->class_name = Json::encode($this->class_name);

        if( is_array( $this->brand_name ) )
            $this->brand_name = Json::encode($this->brand_name);

        if( is_array( $this->pf_name ) )
            $this->pf_name = Json::encode($this->pf_name);

        if( is_array( $this->dt_category ) )
            $this->dt_category = Json::encode($this->dt_category);

        return parent::beforeValidate();
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {

            //cookie && user_agent save
            $post = Yii::$app->request->post();
            $cookie = [];
            $user_agent = [];

            foreach (Yii::$app->getModule('sl')->params['PLATFORM_LIST'] as $pf => $pf_name)
            {
                $childrenSettings = SettingHelper::getPfSetting( $pf, '');

                foreach ($childrenSettings[$pf] as $childrenKey => $childrenVal)
                {
                   if( isset($post[$childrenKey]) )
                   {
                        if( strpos($childrenKey, '_cookie') !== false )
                            $cookie[$childrenKey] = $post[$childrenKey];
                        else if( strpos($childrenKey, '_ua') !== false )
                            $user_agent[$childrenKey] = $post[$childrenKey];
                   }
                }
            }

            $this->setAttributes([
                'cookie' => Json::encode( $cookie ),
                'user_agent' => Json::encode( $user_agent ),
                'update_time' => time()
            ]);
            return true;
        } else {
            return false;
        }
    }

    public function afterFind()
    {
        $class_name = Json::decode( $this->getAttribute('class_name') );
        $brand_name = Json::decode( $this->getAttribute('brand_name') );
        $pf_name = Json::decode( $this->getAttribute('pf_name') );

        $dt_category = Json::decode( $this->getAttribute('dt_category') );
        $cookie = Json::decode( $this->getAttribute('cookie') );
        $user_agent = Json::decode( $this->getAttribute('user_agent') );

        $this->setAttributes([
            'class_name' => $class_name,
            'brand_name' => $brand_name,
            'pf_name' => $pf_name,

            'dt_category' => $dt_category,
            'cookie' => $cookie,
            'user_agent' => $user_agent,
        ]);
    }
}
