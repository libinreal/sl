<?php
return [
    'components' => [
        //analysis database
        'spiderMysql' => [
            'class' => '\yii\db\Connection',
            'dsn' => 'mysql:host=127.0.0.1;dbname=analyzedb',
            'username' => 'root',
            'password' => '123456',
            'tablePrefix' => 'da_',
            'charset' => 'utf8',
        ],
        //analysis database
        'spiderMongodb' => [
            'class' => '\yii\mongodb\Connection',
            'dsn' => 'mongodb://192.168.2.187:27017'
        ],
        //spider database
        'sourceDb' => [
            'class' => '\yii\db\Connection',
            'dsn' => 'mysql:host=127.0.0.1;dbname=webspider',
            'username' => 'root',
            'password' => '123456',
            'tablePrefix' => 'ws_',
            'charset' => 'utf8',
        ],
        'cache' => [
            'class' => 'yii\caching\Cache'
        ],
    ],
    'params' => [
        'tagDependency.tags' => 'res',
        'adminUsers.AccessTokenExpire' => 10800,
        'adminMenus.cacheExpire' => 10800,
        'taskScheduler.stateDelay' => 60,
        'spiderData.fromSites' => [
            'article' => [ 'baiduNewsSpider' => '百度新闻', 'sogouSpider' => '搜狗微信', 'tianyaSpider' => '天涯论坛', 'sinaWeiboSpider' => '新浪微博'],
            'product' => [ 'taobaoSpider' => '淘宝商城', 'jd_Spider' => '京东商城'],
        ],
    ],
];