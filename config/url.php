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
    'res/<controller>/<action>' => 'res/<controller>/<action>',
];