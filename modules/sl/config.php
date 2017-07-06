<?php
return [
    'layout' => 'default',
    'components' => [
        //analysis database
        'db' => [
            'class' => '\yii\db\Connection',
            'dsn' => 'mysql:host=61.155.169.179;port=23306;dbname=webspider',
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
    'params' => [],
];