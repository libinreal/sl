<div class="content">
    <div class="nav-path">
        <div class="np-prs">PRS</div>
        <div class="prs-left">
            <span class="prs-text fl">PRS System</span>
            <select name="" >
                <option value="">分析结果</option>
            </select>
            <div class="breadcrumb fl">
                <div class="breadcrumb-item">
                    词性分析
                </div>
                <!--span class="arrow-gt"> > </span>
                <div class="breadcrumb-item">
                    手机
                </div-->
            </div>
        </div>
        <div class="prs-right">
            <div class="icon icon-mail"></div>
            <div class="icon icon-star"></div>
        </div>
    </div>
    <div class="bb-left clearfix">
        <div class="basic-block">
            <span class="title-prefix-md">文字输入</span>
            <div style="clear:both;"></div>
            <div class="stmts-form">
                <form method="post" action="192.168.2.187:8007/semodel/word_class_analyse">
                    <textarea name="intext">

                    </textarea>
                    <input type="submit" value="提交">
                </form>
            </div>
        </div>
    </div>

    <div class="bb-right clearfix">
        <div class="bb-nav clearfix">
            <ul>
            <li><a class="active" href="/nlp/demo/word-class">词性分析</a></li>
            <li><a href="/nlp/demo/word-class">实体识别</a></li>
            <li><a href="/nlp/demo/word-class">依存文法</a></li>
            <li><a href="/nlp/demo/word-class">情感分析</a></li>
            </ul>
            <div style="clear:both"></div>
        </div>

        <div class="bb-rb clearfix">
            <div class="basic-block ei-panel">
            <span class="title-prefix-md">词性分析</span>

                <div class="ei-dl">

                </div>

            </div>

            <div class="basic-block ei-example">
            <span class="title-prefix-md">词性类别图示</span>

                <div class="ei-dr">

                </div>

            </div>
        </div>
    </div>


</div>