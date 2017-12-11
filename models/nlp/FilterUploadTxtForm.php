<?php
namespace app\models\nlp;
use yii\base\Model;
use yii\helpers\Json;

class FilterUploadTxtForm extends Model {
   public $txt;//UploadedFile instance
   public $saveName;
   public function rules() {
      return [
         [['txt'], 'myTxtRule'],
      ];
   }
   public function upload() {
      if ($this->validate()) {
         $this->saveName = '../uploads/nlp/dict/txt/' . $this->txt->baseName . '.' . $this->txt->extension;
         if( $this->txt->saveAs($this->saveName) )
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

         if( !in_array ($this->excel->extension, ['txt']) )
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
