<?php

$config = [
    'id' => 'basic-console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'app\modules\sl\console',
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
                    'logFile'=>'@runtime/logs/sql/sql.log'.date('Ymd'),
                ],
            ],
        ],
        'db' => [
            'class' => '\yii\db\Connection',
            'dsn' => 'mysql:host=61.155.169.179;port=23306;dbname=webspider',
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
