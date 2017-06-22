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
        'API.NLP_WORD_CLASS_ANALYSE'  => '//192.168.2.187:8007/semodel/word_class_analyse',
        'API.NLP_NAME_ENTITY_RECOGNIZE'  => '//192.168.2.187:8007/semodel/name_entity_recognize',
        'API.NLP_SENTIMENT_ANALYSE'  => '//192.168.2.187:8007/sentiment/',
        'API.NLP_PARSE'  => '//192.168.2.187:8007/semodel/parse',
        'WORD_CLASS_TAG_SET' => [
            'c' => '连词',
            'd' => '副词',
            'v' => '动词',
            'a' => '形容词',
            'wp' => '标点符号',
            'n' => '名词',
            'j' => '名词',
            'r' => '名词',
            'ni' => '名词',
            'nl' => '名词',
            'ns' => '名词',
            'nt' => '名词',
            'nz' => '名词',
            'nd' => '名词',
            'nh' => '名词',
            'ws' => '名词',
        ],
        'NOUN_TAG' => ['n', 'ni', 'nl', 'ns', 'nt', 'nz', 'nd', 'nh', 'ws'],
        'NAME_ENTITY_RECOGNIZE_SET' => [
            'Nh' => '人名',
            'Ni' => '机构名',
            'Ns' => '地名'
        ],
    ],
];