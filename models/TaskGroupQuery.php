<?php

namespace app\models;

/**
 * This is the ActiveQuery class for [[TaskGroup]].
 *
 * @see TaskGroup
 */
class TaskGroupQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * @inheritdoc
     * @return TaskGroup[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return TaskGroup|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
