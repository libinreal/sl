<?php

namespace app\models;

/**
 * This is the ActiveQuery class for [[TaskRuleUrl]].
 *
 * @see TaskRuleUrl
 */
class TaskRuleUrlQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * @inheritdoc
     * @return TaskRuleUrl[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return TaskRuleUrl|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
