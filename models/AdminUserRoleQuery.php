<?php

namespace app\models;

/**
 * This is the ActiveQuery class for [[AdminUserRole]].
 *
 * @see AdminUserRole
 */
class AdminUserRoleQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * @inheritdoc
     * @return AdminUserRole[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return AdminUserRole|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
