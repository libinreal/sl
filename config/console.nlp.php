<?php

$config = [
    'id' => 'basic-console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'app\modules\nlp\console',
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning','info'],
                    'logVars'=>[],
                    'categories'=>['yii\db\*','app\models\*'],
                    'logFile'=>'@runtime/logs/sql/sql.console.nlp.log'.date('Ymd'),
                ],
            ],
        ],
        'db' => [
            'class' => '\yii\db\Connection',
            'dsn' => 'mysql:host=192.168.10.207;port=3306;dbname=webspider',
            'username' => 'webspider',
            'password' => '$b1cFERT@!',
            'tablePrefix' => '',
            'charset' => 'UTF8',
        ]
    ],
    'params' => [
        'PLATFORM_LIST'=>[
            'pf_jd' => '京东',
            'pf_tmall' => '天猫',
            'pf_weixin' => '微信',
        ],
    ],
    /*
    'controllerMap' => [        'fixture' => [ // Fixture generation command line.
            'class' => 'yii\faker\FixtureController',
        ],
    ],
    */
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
    ];
}

return $config;
