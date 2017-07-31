<?php

namespace app\modules\sl\models;

use Yii;

/**
 * This is the model class for table "sl_ws_data_task_page".
 *
 * @property integer $id
 * @property integer $task_id
 * @property integer $schedule_id
 * @property string $task_name
 * @property string $page_url
 * @property string $skuids
 * @property string $spider_name
 * @property string $spider_ip
 * @property string $cookie
 * @property string $brand1
 * @property string $cate1
 * @property string $cate2
 * @property string $cate3
 * @property integer $state
 * @property string $table_name
 * @property string $add_time
 * @property string $finsh_time
 */
class SlWsDataTaskPage extends \yii\db\ActiveRecord
{
    const PAGE_STATE_UNSETTLED = 0;
    const PAGE_STATE_IN_PROGRESS = 1;
    const PAGE_STATE_COMPLETE = 2;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sl_ws_data_task_page';
    }

    public static function getDb()
    {
        return Yii::$app->getModule('sl')->db;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['task_id'], 'required'],
            [['task_id', 'schedule_id', 'item_id', 'state'], 'integer'],
            [['skuids', 'cookie'], 'string'],
            [['add_time', 'finsh_time'], 'safe'],
            [['task_name', 'cate1', 'cate2', 'cate3', 'table_name'], 'string', 'max' => 100],
            [['page_url'], 'string', 'max' => 1024],
            [['spider_name', 'brand1'], 'string', 'max' => 20],
            [['spider_ip'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'task_id' => '每日任务id',
            'schedule_id' => '计划任务id',
            'item_id' => '任务项id',
            'task_name' => '任务名称',
            'page_url' => '单页任务内容url链接地址',
            'skuids' => '指定分页全部skuid',
            'spider_name' => 'spider模块名称',
            'spider_ip' => 'Spider Ip',
            'cookie' => 'spider所需cookie',
            'brand1' => '品牌名',
            'cate1' => '一级分类',
            'cate2' => '二级分类',
            'cate3' => '三级分类',
            'state' => '任务状态（0:未处理;1:正在处理;2:已处理完）',
            'table_name' => 'Table Name',
            'add_time' => 'Add Time',
            'finsh_time' => 'Finsh Time',
        ];
    }
}
