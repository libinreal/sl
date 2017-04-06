<aside class="main-sidebar">

    <section class="sidebar">

        <!-- Sidebar user panel -->
        <div class="user-panel">
            <div class="pull-left image">
                <img src="<?= $directoryAsset ?>/img/user2-160x160.jpg" class="img-circle" alt="User Image"/>
            </div>
            <div class="pull-left info">
                <p><?= $this->context->adminUser['name'] ?></p>

                <a href="#"><i class="fa fa-circle text-success"></i> <?= $this->context->adminUser['role_name'] ?></a>
            </div>
        </div>

        <!-- search form -->
        <form action="#" method="get" class="sidebar-form">
            <div class="input-group">
                <input type="text" name="q" class="form-control" placeholder="Search..."/>
              <span class="input-group-btn">
                <button type='submit' name='search' id='search-btn' class="btn btn-flat"><i class="fa fa-search"></i>
                </button>
              </span>
            </div>
        </form>
        <!-- /.search form -->

        <?= dmstr\widgets\Menu::widget(
            [
                'options' => ['class' => 'sidebar-menu'],
                'items' => [
                    ['label' => '菜单栏', 'options' => ['class' => 'header']],
                    [
                        'label' => '权限管理',
                        'icon' => 'fa fa-group',
                        'url' => '#',
                        'items' => [
                            ['label' => '用户列表', 'icon' => 'fa fa-users', 'url' => ['/ctrl/auth/users'],],
                            ['label' => '权限分组', 'icon' => 'fa fa-user-circle-o', 'url' => ['/ctrl/auth/roles'],],
                            ['label' => '菜单设置', 'icon' => 'fa fa-sitemap', 'url' => ['/ctrl/auth/menu'],],
                        ],
                    ],
                    [
                        'label' => '任务管理',
                        'icon' => 'fa fa-tasks',
                        'url' => '#',
                        'items' => [
                            ['label' => '任务规则', 'icon' => 'fa fa-list', 'url' => ['/ctrl/spider-task/task-rules'],],
                            ['label' => '任务列表', 'icon' => 'fa fa-clock-o', 'url' => ['/ctrl/spider-task/task-schedules'],],
                            ['label' => '任务运行统计', 'icon' => 'fa fa-list-alt', 'url' => ['/ctrl/spider-task/schedule-state'],],
                        ],
                    ],
                    [
                        'label' => '数据处理',
                        'icon' => 'fa fa-calculator',
                        'url' => '#',
                        'items' => [
                            ['label' => '内容检索', 'icon' => 'fa fa-search', 'url' => ['/ctrl/spider-data/data-search'],],
                            ['label' => '自然语义分析', 'icon' => 'fa fa-language', 'url' => ['/ctrl/spider-data/semantics-analysis'],],
                            ['label' => '数据概览', 'icon' => 'fa fa-line-chart', 'url' => ['/ctrl/spider-data/data-overview'],],
                            ['label' => '热搜指数', 'icon' => 'fa fa-dashboard', 'url' => ['/ctrl/spider-data/data-dashboard'],],
                        ],
                    ],
                ],
            ]
        ) ?>

    </section>

</aside>
