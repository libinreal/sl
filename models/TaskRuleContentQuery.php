<?php

namespace app\models;

/**
 * This is the ActiveQuery class for [[TaskRuleContent]].
 *
 * @see TaskRuleContent
 */
class TaskRuleContentQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * @inheritdoc
     * @return TaskRuleContent[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return TaskRuleContent|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
