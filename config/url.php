<?php
return [
	//debug
	'debug/<controller>/<action>' => 'debug/<controller>/<action>',

	//site
	'' => 'ctrl',
	'site' => 'site/index',
	'site/<action>' => 'site/<action>',
    //ctrl
    /*spider-data*/
    'ctrl/spider-data/data-search/<category>' => 'ctrl/spider-data/data-search',
    'ctrl/spider-data/semantics-analysis/<from>/<kw>' => 'ctrl/spider-data/semantics-analysis',

    'ctrl/<controller>/<action>' => 'ctrl/<controller>/<action>',
    //RESTful API
    [ 'class' => 'yii\rest\UrlRule',
      'controller' => ['res/article-comment', 'res/product-comment']
    ],
];