<?php
return [
	//site
	'' => 'sl/schedule/index',
    //sl
    'sl/<controller>/<action>' => 'sl/<controller>/<action>',

    'sl/schedule/edit-schedule/<data_type:\w+>/<sche_id:\d+>' => 'sl/schedule/update-schedule',

    'sl/schedule/add-schedule/<data_type:\w+>' => 'sl/schedule/add-schedule',

    'sl/schedule/task-sche-crontab/<sche_id:\d+>' => 'sl/schedule/task-sche-crontab',

    'sl/schedule/task-item/<cron_id:\d+>' => 'sl/schedule/task-item',
];