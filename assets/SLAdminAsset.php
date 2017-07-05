<?php
namespace app\assets;

use yii\web\AssetBundle;

class SLAdminAsset extends AssetBundle
{

    public $basePath = '@webroot';
    public $baseUrl = '@web';

    public $css = [
        'sl/lib/sui/sui.css',
        'sl/lib/sui/sui-append.css',
        'sl/css/frame.css',
        'sl/css/public.css',
        'sl/lib/selectify/silver.default.css',
        'sl/css/sl.css',
    ];
    public $js = [];

    public $depends = [
    'yii\web\JqueryAsset'
    ];

    public static function addScript($view, $jsfile) {
        $view->registerJsFile($jsfile, [SLAdminAsset::className(), 'depends' => 'app\assets\SLAdminAsset']);
    }

    public static function addCss($view, $cssfile) {
        $view->registerCssFile($cssfile, [SLAdminAsset::className(), 'depends' => 'app\assets\SLAdminAsset']);
    }
}