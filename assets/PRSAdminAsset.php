<?php
namespace app\assets;

use yii\web\AssetBundle;

class PRSAdminAsset extends AssetBundle
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
        $view->registerJsFile($jsfile, [AdminLteAsset::className(), 'depends' => 'app\assets\PRSAdminAsset']);
    }

    public static function addCss($view, $cssfile) {
        $view->registerCssFile($cssfile, [AdminLteAsset::className(), 'depends' => 'app\assets\PRSAdminAsset']);
    }
}