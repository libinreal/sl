<?php
namespace app\modules\ctrl\widget;

use yii\helpers\ArrayHelper;
use yii\base\Widget;
use yii\helpers\Html;
use yii\helpers\Url;
use app\models\AdminUserRole;
use app\models\AdminPrivileges;
use app\models\AdminActions;
use app\models\AdminUsers;

class HelloWidget extends Widget
{

    public function init()
    {
        parent::init();
    }

    public function run()
    {
    	$nav = Nav::find()
        ->where(['status' => 1])
        ->orderBy('sort ASC')
        ->all();
        foreach($nav as $_v){
            $navs[] = $_v->id.'|'.$_v->nav_cn.'|'.$_v->nav_en;
        }
        // 渲染视图
         return $this->render('@app/views/site/_nav', [
             'nav'=>$navs,
         ]);

        return Html::encode($this->message);
    }
}