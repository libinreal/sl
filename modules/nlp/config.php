<?php
return [
    'layout' => 'default',
    'components' => [
        //analysis database
        'mysql' => [
            'class' => '\yii\db\Connection',
            'dsn' => 'mysql:host=192.168.2.187;dbname=analyzedb',
            'username' => '',
            'password' => '',
            'tablePrefix' => '',
            'charset' => '',
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
    ],
];