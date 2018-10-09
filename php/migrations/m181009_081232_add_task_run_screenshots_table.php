<?php

use yii\db\Migration;

/**
 * Class m181009_081232_add_task_run_screenshots_table
 */
class m181009_081232_add_task_run_screenshots_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('task_run_screenshot', [
            'id' => $this->primaryKey(),
            'task_run_id' => $this->integer(),
            'name' => $this->string(),
            'filepath' => $this->string(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ]);

        $this->addForeignKey('task_run_screenshot_task_run_fk', 'task_run_screenshot', 'task_run_id', 'task_run', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m181009_081232_add_task_run_screenshots_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181009_081232_add_task_run_screenshots_table cannot be reverted.\n";

        return false;
    }
    */
}
