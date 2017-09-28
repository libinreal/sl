<?php
use yii\helpers\Html;
use app\components\helpers\HtmlHelper;

/* @var $this \yii\web\View */
/* @var $content string */

app\assets\SLAdminAsset::addScript($this, '@web/sl/lib/responsive-menu/responsive-menu.js');
app\assets\SLAdminAsset::addCss($this, '@web/sl/lib/responsive-menu/responsive-menu.css');

$menuFontCss = <<<EOT
    .rm-nav li a, .rm-menu-item a{
        font-size:14px;
        font-weight:bold;
    }

    .rm-nav ul, .rm-menu{
        background-color: inherit;
    }

    
EOT;
$this->registerCss($menuFontCss);

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
                        /*'items' => 
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
                        ]*/
                      ],
            ]
        ];

//$this->beginBlock("headJs");

$headJs = <<<EOT
//导航顶部菜单 START 

var menu = $('.rm-nav').rMenu({
        minWidth: '769px',
});

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
        <div class="rm-container">
            <div class="nav-left rm-nav rm-nojs rm-lighten">
                <?php echo HtmlHelper::renderResponsiveMenu($navArr);?>
            </div>
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