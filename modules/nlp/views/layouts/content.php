<div class="content">
    <div class="nav-path">
        <div class="np-prs">NLP</div>
        <div class="prs-left">
            <span class="prs-text fl">NLP System</span>
            <select name="" >
                <option value="">分析结果</option>
            </select>
            <div class="breadcrumb fl">
                <div class="breadcrumb-item">
                    <?php echo $this->params['breadcrumbs'][count($this->params['breadcrumbs'])-1]; ?>
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
            <!--span class="title-prefix-md">文字输入</span>
            <div style="clear:both;"></div-->
            <div class="stmts-form">
                <form id="stmts-form" method="post" action=<?php
                if($this->context->action->id == 'word-class'):
                    echo Yii::$app->getModule('nlp')->params['API.NLP_WORD_CLASS_ANALYSE'];
                elseif($this->context->action->id == 'name-entity-recognize'):
                    echo Yii::$app->getModule('nlp')->params['API.NLP_NAME_ENTITY_RECOGNIZE'];
                elseif($this->context->action->id == 'sentiment-analyse'):
                    echo Yii::$app->getModule('nlp')->params['API.NLP_SENTIMENT_ANALYSE'];
                elseif($this->context->action->id == 'depend-parse'):
                    echo Yii::$app->getModule('nlp')->params['API.NLP_PARSE'];
                endif;
                ?>>
                <span class="title-prefix-md">文字输入</span>
                <div style="clear:both;"></div>
                    <textarea name="intext" class="stmts-ta" rows="17" placeholder="请输入文字..."></textarea>
                <div id="stmts-kw" style="display: none;">
                    <span class="title-prefix-md">输入关键字</span>
                    <div style="clear:both;"></div>
                    <input name="kw" class="stmts-tx" placeholder="请输入关键字...">
                </div>
                <input type="submit" class="btn-sub" value="提交">
                </form>
            </div>
        </div>
    </div>

    <div class="bb-right clearfix">
        <div class="bb-nav clearfix">
            <ul>
            <li><a href="javascript:;">词性分析</a></li>
            <li><a href="javascript:;">实体识别</a></li>
            <li><a href="javascript:;">依存文法</a></li>
            <li><a href="javascript:;">情感分析</a></li>
            </ul>
            <div style="clear:both"></div>
        </div>

        <div class="bb-rb clearfix">
            <?= $content ?>
        </div>
    </div>


</div>