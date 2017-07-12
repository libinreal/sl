<?php
namespace app\modules\sl\console;

use yii\console\Controller;
use app\modules\sl\models\SlTaskItem;
use app\modules\sl\models\SlTaskScheduleConsole;
use app\modules\sl\models\SlTaskItemConsole;
use app\modules\sl\models\SlGlobalSettingsConsole;
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
	public function actionAddTaskItem()
	{
		$scheQuery = SlTaskScheduleConsole::find();

		$scheArr = $scheQuery
					// ->where([
					// 	'has_explode' => SlTaskScheduleConsole::EXPLODE_NO
					// ])
				->asArray()->all();

		/*$commandQuery = clone $scheQuery;
        echo $commandQuery->createCommand()->getRawSql();exit;*/

		// var_dump($scheArr);

        $pfKeyArr = array_flip( Yii::$app->params['PLATFORM_LIST'] );

        $pfSettings = SlGlobalSettingsConsole::find()
        	->joinWith('children')
        	->where(['in', SlGlobalSettingsConsole::tableName().'.code', array_values( $pfKeyArr ) ])
        	->orderBy(SlGlobalSettingsConsole::tableName().'.sort_order')
        	->asArray()
        	->all();

        $taskItemArr = (new \yii\db\Query())
        				->select(['COUNT(*)', 'sche_id'])
        				->from(SlTaskItemConsole::tableName())
        				->indexBy('sche_id')
        				->groupBy('sche_id')
        				->all();//Only once tasks

       	$qTaskItem1 = SlTaskItemConsole::find();
       	$hasExplodedTaskItemArr = $qTaskItem1
       							->select('sche_id')
       							->where(['>=', 'create_time', strtotime('today')])
       							->indexBy('sche_id')
       							->asArray()
       							->all();//Exploded task_items from `sl_task_schedule` today
		/*$commandQuery = clone $qTaskItem1;
        print_r($hasExplodedTaskItemArr);exit;*/

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
			'name',
			'pf_name',
            'brand_name',
            'class_name',
            'dt_category',
            'key_words',
            'task_status',
            'task_time',
            'create_time',
            'update_time',
            'cookie',
            'user_agent',
            'spider_name',
		];
		foreach ($scheArr as $sche)
		{
			$schType = $sche['sche_type'];

			$pfNameArr = Json::decode( $sche['pf_name'] );
			$brandArr = Json::decode( $sche['brand_name'] );
			$classArr = Json::decode( $sche['class_name'] );

			$catArr = Json::decode( $sche['dt_category'] );
			$cookie = Json::decode( $sche['cookie'] );
			$user_agent = Json::decode( $sche['user_agent'] );

			if( !is_array($cookie) || empty( $cookie ) ) $cookie = [];
			if( !is_array($user_agent) || empty( $user_agent ) ) $user_agent = [];

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

							if( $schType == 1 && !isset($taskItemArr[$sche['id']]))//Only once 未生成过任务
							{
								$insertList[] = [
									$sche['id'],
									$sche['name'],
									$pfName,
									$brandName,
									$className,
									$catName,
									$sche['key_words'],
									SlTaskItemConsole::TASK_STATUS_CLOSE,//默认关闭
									strtotime( $sche['sche_time'] ),
									time(),
									time(),
									isset( $cookie[ $pfKey.'_cookie' ] ) ? $cookie[ $pfKey.'_cookie' ] : '',
									isset( $user_agent[ $pfKey.'_ua' ] ) ? $user_agent[ $pfKey.'_ua' ] : '',
									$pfSpiderArr[$pfKey]
								];
							}
							else if( $schType == 2 )//everyday repeat
							{
								if( isset( $hasExplodedTaskItemArr[$sche['id']] ) )
									break 4;//Task schedule has been exploded

								$taskTime = strtotime( date('Y-m-d').' '.$sche['sche_time'] );

								$insertList[] = [
									$sche['id'],
									$sche['name'],
									$pfName,
									$brandName,
									$className,
									$catName,
									$sche['key_words'],
									SlTaskItemConsole::TASK_STATUS_CLOSE,//默认关闭
									$taskTime,
									time(),
									time(),
									isset( $cookie[ $pfKey.'_cookie' ] ) ? $cookie[ $pfKey.'_cookie' ] : '',
									isset( $user_agent[ $pfKey.'_ua' ] ) ? $user_agent[ $pfKey.'_ua' ] : '',
									$pfSpiderArr[$pfKey]
								];
							}
							else if( $schType == 3 )//every month repeat
							{
								if( isset( $hasExplodedTaskItemArr[$sche['id']] ) )
									break 4;//Task schedule has been exploded

								$dayNo = date('j');//Day in this month
								$scheDayNoArr = explode(',', $sche['month_days']);
								if( !in_array( $dayNo, $scheDayNoArr ) )
									break 4;//Not today to explode.

								$taskTime = strtotime( date('Y-m-d').' '.$sche['sche_time'] );

								$insertList[] = [
									$sche['id'],
									$sche['name'],
									$pfName,
									$brandName,
									$className,
									$catName,
									$sche['key_words'],
									SlTaskItemConsole::TASK_STATUS_CLOSE,//默认关闭
									$taskTime,
									time(),
									time(),
									isset( $cookie[ $pfKey.'_cookie' ] ) ? $cookie[ $pfKey.'_cookie' ] : '',
									isset( $user_agent[ $pfKey.'_ua' ] ) ? $user_agent[ $pfKey.'_ua' ] : '',
									$pfSpiderArr[$pfKey]
								];
							}
							else if( $schType == 4 )//every week repeat
							{
								if( isset( $hasExplodedTaskItemArr[$sche['id']] ) )
									break 4;//Task schedule has been exploded

								$dayNo = date('N');//Day in this week
								$scheDayNoArr = explode(',', $sche['week_days']);
								if( !in_array( $dayNo, $scheDayNoArr ) )
									break 4;//Not today to explode.

								$taskTime = strtotime( date('Y-m-d').' '.$sche['sche_time'] );

								$insertList[] = [
									$sche['id'],
									$sche['name'],
									$pfName,
									$brandName,
									$className,
									$catName,
									$sche['key_words'],
									SlTaskItemConsole::TASK_STATUS_CLOSE,//默认关闭
									$taskTime,
									time(),
									time(),
									isset( $cookie[ $pfKey.'_cookie' ] ) ? $cookie[ $pfKey.'_cookie' ] : '',
									isset( $user_agent[ $pfKey.'_ua' ] ) ? $user_agent[ $pfKey.'_ua' ] : '',
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
				Yii::$app->db->createCommand('UPDATE {{'.SlTaskScheduleConsole::tableName().'}} SET [[task_number]]= [[task_number]]+'.count($insertList).' WHERE [[id]]='.$sche['id'])
	   				->execute();//Update `task_schedule` `task_number`
	   		}
		}//$scheArr foreach end

		return 0;
	}
}
