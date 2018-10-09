<?php

use yii\db\Migration;

/**
 * Class m181009_080852_add_task_run_table
 */
class m181009_080852_add_task_run_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('task_run', [
            'id' => $this->primaryKey(),
            'task_id' => $this->integer(),
            'model_name' => $this->string(),
            'model_value' => $this->string(),
            'manufacture_code_name' => $this->string(),
            'manufacture_code_value' => $this->string(),
            'color_inside_name' => $this->string(),
            'color_inside_value' => $this->string(),
            'color_outside_name' => $this->string(),
            'color_outside_value' => $this->string(),
            'amount' => $this->integer(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ]);

        $this->addForeignKey('task_run_task_fk', 'task_run', 'task_id', 'task', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('task_run');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181009_080852_add_task_run_table cannot be reverted.\n";

        return false;
    }
    */
}
