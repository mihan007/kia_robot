<?php

use yii\db\Migration;

/**
 * Class m190415_082322_add_task_start_and_finished_cols
 */
class m190415_082322_add_task_start_and_finished_cols extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('task_run', 'started_at',  $this->dateTime());
        $this->addColumn('task_run', 'finished_at',  $this->dateTime());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190415_082322_add_task_start_and_finished_cols cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190415_082322_add_task_start_and_finished_cols cannot be reverted.\n";

        return false;
    }
    */
}
