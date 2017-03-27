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
        return 'webspider.ws_task_group';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['scheduler_id', 'parent_id'], 'integer'],
            [['name'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', '任务分组id'),
            'scheduler_id' => Yii::t('app', '任务主键'),
            'name' => Yii::t('app', '任务分组名'),
            'parent_id' => Yii::t('app', '任务分组父类id'),
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
