Social Listening任务监控 + Natural Language Processing前台
============================

Base on Yii 2.0.11.2

DIRECTORY STRUCTURE
-------------------

      assets/             contains assets definition
      commands/           contains console commands (controllers)
      config/             contains application configurations
      controllers/        contains Web controller classes
      mail/               contains view files for e-mails
      models/             contains model classes
        sl/               SL 模型类
        nlp/              NLP 模型类
      modules
        ctrl/             管理后台测试代码
        nlp/              NLP前台演示控制器和视图
        res/              Restful规范的测试代码
        sl/               SL网页任务监控和命令行任务下发的控制器和视图代码
      views/              contains view files for the Web application
      web/                contains the entry script and Web resources
      nlpCmd              基于jieba分词的自定义词库、标签创建工具
      slCmd               分布式数据抓取任务下发和进度更新工具


REQUIREMENTS
------------
