<?php

use yii\db\Migration;

/**
 * Class m181009_122445_alter_table_task_run
 */
class m181009_122445_alter_table_task_run extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('task_run', 'created_at',  $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m181009_122445_alter_table_task_run cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181009_122445_alter_table_task_run cannot be reverted.\n";

        return false;
    }
    */
}
