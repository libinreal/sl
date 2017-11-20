<?php
namespace app\models\nlp;
use yii\base\Model;

class DictUploadExcelForm extends Model {
   public $excel;
   public $saveName;
   public function rules() {
      return [
         [['excel'], 'file', 'skipOnEmpty' => false, 'extensions' => 'xlsx'],
      ];
   }
   public function upload() {
      if ($this->validate()) {
         $this->saveName = '../uploads/nlp/dict/excel/' . $this->excel->baseName . '.' . $this->excel->extension;
         $this->excel->saveAs($this->saveName);
         return true;
      } else {
         return false;
      }
   }
}
