<?php

use yii\db\Migration;

/**
 * Class m190225_131707_add_client_name_to_task
 */
class m190225_131707_add_client_name_to_task extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('task', 'client_name', $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('task', 'client_name');
    }
}
