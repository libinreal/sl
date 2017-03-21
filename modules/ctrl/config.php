<?php
return [
    'layout' => 'default',
    'components' => [
    	'spiderMysql' => [
    		'class' => '\yii\db\Connection',
		    'dsn' => 'mysql:host=192.168.2.187;dbname=analyzedb',
		    'username' => 'root',
		    'password' => '3ti123',
		    'tablePrefix' => 'da_',
		    'charset' => 'utf8',
    	],
    	'spiderMongodb' => [
    		'class' => '\yii\mongodb\Connection',
		    'dsn' => 'mongodb://192.168.2.187:27017'
    	],
        'user' => [
            'class' => '\yii\web\User',
            'identityClass' => '\app\modules\ctrl\models\AdminUsers', // identityClass must implement the IdentityInterface
            'enableAutoLogin' => true,
            'loginUrl' => ['ctrl/auth/login'],
        ],
        'authManager' => [
            'class' => '\yii\rbac\DbManager',
            'db' => 'spiderMysql',
            'cache' => 'spiderMongodb',

        ],
    ]
];