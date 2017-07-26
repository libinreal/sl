<?php
namespace app\assets;

use yii\web\AssetBundle;

class AdminLteSelect2Asset extends AssetBundle
{
    public $sourcePath = '@vendor/almasaeed2010/adminlte/plugins';
    public $js = [
        'select2/select2.min.js',
        // more plugin Js here
    ];
    public $css = [
        'select2/select2.min.css',
        // more plugin CSS here
    ];
    public $depends = [
        'dmstr\web\AdminLteAsset',
    ];
}