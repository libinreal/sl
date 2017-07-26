<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "webspider.ws_task_group".
 *
 * @property integer $id
 * @property integer $scheduler_id
 * @property string $name
 * @property integer $parent_id
 */
class TaskGroup extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%task_group}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['parent_id'], 'integer'],
            [['name'], 'string', 'max' => 200],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Id',
            'name' => Yii::t('app/ctrl/task_group', 'name'),
            'parent_id' => Yii::t('app/ctrl/task_group', 'parent id'),
        ];
    }

    /**
     * @inheritdoc
     * @return TaskGroupQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new TaskGroupQuery(get_called_class());
    }
}
