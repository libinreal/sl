<?php
namespace app\modules\sl\console;

use yii\console\Controller;
use yii\helpers\Json;
use yii\db\Query;

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
		NlpLogConsole::find();
		// (new Query())->from('nlp_log')->where('ws_data')
		/***生成每日子任务 END***/
		return 0;
	}
}
