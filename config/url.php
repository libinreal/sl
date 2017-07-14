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

    //sl
    'sl/<controller>/<action>' => 'sl/<controller>/<action>',
    'sl/demo/task-item/<sche_id:\d+>' => 'sl/demo/task-item',
    'sl/demo/edit-schedule/<sche_id:\d+>' => 'sl/demo/update-schedule',
    // 'http://<_m:(sl|nlp)>.3tichina.com' => '<_m>'
    //RESTful API
    /*[ 'class' => 'yii\rest\UrlRule',
      'controller' => ['res/article-comment', 'res/product-comment']
    ],*/
];