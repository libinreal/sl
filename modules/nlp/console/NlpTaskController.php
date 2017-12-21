<?php
namespace app\modules\nlp\console;

use yii\console\Controller;
use yii\helpers\Json;
use app\components\helpers\ConfigHelper;
use yii\db\Query;
use app\models\sl\SlTaskScheduleCrontabConsole;
use app\models\nlp\NlpEngineTaskItemConsole;
use app\models\nlp\NlpEngineLogConsole;

use app\models\nlp\FilterUploadTxtForm;
use yii\helpers\Console;
use Yii;
/**
 * NLP系统任务执行
 *
 * @author libin <libin@3ti.us>
 * @since 2.0
 */
class NlpTaskController extends Controller
{
	private $_errMsg = '';

	/**
	 * overide parent method stdout
	 *
	 */
	public function stdout($string)
    {
    	$this->_errMsg = $string;
    	return parent::stdout($string);
    }

	/**
	 * 定时扫描 `nlp_engine_task_item` 检查ready状态的任务
	 * 并执行已准备就绪的任务
	 */	
	public function actionTick()
	{
		$items = NlpEngineTaskItemConsole::find()
					->where(['status' => NlpEngineTaskItemConsole::STATUS_READY])
					->asArray()
					->all();

		$insertLogString = 'INSERT INTO ' . NlpEngineLogConsole::tableName() . ' (module, cmd, params, msg, status, add_time)VALUES';

		foreach ($items as $v)
		{
			//update nlp_engine_task_item `status` = {executing}
			Yii::$app->db->createCommand('UPDATE ' . NlpEngineTaskItemConsole::tableName() . ' SET status = ' . NlpEngineTaskItemConsole::STATUS_EXECUTING . ' WHERE id = ' . $v['id'])
						->execute();

			$ret = Yii::$app->runAction($v['module'] .'/'. $v['cmd'], array_values( Json::decode($v['param_list'], true) ) );
			
			Yii::$app->db->createCommand('UPDATE ' . NlpEngineTaskItemConsole::tableName() . ' SET status = ' . NlpEngineTaskItemConsole::STATUS_COMPLETE . ' WHERE id = ' . $v['id'])
						->execute();

			if($ret === 0)
			{
				$insertLogString .= '(\''. $v['module'] . '\', \'' . $v['cmd'] . '\', \'' . $v['param_list'] . '\', \'\', 0, ' . time() . '),';
			}
			else
			{
				$insertLogString .= '(\''. $v['module'] . '\', \'' . $v['cmd'] . '\', \'' . $v['param_list'] . '\', \'' . $this->_errMsg . '\', ' . $ret . ', ' . time() . '),';
			}
			$this->_errMsg = '';
		}

		if(!empty($items))
		{
			Yii::$app->db->createCommand(substr($insertLogString, 0, -1))->execute();
		}	

		return 0;

	}

	/**
	 * 对指定数据表的指定字段做分词和标注，输出表是以`nlp_seg_`为前缀，tagTable指定名为后缀的
	 * notice：指定的数据表至少包含两个字段 `code`(数据主键)，sourceFiled所指定的字段
	 * e.g. data_table data_field tag_table food,electrical
	 * 需要指定四个参数： sourceTable，sourceField，tagTable，dictNames
	 * @param $sourceTable 进行分词的数据表
	 * @param $sourceField 进行分词的数据字段，只支持单个字段
	 * @param $tagTable 分词输出表
	 * @param $dictNames 分词使用的词库列表，多个词库以','分割，词库中如出现重复分词，则后面的会覆盖前面
	 */
	public function actionFreeTag($sourceTable, $sourceField, $tagTable, $dictNames)
	{
		//paramaters lost
		if(!$sourceTable || !$sourceField || !$tagTable || !$dictNames)
		{
			$this->stdout('参数缺失', Console::BOLD);
			return 1;
		}

		//********************************************************* step 1. merge dict **********************************************
		if($this->mergeDict($dictNames) === false)
		{
			$this->stdout('词库表 合并失败', Console::BOLD);
			return -1;
		}

		//********************************************************* step 2. segment **********************************************
		$tableCheck = Yii::$app->db->createCommand("SHOW TABLES LIKE '". $sourceTable . "'" )->queryOne();//检查数据存放表是否存在
		$fieldCheck = Yii::$app->db->createCommand("SHOW FIELDS FROM ". $sourceTable)->queryAll();//检查数据存放字段是否存在

		$copyFields = $fieldCheck;
		$fieldCheck = [];

		foreach ($copyFields as $f) 
		{
			$fieldCheck[] = $f['Field'];
		}

		//data source not exists , uncompleted
		if(!$tableCheck || !in_array($sourceField, $fieldCheck) || !in_array('code', $fieldCheck) )
		{
			$this->stdout('数据来源表不存在 或 数据来源表的字段缺少', Console::BOLD);
			return 3;
		}

		//drop tag table if exists
		$tableTag = 'nlp_seg_' . $tagTable;
		Yii::$app->db->createCommand("DROP TABLE IF EXISTS `". $tableTag . "`;" )->execute();

		$tableTagCreate = Yii::$app->db->createCommand(
			"CREATE TABLE `". $tableTag ."` (" . 
			  "`code` text NOT NULL COMMENT '全局编码'," .
			  "`word` text NOT NULL COMMENT '分词'," .
			  "`tag` char(100) NOT NULL DEFAULT '' COMMENT '词性标注'" .
			") ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='".$sourceTable."词性分析结果';"
		)->execute();//创建标记数据表

		//create table failed
		if($tableTagCreate === false)
		{
			$this->stdout('切分表创建失败', Console::BOLD);
			return 4;
		}
		
		//source records
		$wsQuery = (new Query())->from( $sourceTable )->select('code, '. $sourceField);
		$wsCount = $wsQuery->count();

		$loopSize = 10000;
		$loopCount = ceil($wsCount / $loopSize);

		//分批插入（每次最多1w条），防止内存占用过大
		for($i = 0; $i < $loopCount;$i++)
		{
			if(!$wsQuery)
				$wsQuery = (new Query())->from( $sourceTable )->select('code, '. $sourceField);

			$offset = $i * $loopSize;
			$wsQuery->limit($loopSize)->offset($offset);
		
			$insertSql = 'INSERT INTO ' . $tableTag . ' (code, word, tag) VALUES ';
			foreach ($wsQuery->each() as $c)
			{
				$c[$sourceField] = trim($c[$sourceField]);
				if($c[$sourceField])
				{
					$segments = jieba($c[$sourceField], 2);//['word' => 'tag']

					$wordArr = array_keys($segments);
					$wordArr = $this->_segBysort($wordArr, $c[$sourceField]);
					$wordArr = $this->_segByAdd($wordArr, $c[$sourceField]);

					$insertSql .=  $this->_spellSegSql( $c['code'], $wordArr, $segments );
				}
			}
			
			$wsQuery = null;
			$c = null;
			
			$insertRet = Yii::$app->db->createCommand(substr($insertSql,0, -1))->execute();#插入数据
			
			$insertSql = null;

			if($insertRet === false)
			{
				$this->stdout('切分表插入失败', Console::BOLD);
				return 17;
			}
		}
		/***生成每日子任务 END***/
		return 0;
	}

	/**
	 * 对指定日期和任务名称的抓取数据表做分词和标注，结果存储在`nlp_seg_`的前缀表
	 * 例如对于数据表`ws_36_20171020_261`，分词和标注结果存在`nlp_seg_36_20171020_261`
	 * 需要指定三个参数： startDate 和 name dictNames
	 * @param $startDate 任务的计划开始日期
	 * @param $name 任务名称
	 * @param $dictNames 分词使用的词库列表，多个词库以','分割，词库中如出现重复分词，则后面的会覆盖前面
	 */
	public function actionTag($startDate, $name, $dictNames)
	{
		// NlpLogConsole::find();
		// (new Query())->from('nlp_log')->where('ws_data')
		// $startDate = '2017-11-11';
		// $name = 'DMP_CLEANER_BRAND';

		//paramaters lost
		if(!$name || !$startDate)
		{
			$this->stdout('name 或 startDate 参数为空', Console::BOLD);
			return 1;
		}

		if(!$dictNames)
		{
			$this->stdout('dictNames 参数为空', Console::BOLD);
			return 1;	
		}

		//********************************************************* step 1. merge dict **********************************************
		if($this->mergeDict($dictNames) === false)
		{
			$this->stdout('词库表 合并失败', Console::BOLD);
			return -1;
		}

		//********************************************************* step 2. segment **********************************************
		$create_time_start = strtotime($startDate);
		$create_time_end = $create_time_start + 3600 * 24;

		$q = SlTaskScheduleCrontabConsole::find();

		$q->select('id, sche_id,start_time, task_status')
			->where('create_time >= :create_time_start and create_time <= :create_time_end', [':create_time_start' => $create_time_start, ':create_time_end' => $create_time_end])
			->andWhere('name = :name', [':name' => $name]);

		$crontabData = $q->asArray()->limit(1)->one();
		$q = null;

		if( $crontabData )
		{

			$start_date_ret = preg_replace('/-/', '', substr($crontabData['start_time'], 0, 10));
			$crontabData['table'] = 'ws_' . $crontabData['sche_id']. '_'.$start_date_ret.'_'.$crontabData['id'];

			$tableCheck = Yii::$app->db->createCommand("SHOW TABLES LIKE '". $crontabData['table'] . "'" )->queryOne();//检查数据存放表是否存在

			//data source not exists , uncompleted
			if(!$tableCheck || $crontabData['task_status'] != SlTaskScheduleCrontabConsole::TASK_STATUS_COMPLETED)
			{
				$this->stdout('数据来源表不存在 或 数据未抓取完毕', Console::BOLD);
				return 3;
			}

			//drop tag table if exists
			$tableTag = 'nlp_seg_' . $crontabData['sche_id']. '_'.$start_date_ret.'_'.$crontabData['id'];
			Yii::$app->db->createCommand("DROP TABLE IF EXISTS `". $tableTag . "`;" )->execute();

			$tableTagCreate = Yii::$app->db->createCommand(
				"CREATE TABLE `". $tableTag ."` (" . 
				  "`id` int(10) unsigned NOT NULL DEFAULT '0'," .
				  "`code` text NOT NULL COMMENT '全局编码'," .
				  "`word` text NOT NULL COMMENT '分词'," .
				  "`tag` char(100) NOT NULL DEFAULT '' COMMENT '词性标注'" .
				") ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='".$crontabData['table']."词性分析结果';"
			)->execute();//创建标记数据表

			//create table failed
			if($tableTagCreate === false)
			{
				$this->stdout('切分表创建失败', Console::BOLD);
				return 4;
			}
			
			//source records
			$wsQuery = (new Query())->from( $crontabData['table'] )->select('id, product_code, product_title ');
			$wsCount = $wsQuery->count();

			$loopSize = 10000;
			$loopCount = ceil($wsCount / $loopSize);

			//分批插入（每次最多1w条），防止内存占用过大
			for($i = 0; $i < $loopCount;$i++)
			{
				if(!$wsQuery)
					$wsQuery = (new Query())->from( $crontabData['table'] )->select('id, product_code, product_title ');

				$offset = $i * $loopSize;
				$wsQuery->limit($loopSize)->offset($offset);
			
				$insertSql = 'INSERT INTO ' . $tableTag . ' (id, code, word, tag) VALUES ';
				foreach ($wsQuery->each() as $c)
				{
					$segments = jieba($c['product_title'], 2);//['word' => 'tag']

					$wordArr = array_keys($segments);
					$wordArr = $this->_segBysort($wordArr, $c['product_title']);
					$wordArr = $this->_segByAdd($wordArr, $c['product_title']);

					$insertSql .=  $this->_spellSegSql( $c['id'], $c['product_code'], $wordArr, $segments );
				}
				
				$wsQuery = null;
				$c = null;
				
				$insertRet = Yii::$app->db->createCommand(substr($insertSql,0, -1))->execute();#插入数据
				
				$insertSql = null;

				if($insertRet === false)
				{
					$this->stdout('切分表插入失败', Console::BOLD);
					return 17;
				}
			}
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
			$p = mb_strpos($stat, $i, 0, 'utf-8');
			if(!isset($ksArr[$p]))
			{
				$ksArr[$p]  = $i;
			}
			else//from new offset find position to avoid replace the lastest position
			{
				$p = mb_strpos($stat, $i, $p+1, 'utf-8');
				$ksArr[$p] = $i;
			}

		}
		ksort($ksArr, SORT_NUMERIC);
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
		$iMap = array_fill(0, mb_strlen($stat, 'utf-8'),'');#整个位置图
		$cMap = [];#使用的位置图
		foreach ($seg as $i => $s) 
		{
			$cm = array_fill( $i, mb_strlen($s, 'utf-8'), '');
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
			$seg[$start] = mb_substr($stat, $start, $end - $start + 1, 'utf-8');
			// echo "<br>add内部<br>";
			// var_dump($oMaps, $start, $end - $start + 1, substr($stat, $start, $end - $start + 1));
			
		}
		ksort($seg, SORT_NUMERIC);
		return $seg;
	}

	/**
	 * 获取分词插入sql语句
	 * @param $code 数据抓取唯一编码 : jd_Spider_jd_Spider_4090788
	 * @param $seg 分词词组 : ['word']
	 * @param $tag 分词和词性关联数组 : ['word' => 'tag']
	 * @param $sql 插入mysql的语句
	 */
	private function _spellSegSql($code, $seg, $tag)
	{
		$preSql = '(\'' . $code . '\',';
		$sql = '';

		foreach ($seg as $s)
		{
			if(trim($s))//过滤为空字符串的分词
			{
				if(isset($tag[$s]))//有标注的
				{
					$sql .= $preSql . '\'' . $s . '\', \'' . $tag[$s] . '\'),';
				}
				else//未标注的
				{
					$sql .= $preSql . '\'' . $s . '\', \'\'),';
				}
			}
		}

		return $sql;
	}

	/**
	 * 合并多个词库，按顺序去重后，输出为一个文本文件
	 * @param $dictNames 词库(词性)名列表，不带'nlp_dict_'前缀
	 *
	 */
	private function mergeDict($dictNames)
	{	
		$dictNameArr = explode(',', $dictNames);

		if(empty($dictNameArr))
		{
			$this->stdout('参数错误，没有指定词库表', Console::BOLD);
		    return 1;			
		}

		$dictList = Yii::$app->db->createCommand("SHOW TABLES LIKE 'nlp_dict%'" )->queryAll();//检查数据存放表是否存在
	    $dictList = (array)$dictList;

	    $dictTemp = $dictList;

        $dictTable = '';
        $tagTable = '';

        $dictTables = [];
        $tagTables = [];

        foreach ($dictNameArr as $dict) 
        {
        	foreach ($dictTemp as $t)
	        {
	            $tn = (array_values($t))[0];
	            
	            if($tn == 'nlp_dict_'.$dict)
	            	$dictTable = $tn;
	            if($tn == 'nlp_dict_tag_'.$dict)
	            	$tagTable = $tn;
	        }

	    	if(empty($dictTable))
		    {
		    	$this->stdout('词库表'.$dictTable.'不存在', Console::BOLD);
		    	return 1;
		    }

		    if(empty($tagTable))
		    {
		    	$this->stdout('词性表'.$tagTable.'不存在', Console::BOLD);
		    	return 1;
		    }

		    $dictTables[] = $dictTable;
		    $tagTables[] = $tagTable;

		    $dictTable = '';
		    $tagTable = '';

        }

	    //创建合并输出表
	    $mergeTable = 'nlp_seg_merge';
		Yii::$app->db->createCommand("DROP TABLE IF EXISTS `". $mergeTable . "`;" )->execute();

		$createRet = Yii::$app->db->createCommand(
			"CREATE TABLE `". $mergeTable ."` (" . 
			  "`word` char(255) NOT NULL COMMENT '分词'," .
			  "`weight` float(24,3) unsigned NOT NULL DEFAULT '0.000'," .
			  "`tag` char(100) NOT NULL DEFAULT '' COMMENT '词性标注'," .
			  "UNIQUE KEY `seg_merge_word` (`word`)" .
			") ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='".implode(',', $dictNameArr)."合并表';"
		)->execute();//创建标记数据表

		//create table failed
		if($createRet === false)
		{
			$this->stdout('生成'.implode(',', $dictNameArr).'合并表失败', Console::BOLD);
			return 3;
		}

        foreach ($dictTables as $k => $dictTable) //遍历参数中的词库表
        {
        	$tagTable = $tagTables[$k];
		    //segment records
			$dictQuery = (new Query())->from( $dictTable . ' d ' )->select('d.word, d.weight, t.tag ')->leftJoin('`'.$tagTable . '` t ', ' d.tag_id = t.id');
			$dictCount = $dictQuery->count();

			$loopSize = 10000;
			$loopCount = ceil($dictCount / $loopSize);

	        //分批写入（每次最多1w条），防止内存占用过大
			for($i = 0; $i < $loopCount;$i++)
			{
				if(!$dictQuery)
					$dictQuery = (new Query())->from( $dictTable . ' d ' )->select('d.word, d.weight, t.tag ')->leftJoin('`'.$tagTable . '` t ', ' d.tag_id = t.id');

				$offset = $i * $loopSize;
				$dictQuery->limit($loopSize)->offset($offset);
				
				$insertSql = 'INSERT INTO ' . $mergeTable . ' (word, weight, tag) VALUES ';
				foreach ($dictQuery->each() as $c)
				{
					$insertSql .= '(\''. $c['word'] . '\', ' . $c['weight'] . ', \'' . $c['tag'] . '\'),';
				}
				
				$dictQuery = null;
				
				if(!empty($c))
				{
					$insertRet = Yii::$app->db->createCommand(substr($insertSql, 0, -1).' ON DUPLICATE KEY UPDATE `word` = VALUES(`word`), `weight` = VALUES(`weight`), `tag` = VALUES(`tag`);')->execute();#插入数据
					
					$insertSql = null;

					if($insertRet === false)
					{
						$this->stdout('合并词库'.$dictTable.'失败', Console::BOLD);
						return 17;
					}
				}
				$c = null;
			}
		}

		//输出为 user.dict.utf8
		if( !is_writable(Yii::$app->params['DICT_PATH'].'user.dict.utf8') || ($dictRes = fopen(Yii::$app->params['DICT_PATH'].'user.dict.utf8', 'w')) === false )//获取文件句柄
		{
			$this->stdout(Yii::$app->params['DICT_PATH'].'user.dict.utf8 创建失败', Console::BOLD);
			return 21;
		}


		$mergeQuery = (new Query())->from( $mergeTable )->select('word, weight, tag');
		$mergeCount = $mergeQuery->count();

		$loopSize = 10000;
		$loopCount = ceil($mergeCount / $loopSize);

        //分批写入（每次最多1w条），防止内存占用过大
		for($i = 0; $i < $loopCount;$i++)
		{
			if(!$mergeQuery)
				$mergeQuery = (new Query())->from( $mergeTable )->select('word, weight, tag');

			$offset = $i * $loopSize;
			$mergeQuery->limit($loopSize)->offset($offset);	

			foreach($mergeQuery->each() as $m)
			{

				if( fwrite( $dictRes, $m['word'] .' ' . $m['weight']. ' ' . $m['tag']."\n") === false )
				{
					$this->stdout(Yii::$app->params['DICT_PATH'].'user.dict.utf8 写入失败', Console::BOLD);
					return 25;
				}
			}
			
			$mergeQuery = null;
		}

		fclose($dictRes);
		return true;
		
	}


	/**
	 * 从爬虫抓取到的词库表导入到正式词库(词性)表
	 * 需要指定采集表名 $from，正式词库表名$to
	 * @param $from 爬虫自动采集到的词库存放表 e.g. sl_ws_cleaner_words
	 * @param $to 被导入的正式词库表dict和词性表tag表名，程序会自动加上该前缀，e.g. food 会导入到 nlp_dict_food 和 nlp_dict_tag_food
	 */
	public function actionImportMysql($from, $to)
	{
		
		if(empty($from) || empty($to))
		{
			$this->stdout('参数:数据表 或 参数:导入表名 没有指定', Console::BOLD);
			return -1;//参数不正确
		}

		$fromCheck = Yii::$app->db->createCommand("SHOW TABLES LIKE '$from'" )->queryOne();//检查数据表是否存在
		if( !$fromCheck )
		{
			$this->stdout('参数:数据表 经检查，不存在该表', Console::BOLD);
			return -1;//指定的数据表不存在
		}

		$dictList = Yii::$app->db->createCommand("SHOW TABLES LIKE 'nlp_dict%'" )->queryAll();//检查导入表是否存在
	    $dictList = (array)$dictList;

	    $dictTemp = $dictList;

        $dictTable = '';
        $tagTable = '';
        foreach ($dictTemp as $t) 
        {
            $tn = (array_values($t))[0];
            
            if($tn == 'nlp_dict_'.$to)
            	$dictTable = $tn;
            if($tn == 'nlp_dict_tag_'.$to)
            	$tagTable = $tn;
        }

        if(empty($dictTable))//$to 词库表不存在
        {
        	$this->stdout('词库表:'.$dictTable.' 不存在，导入数据前手动上传Excel模版创建该表', Console::BOLD);
        	return -3;
        	/*
        	$dictTable = 'nlp_dict_'.$to;
        	$dictTableCreate = Yii::$app->db->createCommand(
                "CREATE TABLE `". $dictTable ."` (" . 
                  "`id` int(10) unsigned NOT NULL AUTO_INCREMENT," .
                  "`word` char(30) NOT NULL DEFAULT ''," .
                  "`weight` float(24,10) unsigned NOT NULL DEFAULT '0.0000000000'," .
                  "`tag_id` int(10) unsigned DEFAULT '0'," .
                  "`prime_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '近义词代表词id'," .
                  "`synonym_ids` text NOT NULL COMMENT '近义词id集合'," .
                  "PRIMARY KEY (`id`)," .
                  "UNIQUE KEY `nlp_dict_" . $to ."_word` (`word`)" .
                ") ENGINE=InnoDB DEFAULT CHARSET=utf8 DEFAULT COLLATE=utf8_bin COMMENT='".$to."分词词库';"
            )->execute();//创建词库表

            if($dictTableCreate === false)
            {
            	$this->stdout('词库表'.$dictTable .'创建失败', Console::BOLD);
                return -3;
            }
            */
        }

        if(empty($tagTable))//$to 词性表不存在
        {
        	$this->stdout('词性表:'.$tagTable.' 不存在，导入数据前手动上传Excel模版创建该表', Console::BOLD);

        	return -3;
        	/*
        	$tagTable = 'nlp_dict_tag_'.$to;
        	$tagTableCreate = Yii::$app->db->createCommand(
                "CREATE TABLE `". $tagTable ."` (" . 
                  "`id` int(11) unsigned NOT NULL AUTO_INCREMENT," .
                  "`tag` char(30) NOT NULL DEFAULT '' COMMENT '标签'," .
                  "`pid` int(11) unsigned NOT NULL DEFAULT '0'," .
                  "`tag_zh` char(100) NOT NULL DEFAULT '' COMMENT '标签中文'," .
                  "PRIMARY KEY (`id`)," .
                  "UNIQUE KEY `tag_".$to."_tag` (`tag`)," .
                  "UNIQUE KEY `tag_".$to."_tag_zh` (`tag_zh`)" .
                ") ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='".$to."分词词性';"
            )->execute();//创建词性表

            if($tagTableCreate === false)
            {
                $this->stdout('词性表'.$tagTable .'创建失败', Console::BOLD);
                return -3;
            }
            */
        }

        //获取抓取的分词数据
        $wsQuery = (new Query())->from( $from )->select('key_name, key_type');
		$wsCount = $wsQuery->count();

		$loopSize = 10000;
		$loopCount = ceil($wsCount / $loopSize);

		//分批插入词库（每次最多1w条），防止内存占用过大
		for($i = 0; $i < $loopCount;$i++)
		{
			//************************************************************* step 1. insert tag ***********************************************
			if(!$wsQuery)
				$wsQuery = (new Query())->from( $from )->select('key_name, key_type');

			$offset = $i * $loopSize;
			$wsQuery->limit($loopSize)->offset($offset);

			$tagZhArr = [];
			$dictArr = [];
				
			$tagSql = 'INSERT INTO ' . $tagTable . ' (tag) VALUES ';//tag, tag_zh 填写为同一词语

			foreach ($wsQuery->each() as $c)
			{
				$tagZh = $this->fileterWord($c['key_type']);
				if(!$tagZh)
					continue;

				$dict = $this->fileterWord($c['key_name']);
				if(!$dict)
					continue;

				$tagZhArr[] = $tagZh;
				$dictArr[] = $dict;

				$tagSql .=  '(\'' . $tagZh . '\'),';
			}
			
			$wsQuery = null;
			$c = null;
			
			$insertRet = Yii::$app->db->createCommand(substr($tagSql,0, -1) .' ON DUPLICATE KEY UPDATE `tag` = VALUES(`tag`);')->execute();#插入词性表
			
			$tagSql = null;

			if($insertRet === false)
			{
				$this->stdout('词性表'.$tagTable .'数据填充失败', Console::BOLD);
				return 10;
			}

			//***********************************************************step 2. insert dict ********************************************************
			//查询tag_id
			$tagQuery = (new Query())->from( $tagTable )->select('id, tag')->where(['in', 'tag', array_unique( $tagZhArr)] );

			//before $tagZhArr = [ 'index' => 'tag_zh' ...]
			foreach ($tagQuery->each() as $t) 
			{
				$tagZhIndexs = array_keys($tagZhArr, $t['tag']);//find the keys of the same tag_zh in array $tagZhArr
				foreach ($tagZhIndexs as $i) 
				{
					$tagZhArr[$i] = $t['id'];//replace the value of tag_zh with the value of tag_id in array $tagZhArr
				}
			}
			$tagZhIndexs = null;
			$tagQuery = null;

			//after $tagZhArr = [ 'index' => 'tag_id' ...]

			//填充词库 插入字段有:word, tag_id, prime_id, synonym_ids
			$dictSql = 'INSERT INTO ' . $dictTable . ' (word, tag_id) VALUES ';
			foreach ($dictArr as $i => $d) 
			{
				$dictSql .= '(\'' . $d . '\', ' . $tagZhArr[$i].'),';
			}
			$insertRet = Yii::$app->db->createCommand(substr($dictSql,0, -1) .' ON DUPLICATE KEY UPDATE `word` = VALUES(`word`);')->execute();#插入词库表

			$dictSql = null;
			$tagZhArr = null;

			if($insertRet === false)
			{
				$this->stdout('词库表'.$dictTable .'数据填充失败', Console::BOLD);
				return 11;
			}

			//***********************************************************step 3. update synonym_ids ********************************************************
			//更新近义词
			//use tables' word, prime_id, synonym_ids, if not exist then use current value
			$needUpdate = false;
			$dictQuery = (new Query())->from( $dictTable )->select('id, word, prime_id, synonym_ids')->where(['in', 'word', array_unique( $dictArr)] );

			$dictSql = 'INSERT INTO ' . $dictTable . ' (word, prime_id, synonym_ids) VALUES ';
			foreach ($dictQuery->each() as $d) 
			{
				if(empty((int)$d['prime_id']))//update prime_id, synonym_ids ,use self id
				{
					$needUpdate = true;
					$dictSql .= '(\'' . $d['word'] . '\', ' . $d['id']. ', ' . $d['id'] . '),';
				}
			}

			if($needUpdate)
			{
				$insertRet = Yii::$app->db->createCommand(substr($dictSql,0, -1) .' ON DUPLICATE KEY UPDATE `prime_id` = VALUES(`prime_id`), `synonym_ids` = VALUES(`synonym_ids`);')->execute();#插入词库表

				$dictQuery = null;
				$dictSql = null;
				$dictArr = null;

				if($insertRet === false)
				{
					$this->stdout('词库表'.$dictTable .'数据填充失败', Console::BOLD);
					return 17;
				}
			}

		}

		return 0;

	}

	/**
	 * 过滤首尾非法字符
	 * @param $str 需要过滤的字符串
	 * @return $newStr 过滤后的字符串
	 */
	private function fileterWord($str)
	{
		static $_filterConfig;
		if(!$_filterConfig)
		{
			$_filterConfig = ConfigHelper::parseIniToLine(FilterUploadTxtForm::getSaveName());
			if(empty($_filterConfig))
			{
				$_filterConfig = ['ltrim' => [], 'rtrim' => []];
			}
		}

		$l = mb_strlen($str, 'utf-8');

		$chars = '';
		foreach ($_filterConfig['ltrim'] as $c) 
		{
			$chars .= $c;
		}
		$str = ltrim( $str, $chars );

		$chars = '';
		foreach ($_filterConfig['rtrim'] as $c) 
		{
			$chars .= $c;
		}
		$str = rtrim( $str, $chars );

		//去括号
		$lb = substr_count($str, '（');
		$rb = substr_count($str, '）');
		if($rb > $lb)
			$str= rtrim($str, '）');

		$lb = substr_count($str, '(');
		$rb = substr_count($str, ')');
		if($rb > $lb)
			$str= rtrim($str, ')');

		return $str;
	}
}
