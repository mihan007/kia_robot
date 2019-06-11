<?php

use yii\db\Migration;

/**
 * Class m190528_133707_add_push_notified_column
 */
class m190528_133707_add_push_notified_column extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('task_run', 'push_notified', $this->boolean()->defaultValue(false));
        $this->createIndex('task_run_company_push_notified', 'task_run', ['company_id', 'push_notified']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190528_133707_add_push_notified_column cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190528_133707_add_push_notified_column cannot be reverted.\n";

        return false;
    }
    */
}
