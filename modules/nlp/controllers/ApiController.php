<?php

namespace app\modules\nlp\controllers;

use app\models\sl\SlTaskScheduleCrontab;

use Yii;
use yii\db\Query;
use yii\web\Response;
use yii\helpers\Json;
/**
 * Api controller for the `nlp` module
 */
class ApiController extends \yii\web\Controller
{
	public $enableCsrfValidation = false;
    /**
     * 词性标注
     * @return string
     */
    public function actionTag()
    {
    	if(Yii::$app->request->isPost)
		{
			Yii::$app->response->format = Response::FORMAT_JSON;
    		$request = Yii::$app->request;
			$start_date = $request->post('date', '');
			$name = $request->post('name', '');

			$emptyRet = [
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

			if(!$name || !$start_date)
				return $emptyRet;

			$create_time_start = strtotime($start_date);
			$create_time_end = $create_time_start + 3600 * 24;

			$q = SlTaskScheduleCrontab::find();

			$q->select('id, name, sche_id,start_time, task_progress, task_status, control_status')
				->where('create_time >= :create_time_start and create_time <= :create_time_end', [':create_time_start' => $create_time_start, ':create_time_end' => $create_time_end])
				->andWhere('name = :name', [':name' => $name]);

			$crontabData = $q->asArray()->one();

			if( $crontabData )
			{

				$start_date_ret = preg_replace('/-/', '', substr($crontabData['start_time'], 0, 10));
				$crontabData['table'] = 'ws_' . $crontabData['sche_id']. '_'.$start_date_ret.'_'.$crontabData['id'];

				$tableCheck = Yii::$app->getModule('sl')->db->createCommand("SHOW TABLES LIKE '". $crontabData['table'] . "'" )->queryOne();//检查数据存放表是否存在

				if(!$tableCheck || $crontabData['task_status'] != SlTaskScheduleCrontab::TASK_STATUS_COMPLETED)
					return $emptyRet;

				//保存标记数据
				$tableTag = 'lt_' . $crontabData['sche_id']. '_'.$start_date_ret.'_'.$crontabData['id'];
				$tableCheck = Yii::$app->getModule('sl')->db->createCommand("DROP TABLE IF EXISTS `". $tableTag . "`;" )->execute();
				$tableTag = Yii::$app->getModule('sl')->db->createCommand(
					"CREATE TABLE `". $tableTag ."` (" . 
					  "`id` int(10) unsigned NOT NULL DEFAULT '0'," .
					  "`tag_ret` text NOT NULL COMMENT '词频'," .
					  "PRIMARY KEY (`id`)" .
					") ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='".$crontabData['table']."词性分析结果';"
				)->execute();//创建标记数据表

				$wsQuery = (new Query())->from( $crontabData['table'] );

				/*$st = microtime(true);
				$i = 0;*/
				foreach ($wsQuery->each() as $c) 
				{
					// echo "第 ${i} 条 数据：</br>";
					// $i++;
					$segments = jieba($c['product_title']);
					// print_r($segments);

				}
				/*echo "共  ${i}  条数据 </br>";

				echo "</br>";
				$et = microtime(true);
				$lt = $et-$st;
				echo '运行时间   '.  $lt . '  s';*/
					
				return [
					'data' 	=> $crontabData,
					'msg'  	=> 'Success',
					'code'	=>	'0'
				];
			}
			else
			{
				return $emptyRet;
			}
		}
    }

}
