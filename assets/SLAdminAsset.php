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
        'sl/lib/xbreadcrumbs/xbreadcrumbs.css',
        'sl/lib/responsive-menu/responsive-menu.css'
    ];
    public $js = [
        'admin/js/jquery-migrate-1.2.1.js',//fix $.browser undefined problem
        'sl/lib/sui/sui.js',
        'sl/lib/xbreadcrumbs/xbreadcrumbs.js',//breadcrumbs
        'sl/lib/responsive-menu/responsive-menu.js'//menus
    ];

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