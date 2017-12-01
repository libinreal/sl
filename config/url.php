<?php
return [
	//site
	'' => 'nlp/demo/index',

    //ctrl
    /*'ctrl/spider-data/data-search/<category>' => 'ctrl/spider-data/data-search',
    'ctrl/spider-data/semantics-analysis/<from>/<kw>' => 'ctrl/spider-data/semantics-analysis',

    'ctrl/<controller>/<action>' => 'ctrl/<controller>/<action>',*/

    //nlp
    'nlp/<controller>/<action>' => 'nlp/<controller>/<action>',
    'nlp/dict/export-dict/<n:\w+>' => 'nlp/dict/export-dict',
    'nlp/dict/export-tag/<n:\w+>' => 'nlp/dict/export-tag',

    // sl/schedule
    'sl/<controller>/<action>' => 'sl/<controller>/<action>',

    'sl/schedule/edit-schedule/<data_type:\w+>/<sche_id:\d+>' => 'sl/schedule/update-schedule',

    'sl/schedule/add-schedule/<data_type:\w+>' => 'sl/schedule/add-schedule',

    'sl/schedule/task-sche-crontab/<sche_id:\d+>' => 'sl/schedule/task-sche-crontab',

    'sl/schedule/task-item/<cron_id:\d+>' => 'sl/schedule/task-item',

    // sl/message
    'sl/message/update-abnormal/<id:\d+>' => 'sl/message/update-abnormal',
    // sl/report
    'sl/report/crontab-data/<data_type:\w+>' => 'sl/report/crontab-data',
    'sl/report/crontab-data/<data_type:\w+>/<name:.+>/<start_time_s:\d[\d-]+>' => 'sl/report/crontab-data',
    //sl temp login
    'SL666888' => 'sl/temp-login/index',
    
    // 'http://<_m:(sl|nlp)>.3tichina.com' => '<_m>'
    //RESTful API
    /*[ 'class' => 'yii\rest\UrlRule',
      'controller' => ['res/article-comment', 'res/product-comment']
    ],*/
];