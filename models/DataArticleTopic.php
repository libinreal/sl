<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "webspider.ws_data_article_topic".
 *
 * @property integer $id
 * @property integer $scheduler_id
 * @property string $article_code
 * @property string $site_name
 * @property string $url
 * @property string $keyword
 * @property string $title
 * @property string $author
 * @property string $area
 * @property string $createtime
 * @property string $praise_num
 * @property string $comment_num
 * @property string $crawl_time
 */
class DataArticleTopic extends \yii\db\ActiveRecord
{
    public $time_ranges;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%data_article_topic}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['scheduler_id', 'site_name', 'keyword', 'title', 'area', 'createtime'], 'required'],
            [['scheduler_id'], 'integer'],
            [['article_code'], 'string', 'max' => 200],
            [['site_name', 'area'], 'string', 'max' => 20],
            [['url', 'keyword', 'title', 'author', 'createtime', 'praise_num', 'comment_num', 'crawl_time'], 'string', 'max' => 255],
            [['article_code'], 'unique'],
            ['time_ranges', 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', '文章标题id'),
            'scheduler_id' => Yii::t('app', '任务id'),
            'article_code' => Yii::t('app', 'Article Code'),
            'site_name' => Yii::t('app', '来源站点名'),
            'url' => Yii::t('app', '文章来源url'),
            'keyword' => Yii::t('app', '关键字'),
            'title' => Yii::t('app', '文章名称'),
            'author' => Yii::t('app', '文章作者'),
            'area' => Yii::t('app', '地区'),
            'createtime' => Yii::t('app', '文章发布时间'),
            'praise_num' => Yii::t('app', '点赞数'),
            'comment_num' => Yii::t('app', '评论数'),
            'crawl_time' => Yii::t('app', '爬取时间'),
        ];
    }
}
