<?php

namespace app\models;

/**
 * This is the ActiveQuery class for [[TaskRule]].
 *
 * @see TaskRule
 */
class TaskRuleQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * @inheritdoc
     * @return TaskRule[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return TaskRule|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
