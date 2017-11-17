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
				$segments = $this->_segBysort($segments, $c['product_title']);
				$segments = $this->_segByAdd($segments, $c['product_title']);

				$insertSql .= '(' . $c['id'] . ', \'' . implode(',', $segments) . '\'),';
				
			}
			
			$insertRet = Yii::$app->db->createCommand(substr($insertSql,0, -1))->execute();
			
			if($insertRet === false)
				return 1;
		}
		/***生成每日子任务 END***/
		return 0;
	}

	/**
	  * 对分词的按原句排序
	  * @param $seg 分词结果
	  * @param $stat 原句
	  * @return array 排序后分词
	  *
	  */
	private function _segBysort($seg, $stat)
	{
		$ksArr = [];
		foreach ($seg as $i) 
		{
			$p = strpos($stat, $i);
			$ksArr[$p]  = $i;
		}
		ksort($ksArr, SORT_NUMERIC);
		// var_dump($ksArr);
		return $ksArr;
	}

	/**
	  *对分词进行补充
	  * @param $seg 分词结果
	  * @param $stat 原句
	  * @return array 补充后的分词结果
	  *
	  */
	private function _segByAdd($seg, $stat)
	{
		$iMap = array_fill(0, strlen($stat),'');#整个位置图
		$cMap = [];#使用的位置图
		foreach ($seg as $i => $s) 
		{
			$cm = array_fill( $i, strlen($s), '');
			$cMap  = $cMap + $cm;
		}
		$oMap = array_diff_key($iMap, $cMap);

		$oMaps = array_keys( $oMap );#遗漏词的位置数组，值为位置

		$c = count($oMaps);
		for($i = 0;$i < $c; $i++)
		{
			$start = $oMaps[$i];
			if(isset($end) && $start <= $end)
				continue;#start 为未补充的起始位置,end为已补充的结束位置, 每次循环起始位置一定要大于上次的结束位置
			$end = false;
			for($j = $i + 1;$j < $c; $j++)
			{
				if($j == $i + 1 && $oMaps[$j] - $oMaps[$i] > 1 )#不连续的位置
				{
					$end = $oMaps[$j - 1];
					// echo "break $end ".$oMaps[$j] . "  ". $oMaps[$i] ."<br>";
					break;
				}
				if( $oMaps[$j] - $oMaps[$j-1] > 1 )#不连续的位置
				{
					$end = $oMaps[$j - 1];
					// echo "break $end ".$oMaps[$j] . "  ". $oMaps[$i] ."<br>";
					break;
				}
			}
			if($end === false)
				$end = $oMaps[$j - 1];# $j - 1 == $c-1
			$seg[$start] = substr($stat, $start, $end - $start + 1);
			// echo "<br>add内部<br>";
			// var_dump($oMaps, $start, $end - $start + 1, substr($stat, $start, $end - $start + 1));
			
		}
		ksort($seg, SORT_NUMERIC);
		return $seg;
	}
}
