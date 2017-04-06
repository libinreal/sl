<?php

namespace app\models;

/**
 * This is the ActiveQuery class for [[TaskSchedulerState]].
 *
 * @see TaskSchedulerState
 */
class TaskSchedulerStateQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * @inheritdoc
     * @return TaskSchedulerState[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return TaskSchedulerState|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
