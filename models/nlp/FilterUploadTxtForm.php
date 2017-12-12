<?php
namespace app\models\nlp;
use yii\base\Model;
use yii\helpers\Json;

class FilterUploadTxtForm extends Model {
   public $txt;//UploadedFile instance
   const SAVE_NAME = '../uploads/nlp/engine/config/filter.txt';
   public function rules() {
      return [
         [['txt'], 'myTxtRule'],
      ];
   }
   public function upload() {
      if ($this->validate()) {
         if( $this->txt->saveAs(self::SAVE_NAME) )
         {
            return true;
         }
         else
         {
            $this->addError($attribute, "Txt upload error:".$this->txt->error);
            return false;
         }
      } else {
         return false;
      }
   }

   /**
     *  自定义验证
     */
   public function myTxtRule($attribute, $params)
   {
      if ($this->txt) 
      {
         $allowExtension = ['txt'];

         if( !in_array ($this->txt->extension, ['txt']) )
            $this->addError($attribute, "Txt validate failed, only allow extentions:" . Json::encode($allowExtension));
         
         if ($this->txt->getHasError())
            $this->addError($attribute, "Txt validate failed, error:".$this->txt->error);
      }
      else
      {
         $this->addError($attribute, 'Form validate failed, input name error');
      }
   }



}
