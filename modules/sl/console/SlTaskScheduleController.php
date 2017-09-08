<?php
namespace app\modules\sl\console;

use yii\console\Controller;
use app\modules\sl\models\SlTaskScheduleConsole;
use app\modules\sl\models\SlTaskScheduleCrontabConsole;
use app\modules\sl\models\SlTaskItemConsole;
use app\modules\sl\models\SlGlobalSettingsConsole;
use app\modules\sl\models\SlWsDataTaskPageConsole;
use app\modules\sl\models\SlTaskScheduleCrontabAbnormalConsole;
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
				->where(['in', 'sche_status', array(SlTaskScheduleConsole::SCHE_STATUS_OPEN, SlTaskScheduleConsole::SCHE_STATUS_COMPLETE)])
				->asArray()
				->all();

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
				$startTime = $sche['sche_time'];
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

			$keyWordsArr = Json::decode( $cron['key_words'] );

			if( !is_array($cookie) || empty( $cookie ) ) $cookie = [];
			if( !is_array($user_agent) || empty( $user_agent ) ) $user_agent = [];

			if( !is_array($pfNameArr) ) $pfNameArr = [];
			if( !is_array($brandArr) ) $brandArr = [];
			if( !is_array($classArr) ) $classArr = [];

			if( !is_array($catArr) ) $catArr = [];
			if( !is_array($keyWordsArr) ) $keyWordsArr = [];

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
									'',//key_words
									SlTaskItemConsole::TASK_STATUS_OPEN,//默认打开
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
									'',//key_words
									SlTaskItemConsole::TASK_STATUS_OPEN,//默认打开
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
									'',//key_words
									SlTaskItemConsole::TASK_STATUS_OPEN,//默认打开
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
									'',//key_words
									SlTaskItemConsole::TASK_STATUS_OPEN,//默认打开
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

			foreach ($pfNameArr as $pfName)
			{
				foreach ($classArr as $className)
				{
					foreach ($keyWordsArr as $keyWords)
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
									'',
									$className,
									$catName,
									$keyWords,//key_words
									SlTaskItemConsole::TASK_STATUS_OPEN,//默认打开
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
									'',
									$className,
									$catName,
									$keyWords,//key_words
									SlTaskItemConsole::TASK_STATUS_OPEN,//默认打开
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
									'',
									$className,
									$catName,
									$keyWords,//key_words
									SlTaskItemConsole::TASK_STATUS_OPEN,//默认打开
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
									'',
									$className,
									$catName,
									$keyWords,//key_words
									SlTaskItemConsole::TASK_STATUS_OPEN,//默认打开
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
	 * 每分钟更新以下两张表：
	 *	task_schedule_crontab 表的 progress 、task_status、act_time 三个字段(实际任务的进度 任务状态 实际开始时间)
	 *	task_item 表的 task_progress 、complete_status、act_time 三个字段(任务项的进度 任务状态 实际开始时间)
	 * @return
	 */
	public function actionUpdateCrontabState()
	{
		$crontabIdArr = SlTaskScheduleCrontabConsole::find()
			->alias('cron')
			->joinWith('schedule')
			->select('sche.alert_params, cron.id, cron.sche_id, cron.name, cron.act_time, cron.start_time')
			->where('task_status='.SlTaskScheduleCrontabConsole::TASK_STATUS_EXECUTING )
			->asArray()
			->indexBy('id')
			->all();

		//page = 0 and cron_id in(111,167,990)
		/*$cronUnpageArr = SlTaskItemConsole::find()
			->select('id, cron_id, paging, act_time')
			->where(['in', 'cron_id', array_keys( $crontabIdArr )])
			->andWhere(array('paging' => SlTaskItemConsole::PAGING_NO))
			->asArray()
			->indexBy('cron_id')
			->all();*/
		$cronUnpageArr = [];

		$itemArr = SlTaskItemConsole::find()
			->select('id, cron_id, paging, act_time')
			->where(['in', 'cron_id', array_keys( $crontabIdArr )])
			->indexBy('id')
			->asArray()
			->all();	

		$time_stamp = time();
		$cronItemActTimeArr = [];
		$cronActTimeArr = [];

		foreach ($itemArr as $item) 
		{

			//make apart of paging and not paging,then calculate paging
			if($item['paging'] == SlTaskItemConsole::PAGING_NO)
			{
				$cronUnpageArr[$item['cron_id']] = '';

				//update `sl_task_item.act_time`
				$cronItemActTimeArr[$item['id']] = 0;
			}
			else
			{
				//update `act_time` of sl_task_item
				if( !$item['act_time'] )
				{
					$cronItemActTimeArr[$item['id']] = $time_stamp;
				}
				else
				{
					$cronItemActTimeArr[$item['id']] = $item['act_time'];
				}

				//update `act_time` of sl_task_schedule_crontab
				if( !$crontabIdArr[$item['cron_id']]['act_time'] )
				{
					$cronActTimeArr[$item['cron_id']] = $time_stamp;
				}
				else
				{
					$cronActTimeArr[$item['cron_id']] = $crontabIdArr[$item['cron_id']]['act_time'];
				}
			}

		}

		//fill the unassigned `act_time` of sl_task_schedule_crontab with default value 
		foreach ($crontabIdArr as $cron)
		{
			if( !isset($cronActTimeArr[$cron['id']] ) )
			{
				$cronActTimeArr[$cron['id']] = 0;
			}
		}

		$itemIdArr = array_keys( $itemArr );//all `task_item`.`id` of the `task_scheduel_crontab`.
		//make sure all `task_item` are paged
		$itemPageArr = SlWsDataTaskPageConsole::find()
			->select('item_id')
			->where(['in', 'item_id', $itemIdArr])
			->indexBy('item_id')
			->asArray()
			->all();

		$itemPageIdArr = array_keys( $itemPageArr );//paged `task_item`.`id` array.
		$itemUnpageIdArr = array_diff( $itemIdArr, $itemPageIdArr);//if result are not empty, then `task_item` are not paged completely.
		//var_dump($itemPageIdArr, $itemIdArr, $itemUnpageIdArr);exit;

		$cronUnpageIdArr = [];//隐式未分页的crontab的id ,即在page表找不到task_item表对应的分页记录
		if( !empty($itemUnpageIdArr ) )// find the unpaged `task_item` and related `task_schedule_crontab`
		{
			foreach ($itemUnpageIdArr as $itemUnpageId) 
			{
				$cronUnpageIdArr[] = $itemArr[$itemUnpageId]['cron_id'];
			}
		}

		$cronIds = array_keys($crontabIdArr);//所有正在执行的crontab的id
		//$cronUnpageIds = array_merge( array_keys($cronUnpageArr), $cronUnpageIdArr );//未完成分页的crontab的id 显式未分页的 和 隐式未分页的crontab的id
		$cronUnpageIds = array_keys($cronUnpageArr);//未完成分页的crontab的id
		$cronPageIds = array_diff($cronIds, $cronUnpageIds);//已完成分页的crontab的id

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

		$crontabAbnormalTypeArr = [];
		$crontabAbnormalMsgArr = [];

		foreach ($cronPageProgress as $cronId => $cronStateArr)
		{
			$cronProgressArr[$cronId] = round(array_sum($cronStateArr)/count($cronStateArr), 4);//cront的进度 = 已完成的page数/所有page数 ，保留4位小数
			if($cronProgressArr[$cronId] == 1.0000)
			{
				$cronCompleteIds[] = $cronId;
				$cronCompleteTimeArr[$cronId] = $time_stamp;

				//verify datas number is and accomplished time is normal
				$start_date_ret = preg_replace('/-/', '', substr($crontabIdArr[$cronId]['start_time'], 0, 10));
				$crontab_data_table = 'ws_' . $crontabIdArr[$cronId]['sche_id']. '_'.$start_date_ret.'_'.$cronId;

				$crontab_data_ct = Yii::$app->db->createCommand('SELECT COUNT(*) as ct FROM ' . $crontab_data_table)->queryOne();
				$crontab_data_num = $crontab_data_ct ? $crontab_data_ct['ct'] : 0;
				$accomplished_duration = round( ($time_stamp - $cronActTimeArr[$cronId]) / 3600, 1);

				$alert_params = Json::decode($crontabIdArr[$cronId]['alert_params']);
				if(empty($alert_params) || !is_array($alert_params))
					$alert_params = [];

				$abnormal_type = SlTaskScheduleCrontabAbnormalConsole::ABNORMAL_TYPE_NONE;
				$abnormal_msg = [];

				if( isset($alert_params['total_num_min']) && $crontab_data_num < $alert_params['total_num_min'])
				{
					$abnormal_type = $abnormal | SlTaskScheduleCrontabAbnormalConsole::ABNORMAL_TYPE_NUM_LESS;
					$abnormal_msg[] = SlTaskScheduleCrontabAbnormalConsole::getNumMinMsg($crontab_data_num, $alert_params['total_num_min']);
				}

				if( isset($alert_params['total_num_max']) && $crontab_data_num > $alert_params['total_num_max'])
				{
					$abnormal_type = $abnormal | SlTaskScheduleCrontabAbnormalConsole::ABNORMAL_TYPE_NUM_MORE;
					$abnormal_msg[] = SlTaskScheduleCrontabAbnormalConsole::getNumMaxMsg($crontab_data_num, $alert_params['total_num_max']);
				}

				if( isset($alert_params['duration']) && $accomplished_duration > $alert_params['duration'] )
				{
					$abnormal_type = $abnormal | SlTaskScheduleCrontabAbnormalConsole::ABNORMAL_TYPE_DURATION;
					$abnormal_msg[] = SlTaskScheduleCrontabAbnormalConsole::getDurationMsg($accomplished_duration, $alert_params['duration']);
				}

				//update `sl_task_schedule_crontab_abnormal`
				if( $abnormal_type )
				{
					$crontabAbnormalTypeArr[$cronId] = $abnormal_type;
					$crontabAbnormalMsgArr[$cronId] = implode(';', $abnormal_msg);
					$cronStatArr[$cronId] = SlTaskScheduleCrontabConsole::TASK_STATUS_ABNORMAL;
				}
				else
				{
					$cronStatArr[$cronId] = SlTaskScheduleCrontabConsole::TASK_STATUS_COMPLETED;
				}
			}
			else
			{
				$cronStatArr[$cronId] = SlTaskScheduleCrontabConsole::TASK_STATUS_EXECUTING;
				$cronCompleteTimeArr[$cronId] = 0;
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
			$taskCrontabValues .= '('.$cId.', '.$crontabProgressArr[$cId].', '.$cState. ', '. $crontabCompleteTimeArr[$cId] . ', '. $cronActTimeArr[$cId] . '),';
		}

		$scheCrontabSql = 'INSERT INTO ' . SlTaskScheduleCrontabConsole::tableName()
				.' (id, task_progress, task_status, complete_time, act_time) values ';
		$scheCrontabSql1 = ' ON DUPLICATE KEY UPDATE task_progress = values(task_progress), task_status = values(task_status), complete_time = values(complete_time), act_time = values(act_time);';

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
		//update task_item proress & complete_status
		/*if(!empty($cronCompleteIds))
		{
			$exeUpdate = Yii::$app->db->createCommand('UPDATE '.SlTaskItemConsole::tableName().' SET [[task_progress]] = 1.0000, [[complete_status]] = '.SlTaskItemConsole::TASK_STATUS_COMPLETE.', [[complete_time]] = '. time() .' WHERE [[cron_id]] IN ('. implode(',', $cronCompleteIds) .');' )->execute();
			if(!$exeUpdate)
			{
				return 11;
			}
		}*/

		$pageArr = SlWsDataTaskPageConsole::find()
			->select('item_id, task_id, state')
			->where(['in', 'task_id', $cronIds])
			->asArray()
			->all();

		$pageIdArr = array();
		foreach ($pageArr as $pv)
		{
			if(!isset($pageIdArr[$pv['item_id']]))
			{
				$pageIdArr[$pv['item_id']] = array();
			}

			if($pv['state'] == SlWsDataTaskPageConsole::PAGE_STATE_COMPLETE)
			{
				$pageIdArr[$pv['item_id']][] = 1;
			}
			else
			{
				$pageIdArr[$pv['item_id']][] = 0;
			}
		}

		$itemProgressArr = array();
		$itemStateArr = array();
		$itemCompleteTimeArr = array();

		foreach ($itemIdArr as $itemId)
		{
			//cann't find any paged record in `ws_data_task_page` related to `task_item`.id
			if(!isset($pageIdArr[$itemId]))
			{
				//records which have finished paged `task_item` but cann't find record in `ws_data_task_page` which should be set 1.0000
				if($itemArr[$itemId]['paging'] == SlTaskItemConsole::PAGING_YES)
				{
					$itemProgressArr[$itemId] = 1.0000;
					$itemStateArr[$itemId] = SlTaskItemConsole::TASK_STATUS_COMPLETE;
					$itemCompleteTimeArr[$itemId] = $time_stamp;
				}
				//unpaged `task_item`
				else
				{
					$itemProgressArr[$itemId] = 0.0000;
				}
				continue;
			}
			$itemProgressArr[$itemId] = round(array_sum( $pageIdArr[$itemId] ) / count( $pageIdArr[$itemId] ), 4);

			if($itemProgressArr[$itemId] == 1.0000)
			{
				$itemStateArr[$itemId] = SlTaskItemConsole::TASK_STATUS_COMPLETE;
				$itemCompleteTimeArr[$itemId] = $time_stamp;
			}
			else
			{
				$itemStateArr[$itemId] = SlTaskItemConsole::TASK_STATUS_OPEN;
				$itemCompleteTimeArr[$itemId] = 0;
			}
		}

		$updateItemValues = '';
		foreach ($itemStateArr as $itemId => $itemState)
		{
			$updateItemValues .= '('.$itemId.', '.$itemProgressArr[$itemId].', '.$itemState. ', '. $itemCompleteTimeArr[$itemId] . ', ' . $cronItemActTimeArr[$itemId] . '),';
		}

		$updateItemSql = 'INSERT INTO ' . SlTaskItemConsole::tableName()
				.' (id, task_progress, complete_status, complete_time, act_time) values ';
		$updateItemSql1 = ' ON DUPLICATE KEY UPDATE task_progress = values(task_progress), complete_status = values(complete_status), complete_time = values(complete_time), act_time = values(act_time);';

		//update task_schedule_crontab proress & complete_status

		if(!empty($updateItemValues))
		{
			$exeUpdate = Yii::$app->db->createCommand($updateItemSql . substr($updateItemValues, 0, -1) . $updateItemSql1)->execute();
			if(!$exeUpdate)
			{
				return 10;
			}
		}
		/***更新task_item END***/

		/*** 更新sl_task_schedule_crontab_abnormal START ***/
		$updateAbnormalValues = '';
		foreach ($crontabAbnormalTypeArr as $cronId => $abnormaType) 
		{
			$updateAbnormalValues .= '(' . $cronId . ', ' . $crontabIdArr[$cronId]['sche_id'] . ', ' . $abnormaType . ', \'' . $crontabAbnormalMsgArr[$cronId] . '\'),';
		}

		$updateAbnormalSql = 'INSERT INTO ' . SlTaskScheduleCrontabAbnormalConsole::tableName()
							. ' (cron_id, sche_id, abnormal_type, msg) values '	;
		$updateAbnormalSql1 = ' ON DUPLICATE KEY UPDATE cron_id = values(cron_id), sche_id = values(sche_id), abnormal_type = values(abnormal_type), msg = values(msg);';

		if(!empty($updateAbnormalValues))
		{
			$exeUpdate = Yii::$app->db->createCommand($updateAbnormalSql . substr($updateAbnormalValues, 0, -1) . $updateAbnormalSql1)->execute();
			if(!$exeUpdate)
			{
				return 10;
			}
		}
		/*** 更新sl_task_schedule_crontab_abnormal END ***/
		return 0;
	}

	/**
	 * 检查未完成实际任务是否超过预警时间24小时
	 * 每日执行一次
	 */
	public function actionCheckDeadCrontab()
	{
		$crontabArr = SlTaskScheduleCrontabConsole::find()
			->alias('cron')
			->joinWith('schedule')
			->select('sche.alert_params, cron.id, cron.sche_id, cron.act_time')
			->where('task_status='.SlTaskScheduleCrontabConsole::TASK_STATUS_EXECUTING )
			->asArray()
			->indexBy('id')
			->all();

		$time_stamp = time();

		$crontabAbnormalTypeArr = [];
		$crontabAbnormalMsgArr = [];
		$cronAbnormalIds = [];

		foreach ($crontabArr as $cronId => $cron) 
		{
			if( $cron['act_time'] )
			{
				$alert_params = Json::decode( $cron['alert_params'] );
				if(empty($alert_params) || !is_array($alert_params))
					$alert_params = [];

				$act_duration = round( ($time_stamp - $cron['act_time']) / 3600, 1);

				if( isset( $alert_params['duration'] ) && $act_duration - $alert_params['duration'] > 24 )
				{
					$crontabAbnormalTypeArr[$cronId] = SlTaskScheduleCrontabAbnormalConsole::ABNORMAL_TYPE_DURATION;
					$crontabAbnormalMsgArr[$cronId] = SlTaskScheduleCrontabAbnormalConsole::getDurationMsg("超过预警时间24小时未完成");
					$cronAbnormalIds[] = $cronId;
				}

			}
		}

		/***更新cron START***/
		//update task_schedule_crontab task_status
		if(!empty($cronAbnormalIds))
		{
			$updateCronSql = 'UPDATE ' . SlTaskScheduleCrontabConsole::tableName() . ' SET task_status='
							. SlTaskScheduleCrontabConsole::TASK_STATUS_ABNORMAL . ' WHERE id IN (' . implode(',', $cronAbnormalIds) . ');';
			$exeUpdate = Yii::$app->db->createCommand($updateCronSql)->execute();
			if(!$exeUpdate)
			{
				return 10;
			}
		}
		/***更新cron END***/

		/*** 更新sl_task_schedule_crontab_abnormal START ***/
		$updateAbnormalValues = '';
		foreach ($crontabAbnormalTypeArr as $cronId => $abnormaType) 
		{
			$updateAbnormalValues .= '(' . $cronId . ', ' . $crontabArr[$cronId]['sche_id'] . ', ' . $abnormaType . ', \'' . $crontabAbnormalMsgArr[$cronId] . '\'),';
		}

		$updateAbnormalSql = 'INSERT INTO ' . SlTaskScheduleCrontabAbnormalConsole::tableName()
							. ' (cron_id, sche_id, abnormal_type, msg) values '	;
		$updateAbnormalSql1 = ' ON DUPLICATE KEY UPDATE cron_id = values(cron_id), sche_id = values(sche_id), abnormal_type = values(abnormal_type), msg = values(msg);';

		if(!empty($updateAbnormalValues))
		{
			$exeUpdate = Yii::$app->db->createCommand($updateAbnormalSql . substr($updateAbnormalValues, 0, -1) . $updateAbnormalSql1)->execute();
			if(!$exeUpdate)
			{
				return 10;
			}
		}
		/*** 更新sl_task_schedule_crontab_abnormal END ***/	
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
				.' (id, task_progress, complete_status) values ';
		$taskPageSql1 = ' ON DUPLICATE KEY UPDATE task_progress = values(task_progress), complete_status = values(complete_status);';

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
