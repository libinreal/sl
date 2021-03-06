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
        'mongodb' => [
            'class' => '\yii\mongodb\Connection',
            'dsn' => 'mongodb://192.168.2.187:27017'
        ],
        'cache' => [
            'class' => 'yii\caching\Cache'
        ],
    ],
    'params' => [
        'API.NLP_WORD_CLASS_ANALYSE'  => '//nlp.3tichina.com/api/semodel/word_class_analyse',//词性分析
        'API.NLP_NAME_ENTITY_RECOGNIZE'  => '//nlp.3tichina.com/api/semodel/name_entity_recognize',//实体识别
        'API.NLP_SENTIMENT_ANALYSE'  => '//nlp.3tichina.com/api/sentiment/',//情感分析
        'API.NLP_PARSE'  => '//nlp.3tichina.com/api/semodel/dependency_parser',//依存关系
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