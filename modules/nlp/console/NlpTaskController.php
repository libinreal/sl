<?php
namespace app\modules\nlp\console;

use yii\console\Controller;
use yii\helpers\Json;
use yii\db\Query;
use app\models\sl\SlTaskScheduleCrontabConsole;

use Yii;
/**
 * NLP系统任务执行
 *
 * @author libin <libin@3ti.us>
 * @since 2.0
 */
class NlpTaskController extends Controller
{
	/**
	 * 每分钟扫描如`ws_36_20171020_261`格式的数据表，
	 * 生成与之关联的 词性标记表 通过id关联
	 */
	public function actionTag()
	{
		// NlpLogConsole::find();
		// (new Query())->from('nlp_log')->where('ws_data')
		$start_date = '2017-10-22';
		$name = 'DMP_WINE_BRAND';

		//paramaters lost
		if(!$name || !$start_date)
			return 1;

		$create_time_start = strtotime($start_date);
		$create_time_end = $create_time_start + 3600 * 24;

		$q = SlTaskScheduleCrontabConsole::find();

		$q->select('id, name, sche_id,start_time, task_progress, task_status, control_status')
			->where('create_time >= :create_time_start and create_time <= :create_time_end', [':create_time_start' => $create_time_start, ':create_time_end' => $create_time_end])
			->andWhere('name = :name', [':name' => $name]);

		$crontabData = $q->asArray()->one();

		if( $crontabData )
		{

			$start_date_ret = preg_replace('/-/', '', substr($crontabData['start_time'], 0, 10));
			$crontabData['table'] = 'ws_' . $crontabData['sche_id']. '_'.$start_date_ret.'_'.$crontabData['id'];

			$tableCheck = Yii::$app->db->createCommand("SHOW TABLES LIKE '". $crontabData['table'] . "'" )->queryOne();//检查数据存放表是否存在

			//data source not exists , uncompleted
			if(!$tableCheck || $crontabData['task_status'] != SlTaskScheduleCrontabConsole::TASK_STATUS_COMPLETED)
				return 3;

			//drop tag table if exists
			$tableTag = 'nlp_seg_' . $crontabData['sche_id']. '_'.$start_date_ret.'_'.$crontabData['id'];
			Yii::$app->db->createCommand("DROP TABLE IF EXISTS `". $tableTag . "`;" )->execute();

			$tableTagCreate = Yii::$app->db->createCommand(
				"CREATE TABLE `". $tableTag ."` (" . 
				  "`id` int(10) unsigned NOT NULL DEFAULT '0'," .
				  "`tag_ret` text NOT NULL COMMENT '词性标注'," .
				  "PRIMARY KEY (`id`)" .
				") ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='".$crontabData['table']."词性分析结果';"
			)->execute();//创建标记数据表

			//create table failed
			if($tableTagCreate === false)
				return 4;
			
			//source records
			$wsQuery = (new Query())->from( $crontabData['table'] )->select('id, product_title');

			/*$st = microtime(true);
			$i = 0;*/
			$insertSql = "INSERT INTO ${tableTag} (`id`, `tag_ret`) VALUES ";
			foreach ($wsQuery->each() as $c) 
			{
				// echo "第 ${i} 条 数据：</br>";
				// $i++;
				$segments = jieba($c['product_title']);
				$insertSql .= '(' . $c['id'] . ', \'' . implode(',', $segments) . '\'),';
				// print_r($segments);
			}
			
			$insertRet = Yii::$app->db->createCommand(substr($insertSql,0, -1))->execute();
			
			if($insertRet === false)
				return 1;
		}
		/***生成每日子任务 END***/
		return 0;
	}
}
