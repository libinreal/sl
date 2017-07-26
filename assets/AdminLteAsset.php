<?php
namespace app\assets;

use yii\web\AssetBundle;

class AdminLteAsset extends AssetBundle
{

    public $basePath = '@webroot';
    public $baseUrl = '@web';

    public $css = [
        // '//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css',
        // 'admin/css/reset.css',
        // 'admin/css/style.css',
        'admin/css/default.css',
    ];
    public $js = [
        'admin/js/default.js',
    ];

    public $depends = [
        'dmstr\web\AdminLteAsset',
    ];

    public static function addScript($view, $jsfile) {
        $view->registerJsFile($jsfile, [AdminLteAsset::className(), 'depends' => 'app\assets\AdminLteAsset']);
    }

    public static function addCss($view, $cssfile) {
        $view->registerCssFile($cssfile, [AdminLteAsset::className(), 'depends' => 'app\assets\AdminLteAsset']);
    }
}