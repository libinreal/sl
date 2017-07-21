<?php

namespace app\modules\sl\controllers;

use yii\data\ActiveDataProvider;
use Yii;
use app\modules\sl\models\SlTaskScheduleCrontab;

use yii\web\Response;
use yii\helpers\Json;

/**
 * Api controller for the `sl` module
 * Get schedule and task data
 */
class ApiController extends \yii\web\Controller
{
	public $enableCsrfValidation = false;
	/**
	 * 接口作用：获取某天任务的状态和存放数据表名
	 * 接口地址：http://sl.3tichina.com/sl/api/task-state
	 *
	 * HTTP请求方法 : POST
	 *
	 * 传入参数 ：
	 * <date> 必填项，任务日期("2017-07-18")
	 * <name> 选填项，任务名称("dmp化妆品爬取数据")
	 *
	 * 返回数据 : (Json)
	 * 		"data"下面的各个字段说明:
	 * 		<state>	任务状态用0-4共5个数字表示(0 未创建, 1 未启动, 2 执行中, 3 已完成, 4 已取消)
	 * 		<table> 任务抓取的数据存放的表名
	 * 		<name>	任务名称
	 * 		<start_time>	任务开始时间
	 * 		<task_progress> 任务进度(0~1之间的带4位有效数字的浮点数)
	 * 		<task_status>	任务运行状态(0 未启动 1 正在进行 2 已完成)
	 * 		<control_status> 任务控制状态(0 停止 1 运行)
	 *  示例：
	 * 	{
	 * 		"data" : {
	 * 					"state":1
	 * 					"table":"ws_1_1_20170718",
	 * 					"name":"dmp抓取任务",
	 * 					"start_time":"2017-07-18",
	 * 					"task_progress":"0.3300",
	 * 					"task_status":1,
	 * 					"control_status":1
	 * 				  },
	 * 	     "code"	: "0",
	 * 	     "msg"	: "Success"
	 * 	}
	 *
	 * @return [type] [description]
	 */
	public function actionTaskState()
	{
		if(Yii::$app->request->isPost)
		{
			Yii::$app->response->format = Response::FORMAT_JSON;

			$request = Yii::$app->request;
			$start_date = $request->post('date', '');
			$name = $request->post('name', '');

			$create_time_start = strtotime($start_date);
			$create_time_end = $create_time_start + 3600 * 24;

			$q = SlTaskScheduleCrontab::find();

			$q->select('id, name, sche_id,start_time, task_progress, task_status, control_status')
				->where('create_time >= :create_time_start and create_time <= :create_time_end', [':create_time_start' => $create_time_start, ':create_time_end' => $create_time_end])
				->andWhere('name = :name', [':name' => $name]);

			$crontabData = $q->asArray()->one();

			if( $crontabData )
			{
				$crontabData['table'] = 'ws_' . $crontabData['sche_id']. '_'.date('Ymd', strtotime($crontabData['create_time'])).'_'.$crontabData['id'];

				return [
					'data' 	=> $crontabData,
					'msg'  	=> 'Success',
					'code'	=>	'0'
				];
			}
			else
			{
				return [
					'data' 	=> [
									'table' => '',
									'name' => '',
									'start_time' => '',
									'task_progress' => '',
									'task_status' => SlTaskScheduleCrontab::TASK_STATUS_UNSTARTED,
									'control_status' => SlTaskScheduleCrontab::CONTROL_STOPPED,
								],
					'msg'  	=> 'Success',
					'code'	=>	'0'
				];
			}

		}
	}
}