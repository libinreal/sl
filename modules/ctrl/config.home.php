<?php
return [
    'layout' => 'default',
    'components' => [
    	'spiderMysql' => [
    		'class' => 'yii\db\Connection',
		    'dsn' => 'mysql:host=127.0.0.1;dbname=analyzedb',
		    'username' => 'root',
		    'password' => 'dishuihu333',
		    'tablePrefix' => 'da_',
		    'charset' => 'utf8',
    	],
    	'spiderMongodb' => [
    		'class' => 'yii\mongodb\Connection',
		    'dsn' => 'mongodb://192.168.2.187:27017'
    	],
        'user' => [
            'class' => 'yii\web\User',
            'identityClass' => '\app\modules\ctrl\models\AdminUsers', // identityClass must implement the IdentityInterface
            'enableAutoLogin' => true,
            'loginUrl' => ['ctrl/auth/login'],
        ],
        'authManager' => [
            'class' => 'yii\rbac\DbManager',
            'defaultRoles' => ['guest'],
            'db' => 'spiderMysql',
            'cache' => 'spiderMongodb',
        ],
        'cache' => [
            'calss' => 'yii\caching\Cache'
        ],
    ],
    'params' => [
        'tagDependency.tags' => 'ctrl',
        'adminUsers.AccessTokenExpire' => 10800,
        'adminMenus.cacheExpire' => 10800,

    ],

];