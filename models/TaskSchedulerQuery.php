<?php

namespace app\models;

/**
 * This is the ActiveQuery class for [[TaskScheduler]].
 *
 * @see TaskScheduler
 */
class TaskSchedulerQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * @inheritdoc
     * @return TaskScheduler[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return TaskScheduler|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
