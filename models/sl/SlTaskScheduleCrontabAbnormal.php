<?php

namespace app\models\sl;

use Yii;

/**
 * This is the model class for table "sl_task_schedule_crontab_abnormal".
 *
 * @property integer $id
 * @property integer $cron_id
 * @property integer $sche_id
 * @property integer $abnormal_type
 * @property string $msg
 */
class SlTaskScheduleCrontabAbnormal extends \yii\db\ActiveRecord
{
    const ABNORMAL_TYPE_NONE = 0;
    const ABNORMAL_TYPE_DURATION = 1;
    const ABNORMAL_TYPE_NUM_LESS = 2;
    const ABNORMAL_TYPE_NUM_MORE = 4;

    const RESOLVE_TYPE_UNRESOLVED = 0;
    const RESOLVE_TYPE_RESOLVED = 1;
    const RESOLVE_TYPE_IGNORED = 2;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sl_task_schedule_crontab_abnormal';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['cron_id', 'sche_id', 'abnormal_type', 'resolve_stat', 'add_time'], 'integer'],
            [['msg', 'name'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '异常日志id',
            'cron_id' => '实际任务id',
            'sche_id' => '计划id',
            'name' => '计划名',
            'abnormal_type' => '异常类型(0:正常1:爬取时间异常2:爬取数量过小4:爬取数量过大)',
            'msg' => '异常信息',
            'resolve_stat' => '解决状态(0:未解决1:已解决2:忽略)',
            'add_time' => '添加时间',
        ];
    }

    public static function getDb()
    {
        return Yii::$app->getModule('sl')->db;
    }

    public function getSearchQuery()
    {
        $query = static::find();

        $this->load( Yii::$app->request->post(), '' );
        if (!$this->validate())
        {
            return false;
        }

        $post = Yii::$app->request->post();
        if( isset( $post['add_time_s'] ) && !empty( $post['add_time_s'] ) )
        {
            $query->andFilterWhere(['>=', 'add_time', strtotime($post['add_time_s'])]);
        }
        if( isset( $post['add_time_e'] ) && !empty( $post['add_time_e'] ) )
        {
            $query->andFilterWhere(['<=', 'add_time', strtotime($post['add_time_e'])]);
        }

        $query->andFilterWhere(['cron_id' => $this->cron_id])
            ->andFilterWhere(['sche_id' => $this->sche_id])
            ->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['abnormal_type' => $this->abnormal_type])
            ->andFilterWhere(['resolve_stat' => $this->resolve_stat])
            ->andFilterWhere(['like', 'msg', $this->msg]);
            /*$t = clone $query;
        echo $t->createCommand()->getRawSql();exit;*/

        return $query;
    }

    public static function getDurationMsg($act_duration , $alert_duration)
    {
        $delay = (float)$act_duration - (float)$alert_duration;
        $per = round($delay / $alert_duration, 3) * 100;
        return "抓取耗时{$act_duration}h，预警值{$alert_duration}h，超时{$delay}h，占比<font font-weight='bold' font-color='red'>${per}%</font>";
    }

    public static function getNumMinMsg($act_num, $alert_min)
    {
        $distance = $alert_min - $act_num;
        $per = round($distance / $alert_min, 3) * 100;
        return "抓取共{$act_num}条，预警值{$alert_min}条，缺少{$distance}条，占比<font font-weight='bold' font-color='red'>${per}%</font>";
    }

    public static function getNumMaxMsg($act_num, $alert_max)
    {
        $distance = $act_num - $alert_max;
        $per = round($distance / $alert_max, 3) * 100;
        return "抓取共{$act_num}条，预警值{$alert_max}条，超出{$distance}条，占比<font font-weight='bold' font-color='red'>${per}%</font>";
    }
}
