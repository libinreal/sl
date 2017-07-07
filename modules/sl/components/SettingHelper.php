<?php

namespace app\modules\sl\components;

use Yii;
use app\modules\sl\models\SlTaskSchedule;
use app\modules\sl\models\SlGlobalSettings;

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
     * Get platform settings
     * ```
     * @param mix    $pf         平台名
     * @param string    $item_name  平台的下配置项名
     * @return array
     */
    public static function getPfSetting( $pf = '', $item_name = '')
    {
        if( !$pf ) return array();
        if( !is_array($pf) ) return array( $pf );

        $pfSettings = SlGlobalSettings::find()->joinWith('children')->where(['in', SlGlobalSettings::tableName().'.code', $pf])->orderBy(SlGlobalSettings::tableName().'.sort_order')->asArray()->all();

        if( empty( $pfSettings ) ) return [];

        $ret = [];

        foreach ($pfSettings as $p)
        {
            $ret[$p['code']] = array();
            foreach ($p['children'] as $c)
            {
                $ret[$p['code']][$c['code']] = $c['value'];
            }
        }

        return empty( $item_name ) ? $ret : $ret[$pf][$item_name];
    }
}
