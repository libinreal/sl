<?php
return [
    'layout' => 'default',
    'components' => [
        //analysis database
        'db' => [
            'class' => '\yii\db\Connection',
            'dsn' => 'mysql:host=192.168.10.207;port=3306;dbname=webspider',
            'username' => 'webspider',
            'password' => '$b1cFERT@!',
            'tablePrefix' => '',
            'charset' => 'UTF8',
        ],
        //analysis database
        'mongodb' => [
            'class' => '\yii\mongodb\Connection',
            'dsn' => 'mongodb://192.168.2.187:27017'
        ],
        'cache' => [
            'class' => 'yii\caching\Cache'
        ],
    ],
    'params' => [
        'PLATFORM_LIST'=>[
            'pf_jd' => '京东',
            'pf_tmall' => '天猫',
        ],
    ],
];