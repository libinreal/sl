<?php

$params = require(__DIR__ . '/params.php');
$urlRules = require(__DIR__ . '/url.sl.php');
$i18n = require(__DIR__ . '/i18n.php');

$config = [
    'id' => 'basic',
    'name' => 'Media Monitoring Service',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'modules' => [
        'res' => [
            'class' => 'app\modules\res\Module',
        ],
        'ctrl' => [
            'class' => 'app\modules\ctrl\Module',
        ],
        'nlp' => [
            'class' => 'app\modules\nlp\Module',
        ],
        'sl' => [
            'class' => 'app\modules\sl\Module',
        ],
    ],
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'nwv6G9utgXscTOwFReqQYFFXxyCHUCVU',
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ],
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            'useFileTransport' => true,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => require(__DIR__ . '/db.php'),
        //analysis database
        'spiderMongodb' => [
            'class' => '\yii\mongodb\Connection',
            'dsn' => 'mongodb://192.168.2.187:27017/comment'
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'enableStrictParsing' => true,
            'showScriptName' => false,
            'rules' => $urlRules,
        ],
        'i18n' => $i18n,
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        'allowedIPs' => ['192.168.91.*'],
        'panels' => [
            'db' => [
                'class' => 'yii\debug\panels\DbPanel',
                'defaultOrder' => [
                    'seq' => SORT_ASC
                ],
                'defaultFilter' => [
                    'type' => 'SELECT'
                ]
            ],
        ],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        'allowedIPs' => ['192.168.91.*'],
        'generators' => [
                'mongoDbModel' => [
                    'class' => 'yii\mongodb\gii\model\Generator'
                ]
         ],
    ];
}

return $config;
