<?php
return [
	//site
	'' => 'sl/demo/index',
    //sl
    'sl/<controller>/<action>' => 'sl/<controller>/<action>',

    'sl/demo/edit-schedule/<sche_id:\d+>' => 'sl/demo/update-schedule',

    'sl/demo/task-sche-crontab/<sche_id:\d+>' => 'sl/demo/task-sche-crontab',

    'sl/demo/task-item/<cron_id:\d+>' => 'sl/demo/task-item',

];