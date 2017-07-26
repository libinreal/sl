<?php

namespace app\models;

/**
 * This is the ActiveQuery class for [[AdminMenus]].
 *
 * @see AdminMenus
 */
class AdminMenusQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * @inheritdoc
     * @return AdminMenus[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return AdminMenus|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
