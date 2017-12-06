<?php

$config = [
    'id' => 'basic-console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'app\modules\sl\console',
    'timeZone' => 'Asia/Shanghai',
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
                    'logFile'=>'@runtime/logs/sql/sql.console.sl.log'.date('Ymd'),
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'info'],
                    'logVars'=>[],
                    'categories'=>['app\modules\sl\console\SlTaskScheduleController'],
                    'logFile'=>'@runtime/logs/console/sl.log'.date('Ymd'),
                ],
            ],
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'useFileTransport' => false,
            'transport' => [
                'class' => 'Swift_SmtpTransport',
                'host' => 'mail.3ti.us',
                'username' => 'sladmin@3ti.us',
                'password' => '3ti@SL2017',
                'port' => '25',
                'streamOptions' => [
                    'ssl' => [
                        'allow_self_signed' => true,
                        'verify_peer' => false
                    ],
                ],
                'encryption' => 'tls',
            ],
            'messageConfig'=>[ 
                'charset'=>'UTF-8', 
                'from'=>['sladmin@3ti.us'=>'sladmin'] 
            ], 
        ],
        'db' => [
            'class' => '\yii\db\Connection',
            'dsn' => 'mysql:host=192.168.2.17;port=3306;dbname=webspider',
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
        'DEV_EMAIL' =>[
            'wened.wan@3ti.us',
            'libin@3ti.us',
            'zqni@3ti.us',
        ]
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
