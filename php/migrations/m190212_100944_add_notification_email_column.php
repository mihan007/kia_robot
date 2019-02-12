<?php

use yii\db\Migration;

/**
 * Class m190212_100944_add_notification_email_column
 */
class m190212_100944_add_notification_email_column extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('company', 'notification_email', $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('company', 'notification_email');
    }
}
