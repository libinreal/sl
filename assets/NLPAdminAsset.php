<?php
namespace app\assets;

use yii\web\AssetBundle;

class NLPAdminAsset extends AssetBundle
{

    public $basePath = '@webroot';
    public $baseUrl = '@web';

    public $css = [
        'admin/css/frame.css',
        'admin/css/public.css',
        'admin/css/content.css',
        'admin/lib/selectify/silver.default.css',
    ];
    public $js = [];

    public $depends = [
    'yii\web\JqueryAsset'
    ];

    public static function addScript($view, $jsfile) {
        $view->registerJsFile($jsfile, [NLPAdminAsset::className(), 'depends' => 'app\assets\NLPAdminAsset']);
    }

    public static function addCss($view, $cssfile) {
        $view->registerCssFile($cssfile, [NLPAdminAsset::className(), 'depends' => 'app\assets\NLPAdminAsset']);
    }
}