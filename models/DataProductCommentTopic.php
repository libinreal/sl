<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "webspider.ws_data_product_comment_topic".
 *
 * @property integer $id
 * @property integer $scheduler_id
 * @property string $keyword
 * @property string $product_name
 * @property string $product_brand
 * @property string $product_code
 * @property string $product_title
 * @property string $product_cate1
 * @property string $product_cate2
 * @property string $product_cate3
 * @property string $product_attr1
 * @property string $product_attr2
 * @property string $product_attr3
 * @property string $product_addr
 * @property string $comment_url
 * @property string $comment_count
 * @property string $good_count
 * @property string $general_count
 * @property string $poor_count
 * @property string $record_time
 * @property string $site_name
 * @property string $crawl_time
 */
class DataProductCommentTopic extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%data_product_comment_topic}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['scheduler_id'], 'integer'],
            [['record_time', 'crawl_time'], 'safe'],
            [
                ['keyword', 'product_name', 'product_brand', 'product_cate1', 'product_cate2', 'product_cate3', 'comment_count',
                'product_code', 'product_title', 'product_attr1', 'product_attr2', 'product_attr3',
                'good_count', 'general_count', 'poor_count', 'site_name', 'product_addr', 'comment_url'],
                'string'
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', '商品评论id'),
            'scheduler_id' => Yii::t('app', '任务id'),
            'keyword' => Yii::t('app', '关键字(产品名)'),
            'product_name' => Yii::t('app', '产品名'),
            'product_brand' => Yii::t('app', '产品品牌'),
            'product_code' => Yii::t('app', '产品编码'),
            'product_title' => Yii::t('app', '产品标题'),
            'product_cate1' => Yii::t('app', '一级分类名'),
            'product_cate2' => Yii::t('app', '二级分类名'),
            'product_cate3' => Yii::t('app', '三级分类名'),
            'product_attr1' => Yii::t('app', '产品属性1'),
            'product_attr2' => Yii::t('app', '产品属性2'),
            'product_attr3' => Yii::t('app', '产品属性3'),
            'product_addr' => Yii::t('app', '产品地址'),
            'comment_url' => Yii::t('app', '评论来源url'),
            'comment_count' => Yii::t('app', '商家评价数'),
            'good_count' => Yii::t('app', '好评数'),
            'general_count' => Yii::t('app', '中评数'),
            'poor_count' => Yii::t('app', '差评数'),
            'record_time' => Yii::t('app', '当前插入数据库时间'),
            'site_name' => Yii::t('app', '站点名称'),
            'crawl_time' => Yii::t('app', '爬行时间'),
        ];
    }
}
