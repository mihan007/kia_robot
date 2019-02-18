<?php

use yii\db\Migration;

/**
 * Class m190218_080610_add_banned_at_for_company
 */
class m190218_080610_add_banned_at_for_company extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('company', 'banned_at', $this->integer());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('company', 'banned_at');
    }
}