<?php

namespace app\components\helpers;

use Yii;
use app\models\sl\SlTaskSchedule;
use app\models\sl\SlGlobalSettings;

/**
 * SettingHelper
 * Usage
 *
 * ```
 * use app\modules\sl\components;
 *
 *
 * @author libin <libin@3ti.us>
 * @since 1.0
 */
class SettingHelper
{
    /**
     * Get platform settings By parent code
     * ```
     * @param mix    $pf         平台名
     * @param string    $item_name  平台的下配置项名
     * @param int    $all_field  0 只返回配置对应的值 1 返回整条配置数据
     * @return array
     */
    public static function getPfSetting( $pf = '', $item_name = '', $all_field = 0)
    {
        if( !$pf ) return array();
        if( !is_array($pf) )
            $pf = array( $pf );

        $pfSettings = SlGlobalSettings::find()->joinWith('children')->where(['in', SlGlobalSettings::tableName().'.code', $pf])->orderBy(SlGlobalSettings::tableName().'.sort_order')->asArray()->all();

        if( empty( $pfSettings ) ) return [];

        $ret = $all_ret = [];

        foreach ($pfSettings as $p)
        {
            $ret[$p['code']] = $ret[$p['code']] = array();
            foreach ($p['children'] as $c)
            {
                $ret[$p['code']][$c['code']] = $c['value'];
                $all_ret[$p['code']][$c['code']] = $c;
            }
        }

        return empty( $item_name ) ? ( $all_field == 0 ? $ret : $all_ret ) : ( $all_field == 0 ? $ret[$pf][$item_name] : $all_ret[$pf][$item_name]);
    }

    /**
     * Get platform settings By code
     * @param mix    $pf         设置名
     * @return object     设置
     */
    public static function getPfSettingByCode($code)
    {
        if( !$code ) return array();
        if( !is_array($code) )
            $code = array( $code );

        $pfSettings = SlGlobalSettings::find()->where(['in', 'code', $code])->all();
    }
}
