<?php
return [
        'translations' => [
            'app*' => [
                'class' => 'yii\i18n\PhpMessageSource',
                //'basePath' => '@app/messages',
                //'sourceLanguage' => 'en-US',
                'fileMap' => [
                    'app' => 'app.php',
                    'app/ctrl/auth' => 'app_ctrl_auth.php',
                    'app/ctrl/admin_menus' => 'app_ctrl_admin_menus.php',
                    'app/ctrl/admin_users' => 'app_ctrl_admin_users.php',
                    'app/ctrl/auth_assignment' => 'app_ctrl_auth_assignment.php',
                    'app/ctrl/auth_item' => 'app_ctrl_auth_item.php',
                    'app/ctrl/auth_item_child' => 'app_ctrl_auth_item_child.php',
                    'app/ctrl/auth_rule' => 'app_ctrl_auth_rule.php',
                    'app/ctrl/task' => 'app_ctrl_task.php',
                    'app/ctrl/task_rule' => 'app_ctrl_task_rule.php',
                    'app/ctrl/task_rule_content' => 'app_ctrl_task_rule_content.php',
                    'app/ctrl/task_rule_url' => 'app_ctrl_task_rule_url.php',
                    'app/ctrl/task_group' => 'app_ctrl_task_group.php',
                    'app/ctrl/task_scheduler' => 'app_ctrl_task_scheduler.php',
                    'app/ctrl/task_scheduler_state' => 'app_ctrl_task_scheduler_state.php',

                    'app/ctrl/spider_data' => 'app_ctrl_spider_data.php',
                    'app/ctrl/spider_data_article_topic' => 'app_ctrl_spider_data_article_topic.php',
                    'app/ctrl/spider_data_comment_article' => 'app_ctrl_spider_data_comment_article.php',
                    'app/ctrl/spider_data_product_comment_topic' => 'app_ctrl_spider_data_product_comment_topic.php',
                    'app/ctrl/spider_data_comment_product' => 'app_ctrl_spider_data_comment_product.php',
                ],
            ],
        ],
];