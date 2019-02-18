<?php

use yii\db\Migration;

/**
 * Class m190218_085925_add_notified_about_ban_column
 */
class m190218_085925_add_notified_about_ban_column extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('company', 'notified_about_ban', $this->boolean()->defaultValue(false));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('company', 'notified_about_ban');
    }
}
