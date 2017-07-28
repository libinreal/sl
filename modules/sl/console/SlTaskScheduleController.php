<?php
namespace app\modules\sl\console;

use yii\console\Controller;
use app\modules\sl\models\SlTaskScheduleConsole;
use app\modules\sl\models\SlTaskScheduleCrontabConsole;
use app\modules\sl\models\SlTaskItemConsole;
use app\modules\sl\models\SlGlobalSettingsConsole;
use app\modules\sl\models\SlWsDataTaskPageConsole;
use yii\helpers\Json;


use Yii;
/**
 * SL系统任务生成以及监控
 *
 * @author Qiang Xue <libin@3ti.us>
 * @since 2.0
 */
class SlTaskScheduleController extends Controller
{
	/**
	 * 每分钟扫描`sl_task_schedule` `sl_task_schedule_crontab` `sl_task_item`三个表，
	 * 从`sl_task_schedule`表生成每日任务和子任务，分别插入 `sl_task_schedule_crontab` 和 `sl_task_item`
	 */
	public function actionAddTask()
	{
		$scheQuery = SlTaskScheduleConsole::find();

		$scheArr = $scheQuery
				->asArray()->all();

        $crontabByScheArr = SlTaskScheduleCrontabConsole::find()
        					->select('sche_id')
        					->indexBy('sche_id')
        					->groupBy('sche_id')
        					->asArray()
        					->all();

        $scheCrontabArr = SlTaskScheduleCrontabConsole::find()
        					->select('sche_id')
        					->where(['>=', 'create_time', strtotime('today')])
        					->indexBy('sche_id')
        					->asArray()->all();

		/***生成每日任务 START***/
		$cronFields = [
			'name',
			'start_time',
			'task_progress',
			'sche_id',
			'task_status',
			'control_status',
			'create_time'
		];

		$cronInsertList = [];
		foreach ($scheArr as $sche)
		{
			$schType = $sche['sche_type'];
			$startTime = date('Y-m-d').' '.$sche['sche_time'];

			if( $schType == SlTaskScheduleConsole::SCHE_TYPE_ONCE && !isset($crontabByScheArr[$sche['id']]))//Only once 未生成过任务
			{

				$cronInsertList[] = [
					$sche['name'],
					$startTime,
					0,
					$sche['id'],
					SlTaskScheduleCrontabConsole::TASK_STATUS_EXECUTING,
					SlTaskScheduleCrontabConsole::CONTROL_STARTED,
					time()
				];
			}
			else if( $schType == SlTaskScheduleConsole::SCHE_TYPE_DAY )//everyday repeat
			{
				if( isset( $scheCrontabArr[$sche['id']] ) )
					continue;//Task schedule has been exploded

				$cronInsertList[] = [
					$sche['name'],
					$startTime,
					0,
					$sche['id'],
					SlTaskScheduleCrontabConsole::TASK_STATUS_EXECUTING,
					SlTaskScheduleCrontabConsole::CONTROL_STARTED,
					time()
				];
			}
			else if( $schType == SlTaskScheduleConsole::SCHE_TYPE_MONTH )//every month repeat
			{
				if( isset( $scheCrontabArr[$sche['id']] ) )
					continue;//Task schedule has been exploded

				$dayNo = date('j');//Day in this month
				$scheDayNoArr = explode(',', $sche['month_days']);
				if( !in_array( $dayNo, $scheDayNoArr ) )
					continue;//Not today to explode.

				$cronInsertList[] = [
					$sche['name'],
					$startTime,
					0,
					$sche['id'],
					SlTaskScheduleCrontabConsole::TASK_STATUS_EXECUTING,
					SlTaskScheduleCrontabConsole::CONTROL_STARTED,
					time()
				];
			}
			else if( $schType == SlTaskScheduleConsole::SCHE_TYPE_WEEK )//every week repeat
			{
				if( isset( $scheCrontabArr[$sche['id']] ) )
					continue;//Task schedule has been exploded

				$dayNo = date('N');//Day in this week
				$scheDayNoArr = explode(',', $sche['week_days']);
				if( !in_array( $dayNo, $scheDayNoArr ) )
					continue;//Not today to explode.

				$cronInsertList[] = [
					$sche['name'],
					$startTime,
					0,
					$sche['id'],
					SlTaskScheduleCrontabConsole::TASK_STATUS_EXECUTING,
					SlTaskScheduleCrontabConsole::CONTROL_STARTED,
					time()
				];
			}
		}

		if( !empty( $cronInsertList ) )
		{
			Yii::$app->db->createCommand()->batchInsert( SlTaskScheduleCrontabConsole::tableName(), $cronFields, $cronInsertList)->execute();//Explode to crontab
   		}
   		/***生成每日任务 END***/

   		/***生成每日子任务 START***/
   		$pfKeyArr = array_flip( Yii::$app->params['PLATFORM_LIST'] );

        $pfSettings = SlGlobalSettingsConsole::find()
        	->alias('ps')
        	->joinWith('children')
        	->where(['in', 'ps.code', array_values( $pfKeyArr ) ])
        	->orderBy('ps.sort_order')
        	->asArray()
        	->all();

		$scheCrontabArr = SlTaskScheduleCrontabConsole::find()
							->alias('cron')
							->joinWith('schedule')
							->select('sche.sche_type, sche.pf_name, sche.brand_name, sche.class_name, sche.dt_category, sche.cookie, sche.user_agent, sche.key_words, sche.week_days, sche.month_days, sche.sche_time, cron.id, cron.sche_id, cron.name')
							->where(['>=', 'cron.create_time', strtotime('today')])
							->asArray()->all();

        $taskItemArr = (new \yii\db\Query())
        				->select(['COUNT(*)', 'cron_id'])
        				->from(SlTaskItemConsole::tableName())
        				->indexBy('cron_id')
        				->groupBy('cron_id')
        				->all();//Only once tasks

       	$hasExplodedTaskItemArr = SlTaskItemConsole::find()
       								->select('cron_id')
	       							->where(['>=', 'create_time', strtotime('today')])
	       							->indexBy('cron_id')
	       							->groupBy('cron_id')
	       							->asArray()
	       							->all();//Exploded task_items from `sl_task_schedule` today

        $pfSpiderArr = [];
        foreach ($pfSettings as $pfSetting)
        {
        	$pf = $pfSetting['code'];
        	foreach ($pfSetting['children'] as $ci => $setting)
        	{
        		if( strpos($setting['code'], '_spider') !== false )
        		{
        			$pfSpiderArr[$pf] = $setting['value'];
        		}

        	}
        }


		$itemFields = [
			'sche_id',
			'cron_id',
			'name',
			'pf_name',
            'brand_name',
            'class_name',
            'dt_category',
            'key_words',
            'complete_status',
            'task_time',
            'task_date',
            'create_time',
            'update_time',
            'cookie',
            'user_agent',
            'spider_name',
		];

		foreach ($scheCrontabArr as $cron)
		{
			$schType = $cron['sche_type'];

			$pfNameArr = Json::decode( $cron['pf_name'] );
			$brandArr = Json::decode( $cron['brand_name'] );
			$classArr = Json::decode( $cron['class_name'] );

			$catArr = Json::decode( $cron['dt_category'] );
			$cookie = Json::decode( $cron['cookie'] );
			$user_agent = Json::decode( $cron['user_agent'] );

			if( !is_array($cookie) || empty( $cookie ) ) $cookie = [];
			if( !is_array($user_agent) || empty( $user_agent ) ) $user_agent = [];

			if( !is_array($pfNameArr) ) $pfNameArr = [];
			if( !is_array($brandArr) ) $brandArr = [];
			if( !is_array($classArr) ) $classArr = [];

			if( !is_array($catArr) ) $catArr = [];

			$insertList = [];

			foreach ($pfNameArr as $pfName)
			{
				foreach ($classArr as $className)
				{
					foreach ($brandArr as $brandName)
					{
						foreach ($catArr as $catName)
						{
							$pfKey = $pfKeyArr[$pfName];

							if( $schType == SlTaskScheduleConsole::SCHE_TYPE_ONCE && !isset($taskItemArr[$cron['id']]))//Only once 未生成过任务
							{
								$insertList[] = [
									$cron['sche_id'],
									$cron['id'],
									$cron['name'],
									$pfName,
									$brandName,
									$className,
									$catName,
									$cron['key_words'],
									SlTaskItemConsole::TASK_STATUS_CLOSE,//默认关闭
									strtotime( $cron['sche_time'] ),
									$cron['sche_time'],
									time(),
									time(),
									isset( $cookie[ $pfKey.'_cookie' ] ) ? $cookie[ $pfKey.'_cookie' ] : '',
									isset( $user_agent[ $pfKey.'_ua' ] ) ? Json::encode( $user_agent[ $pfKey.'_ua' ] ) : '',
									$pfSpiderArr[$pfKey]
								];
							}
							else if( $schType == SlTaskScheduleConsole::SCHE_TYPE_DAY )//everyday repeat
							{
								if( isset( $hasExplodedTaskItemArr[$cron['id']] ) )
									break 4;//Task schedule has been exploded

								$taskTime = strtotime( date('Y-m-d').' '.$cron['sche_time'] );

								$insertList[] = [
									$cron['sche_id'],
									$cron['id'],
									$cron['name'],
									$pfName,
									$brandName,
									$className,
									$catName,
									$cron['key_words'],
									SlTaskItemConsole::TASK_STATUS_CLOSE,//默认关闭
									$taskTime,
									date('Y-m-d').' '.$cron['sche_time'],
									time(),
									time(),
									isset( $cookie[ $pfKey.'_cookie' ] ) ? $cookie[ $pfKey.'_cookie' ] : '',
									isset( $user_agent[ $pfKey.'_ua' ] ) ? Json::encode( $user_agent[ $pfKey.'_ua' ] ) : '',
									$pfSpiderArr[$pfKey]
								];
							}
							else if( $schType == SlTaskScheduleConsole::SCHE_TYPE_MONTH )//every month repeat
							{
								if( isset( $hasExplodedTaskItemArr[$cron['id']] ) )
									break 4;//Task schedule has been exploded

								$dayNo = date('j');//Day in this month
								$scheDayNoArr = explode(',', $cron['month_days']);
								if( !in_array( $dayNo, $scheDayNoArr ) )
									break 4;//Not today to explode.

								$taskTime = strtotime( date('Y-m-d').' '.$cron['sche_time'] );

								$insertList[] = [
									$cron['sche_id'],
									$cron['id'],
									$cron['name'],
									$pfName,
									$brandName,
									$className,
									$catName,
									$cron['key_words'],
									SlTaskItemConsole::TASK_STATUS_CLOSE,//默认关闭
									$taskTime,
									date('Y-m-d').' '.$cron['sche_time'],
									time(),
									time(),
									isset( $cookie[ $pfKey.'_cookie' ] ) ? $cookie[ $pfKey.'_cookie' ] : '',
									isset( $user_agent[ $pfKey.'_ua' ] ) ? Json::encode( $user_agent[ $pfKey.'_ua' ] ) : '',
									$pfSpiderArr[$pfKey]
								];
							}
							else if( $schType == SlTaskScheduleConsole::SCHE_TYPE_WEEK )//every week repeat
							{
								if( isset( $hasExplodedTaskItemArr[$cron['id']] ) )
									break 4;//Task schedule has been exploded

								$dayNo = date('N');//Day in this week
								$scheDayNoArr = explode(',', $cron['week_days']);
								if( !in_array( $dayNo, $scheDayNoArr ) )
									break 4;//Not today to explode.

								$taskTime = strtotime( date('Y-m-d').' '.$cron['sche_time'] );

								$insertList[] = [
									$cron['sche_id'],
									$cron['id'],
									$cron['name'],
									$pfName,
									$brandName,
									$className,
									$catName,
									$cron['key_words'],
									SlTaskItemConsole::TASK_STATUS_CLOSE,//默认关闭
									$taskTime,
									date('Y-m-d').' '.$cron['sche_time'],
									time(),
									time(),
									isset( $cookie[ $pfKey.'_cookie' ] ) ? $cookie[ $pfKey.'_cookie' ] : '',
									isset( $user_agent[ $pfKey.'_ua' ] ) ? Json::encode( $user_agent[ $pfKey.'_ua' ] ) : '',
									$pfSpiderArr[$pfKey]
								];
							}
						}
					}
				}
			}

			if( !empty( $insertList ) )
			{
				Yii::$app->db->createCommand()->batchInsert( SlTaskItemConsole::tableName(), $itemFields, $insertList)->execute();//Explode batch task items
				Yii::$app->db->createCommand('UPDATE {{'.SlTaskScheduleConsole::tableName().'}} SET [[task_number]]= [[task_number]]+'.count($insertList).' WHERE [[id]]='.$cron['sche_id'])
	   				->execute();//Update `task_schedule` `task_number`
	   		}
		}
		/***生成每日子任务 END***/
		return 0;
	}

	/**
	 * 每分钟更新 task_schedule_crontab 表的 progress 、task_status两个字段(每日任务的进度和任务状态)
	 * @return
	 */
	public function actionUpdateCrontabState()
	{
		$crontabIdArr = SlTaskScheduleCrontabConsole::find()
			->select('id')
			->where('task_status='.SlTaskScheduleCrontabConsole::TASK_STATUS_EXECUTING)
			->asArray()
			->indexBy('id')
			->all();

		//page = 0 and cron_id in(111,167,990)
		$cronUnpageArr = SlTaskItemConsole::find()
			->select('id, cron_id, paging')
			->where(['in', 'cron_id', array_keys( $crontabIdArr )])
			->andWhere(array('paging' => SlTaskItemConsole::PAGING_NO))
			->asArray()
			->indexBy('cron_id')
			->all();

		$cronIds = array_keys($crontabIdArr);//所有正在执行的crontab的id
		$cronUnpageIds = array_keys($cronUnpageArr);//未完成分页的crontab的id
		$cronPageIds = array_diff($cronIds, $cronUnpageIds);//已完成分页的crontab的id
		// var_dump($cronUnpageIds, $cronPageIds);

		$taskPageArr = SlWsDataTaskPageConsole::find()
			->select('id, task_id, state')
			->where(['in', 'task_id', $cronPageIds])//此处 task_id 对应 task_schedule_crontab表的id 而不是 task_item的id
			->asArray()
			->all();

		//开始计算已分页的crontab的进度和状态
		$cronPageProgress = [];
		foreach ($taskPageArr as $pVal)
		{
			if(!isset($cronPageProgress[$pVal['task_id']]))
			{
				$cronPageProgress[$pVal['task_id']] = [];
			}

			if($pVal['state'] == SlWsDataTaskPageConsole::PAGE_STATE_COMPLETE)
			{
				$cronPageProgress[$pVal['task_id']][] = 1;
			}
			else
			{
				$cronPageProgress[$pVal['task_id']][] = 0;
			}
		}

		/***已完成分页的cron的进度和状态计算START***/
		$cronProgressArr = [];
		$cronStatArr = [];
		$cronCompleteIds = [];//已完成的每日任务id

		$cronCompleteTimeArr = [];//完成时间
		foreach ($cronPageProgress as $cronId => $cronStateArr)
		{
			$cronProgressArr[$cronId] = round(array_sum($cronStateArr)/count($cronStateArr), 4);//cront的进度 = 已完成的page数/所有page数 ，保留4位小数
			if($cronProgressArr[$cronId] == 1.0000)
			{
				$cronCompleteIds[] = $cronId;
				$cronStatArr[$cronId] = SlTaskScheduleCrontabConsole::TASK_STATUS_COMPLETED;
				$cronCompleteTimeArr[$cronId] = time();
			}
			else
			{
				$cronStatArr[$cronId] = SlTaskScheduleCrontabConsole::TASK_STATUS_EXECUTING;
			}
		}

		//在已分页的cron记录里，把进度为0的cron统一赋值为0.0100（1%）
		foreach ($cronProgressArr as $cpKey => $cpVal)
		{
			if($cpVal==0.0000)
			{
				$cronProgressArr[$cpKey] == 0.0100;
			}
		}
		/***已完成分页的cron的进度和状态计算END***/

		/***未完成分页的cron的进度和状态计算START***/
		//未完成分页的cron统一赋值为0.0050（0.5%）
		$cronUnpageStatVals = array_fill(0, count($cronUnpageIds), SlTaskScheduleCrontabConsole::TASK_STATUS_EXECUTING);
		$cronUnpageProgressVals = array_fill(0, count($cronUnpageIds), 0.0050);
		$cronUnpageCompleteTimeVals = array_fill(0, count($cronUnpageIds), 0);

		$cronUnpageStatArr = array_combine($cronUnpageIds, $cronUnpageStatVals);
		$cronUnpageProgressArr = array_combine($cronUnpageIds, $cronUnpageProgressVals);
		$cronUnpageCompleteTimeArr = array_combine($cronUnpageIds, $cronUnpageCompleteTimeVals);
		/***未完成分页的cron的进度和状态计算END***/

		/***合并已完成和未完成的cron START***/
		$crontabStatusArr = $cronStatArr + $cronUnpageStatArr;
		$crontabProgressArr = $cronProgressArr + $cronUnpageProgressArr;
		$crontabCompleteTimeArr = $cronCompleteTimeArr + $cronUnpageCompleteTimeArr;
		/***合并已完成和未完成的cron END***/

		/***更新cron START***/
		$taskCrontabValues = '';
		foreach ($crontabStatusArr as $cId => $cState)
		{
			$taskCrontabValues .= '('.$cId.', '.$crontabProgressArr[$cId].', '.$cState. ', '. $crontabCompleteTimeArr[$cId] . '),';
		}

		$scheCrontabSql = 'INSERT INTO ' . SlTaskScheduleCrontabConsole::tableName()
				.' (id, task_progress, task_status, complete_time) values ';
		$scheCrontabSql1 = ' ON DUPLICATE KEY UPDATE task_progress = values(task_progress), task_status = values(task_status), complete_time = values(complete_time);';

		//update task_schedule_crontab proress & task_status
		if(!empty($taskCrontabValues))
		{
			$exeUpdate = Yii::$app->db->createCommand($scheCrontabSql . substr($taskCrontabValues, 0, -1) . $scheCrontabSql1)->execute();
			if(!$exeUpdate)
			{
				return 10;
			}
		}
		/***更新cron END***/

		/***更新task_item START***/
		//update task_item proress & task_status
		if(!empty($cronCompleteIds))
		{
			$exeUpdate = Yii::$app->db->createCommand('UPDATE '.SlTaskItemConsole::tableName().' SET [[task_progress]] = 1.0000, [[complete_status]] = '.SlTaskItemConsole::TASK_STATUS_COMPLETE.', [[complete_time]] = '. time() .' WHERE [[cron_id]] IN ('. implode(',', $cronCompleteIds) .');' )->execute();
			if(!$exeUpdate)
			{
				return 11;
			}
		}
		/***更新task_item END***/

		return 0;
	}

	/**
	 * 弃用 ：data_task_page 缺少和 task_item 的关联id，无法通过 前者计算 task_item的task_progress
	 *
	 * 每分钟执行每日任务以及子任务的进度、状态检查
	 */
	public function actionTrackProgress()
	{
		$crontabIdArr = SlTaskScheduleCrontabConsole::find()
			->select('id')
			->where('task_status='.SlTaskScheduleCrontabConsole::TASK_STATUS_EXECUTING)
			->asArray()
			->indexBy('id')
			->all();

		$taskItemArr = SlTaskItemConsole::find()
			->select('id, cron_id, paging')
			->where(['in', 'cron_id', array_keys( $crontabIdArr )])
			->asArray()
			->indexBy('id')
			->all();

		$taskPageArr = SlWsDataTaskPageConsole::find()
			->select('id, task_id, state')
			->where(['in', 'task_id', array_keys($taskItemArr)])
			->asArray()
			->all();

		$taskItemPageStatArr = [];

		foreach ($taskPageArr as $pv)
		{
			if(!isset($taskItemPageStatArr[$pv['task_id']]))
			{
				$taskItemPageStatArr[$pv['task_id']] = [];
			}

			if($pv['state'] == SlWsDataTaskPageConsole::PAGE_STATE_COMPLETE)
			{
				$taskItemPageStatArr[$pv['task_id']][] = 1;
			}
			else
			{
				$taskItemPageStatArr[$pv['task_id']][] = 0;
			}
		}

		// var_dump($taskPageArr, $taskItemPageStatArr);

		$taskItemProgressArr = [];
		$taskItemValues = '';

		foreach ($taskItemArr as $iv)
		{
			if(isset($taskItemPageStatArr[$iv['id']]))//check table `task_item_page`
			{
				$taskItemProgressArr[$iv['id']] = round(array_sum( $taskItemPageStatArr[$iv['id']] ) / count( $taskItemPageStatArr[$iv['id']] ), 4);

				if($iv['paging'] == SlTaskItemConsole::PAGING_NO)
				{
					$taskItemProgressArr[$iv['id']] *= 0.5;
				}
			}
			else
			{
				$taskItemProgressArr[$iv['id']] = 0;
			}

			if($taskItemProgressArr[$iv['id']] == 1.0000)
				$taskItemValues .= '('.$iv['id'].', '.$taskItemProgressArr[$iv['id']] . ', ' . SlTaskItemConsole::TASK_STATUS_COMPLETE . '),';
			else
				$taskItemValues .= '('.$iv['id'].', '.$taskItemProgressArr[$iv['id']] . ', ' . SlTaskItemConsole::TASK_STATUS_OPEN . '),';
		}


		$taskPageSql = 'INSERT INTO ' . SlTaskItemConsole::tableName()
				.' (id, task_progress, task_status) values ';
		$taskPageSql1 = ' ON DUPLICATE KEY UPDATE task_progress = values(task_progress), task_status = values(task_status);';

		//update task_item proress & state
		$exeUpdate = Yii::$app->db->createCommand($taskPageSql . substr($taskItemValues, 0, -1) . $taskPageSql1)->execute();
		if(!$exeUpdate)
		{
			return 10;
		}

		$taskItemStatArr = [];

		foreach ($taskItemArr as $iv)
		{
			if(!isset($taskItemStatArr[$iv['cron_id']]))
			{
				$taskItemStatArr[$iv['cron_id']] = [];
			}

			if($taskItemProgressArr[$iv['id']] == 1)
			{
				$taskItemStatArr[$iv['cron_id']][] = 1;
			}
			else
			{
				$taskItemStatArr[$iv['cron_id']][] = 0;
			}
		}

		$taskCrontabProgressArr = [];
		$taskCrontabValues = '';

		foreach ($crontabIdArr as $cv)
		{
			if( isset($taskItemStatArr[$cv['id']] ))
			{
				$taskCrontabProgressArr[$cv['id']] = round(array_sum($taskItemStatArr[$cv['id']]) / count($taskItemStatArr[$cv['id']]), 4);
			}
			else
			{
				$taskCrontabProgressArr[$cv['id']] = 0;
			}

			if($taskCrontabProgressArr[$cv['id']] == 1.0000)
			{
				$taskCrontabValues .= '('.$cv['id'] . ', 1.0000, ' . SlTaskScheduleCrontabConsole::TASK_STATUS_COMPLETED . '),';
			}
			else
			{
				$taskCrontabValues .= '('.$cv['id'] . ', '.$taskCrontabProgressArr[$cv['id']].', ' . SlTaskScheduleCrontabConsole::TASK_STATUS_EXECUTING . '),';
			}
		}

		$scheCrontabSql = 'INSERT INTO ' . SlTaskScheduleCrontabConsole::tableName()
				.' (id, task_progress, task_status) values ';
		$scheCrontabSql1 = ' ON DUPLICATE KEY UPDATE task_progress = values(task_progress), task_status = values(task_status);';

		//update task_item proress & state
		$exeUpdate = Yii::$app->db->createCommand($scheCrontabSql . substr($taskCrontabValues, 0, -1) . $scheCrontabSql1)->execute();
		if(!$scheCrontabSql1)
		{
			return 10;
		}

		return 0;
	}
}
