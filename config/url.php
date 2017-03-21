<?php
return [
	//debug
	'debug/<controller>/<action>' => 'debug/<controller>/<action>',

	//site
	'' => 'ctrl',
	'site' => 'site/index',
	'site/<action>' => 'site/<action>',
    //ctrl
    'ctrl/<controller>/<action>' => 'ctrl/<controller>/<action>',
    'ctrl/<controller>' => 'ctrl/<controller>/index',
    'ctrl' => 'ctrl/default/index',
    //RESTful API
    'res/<controller>/<action>' => 'res/<controller>/<action>',
];