<?php
namespace app\assets;

use yii\web\AssetBundle;

class NLPAdminAsset extends AssetBundle
{

    public $basePath = '@webroot';
    public $baseUrl = '@web';

    public $css = [
        'nlp/sui/sui.css',
        'nlp/sui/sui-append.css',
        'admin/css/frame.css',
        'admin/css/public.css',
        'admin/css/content.css',
        'nlp/css/nlp.css',
        'admin/lib/selectify/silver.default.css',
        'nlp/xbreadcrumbs/xbreadcrumbs.css',
    ];
    public $js = [
        'nlp/sui/sui.js',
        'admin/js/jquery-migrate-1.2.1.js',//fix $.browser undefined problem
        'nlp/xbreadcrumbs/xbreadcrumbs.js',//breadcrumbs
    ];

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