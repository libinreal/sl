<?php
use yii\helpers\Html;
use app\components\helpers\HtmlHelper;

/* @var $this \yii\web\View */
/* @var $content string */

//navigation config
$navArr = [
            'items' => 
            [
                       [
                        'name' => 'task',
                        'label' => 'Task',
                        'url' => '/sl/schedule/index'
                      ],
                      [
                        'name' => 'message',
                        'label' => 'Message',
                        'url' => '/sl/message/abnormal',
                      ],
                      [
                        'name' => 'report',
                        'label' => 'Report',
                        'url' => '/sl/report/crontab-data/product',
                        'items' => 
                        [
                            [
                                'name' => 'report-crontab-product',
                                'label' => 'Product Report',
                                'url' => '/sl/report/crontab-data/product'
                            ],
                            [
                                'name' => 'report-crontab-wechat',
                                'label' => 'WeChat Report',
                                'url' => '/sl/report/crontab-data/article'
                            ],
                        ]
                      ],
            ]
        ];

//$this->beginBlock("headJs");

$headJs = <<<EOT
    

//导航顶部菜单 START 
var NavTop = function() {
    this.navLi = $('#nav-top1 li').children('ul').hide().end();
    this.init();
};

NavTop.prototype = {
    
    init : function() {
        this.setMenu();
    },
    
    // Enables the slidedown menu, and adds support for IE6
    
    setMenu : function() {
    
    $.each(this.navLi, function() {
        if ( $(this).children('ul')[0] ) {
            $(this).append('<span class="hasChildren" />');
        }
    });
    
        this.navLi.hover(function() {
            // mouseover
            $(this).find('> ul').stop(true, true).slideDown();
        }, function() {
            // mouseout
            $(this).find('> ul').stop(true, true).hide();       
        });
        
    }
 
}

new NavTop();
//导航顶部菜单  END

EOT;
    $this->registerJs($headJs);
?>

<?php
//$this->endBlock();
//$this->registerJs($this->blocks['headJs'], \yii\web\View::POS_END);
?>
<header style="left: 0px;">
    <nav class="top-nav clearfix">
        <div id="nav-top1" class="nav-left">
            <?php echo HtmlHelper::renderNav1($navArr);?>
        </div>
        <div class="btn-keys">
            <div class="triangle-down">

            </div>
        </div>
        <div class="nav-right">
            <span class="nr-left">welcome</span>
            <span class="nr-right">3ti</span>
            <span class="icon-exit">&nbsp;</span>

        </div>

    </nav>
    <div class="keys-wrapper clearfix">
        <div class="kw-left">
            <span class="kwl-add">+ Add</span>
            <span class="kwl-find">Find Content</span>
        </div>
        <div class="kw-right">编辑快捷键</div>
    </div>
</header>