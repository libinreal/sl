<?php
return [
    'layout' => 'default',
    'components' => [
        //analysis database
        'db' => [
            'class' => '\yii\db\Connection',
            'dsn' => 'mysql:host=192.168.2.17;port=3306;dbname=webspider',
            'username' => 'webspider',
            'password' => '$b1cFERT@!',
            'tablePrefix' => '',
            'charset' => 'UTF8',
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
                'encryption' => 'ssl',
            ],
            'messageConfig'=>[ 
                'charset'=>'UTF-8', 
                'from'=>['sladmin@3ti.us'=>'sladmin'] 
            ], 
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
            'pf_weixin' => '微信',
        ],
    ],
];