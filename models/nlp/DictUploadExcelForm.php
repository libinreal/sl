<?php
namespace app\models\nlp;
use yii\base\Model;
use yii\helpers\Json;

class DictUploadExcelForm extends Model {
   public $excel;//UploadedFile instance
   public $saveName;
   public function rules() {
      return [
         [['excel'], 'myExcelRule'],
      ];
   }
   public function upload() {
      if ($this->validate()) {
         $this->saveName = '../uploads/nlp/dict/excel/' . $this->excel->baseName . '.' . $this->excel->extension;
         if( $this->excel->saveAs($this->saveName) )
         {
            return true;
         }
         else
         {
            $this->addError($attribute, "Excel upload error:".$this->excel->error);
            return false;
         }
      } else {
         return false;
      }
   }

   /**
     *  自定义验证
     */
   public function myExcelRule($attribute, $params)
   {
      if ($this->excel) 
      {
         $allowExtension = ['xlsx'];

         if( !in_array ($this->excel->extension, ['xlsx']) )
            $this->addError($attribute, "Excel validate failed, only allow extentions:" . Json::encode($allowExtension));
         
         if ($this->excel->getHasError())
            $this->addError($attribute, "Excel validate failed, error:".$this->excel->error);
      }
      else
      {
         $this->addError($attribute, 'Form validate failed, input name error');
      }
   }



}
