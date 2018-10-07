<?php

use yii\db\Migration;

/**
 * Handles the creation of table `task`.
 */
class m181007_160724_create_task_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('task', [
            'id' => $this->primaryKey(),
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
            'deleted_at' => $this->integer()
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('task');
    }
}
