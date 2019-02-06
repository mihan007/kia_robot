<?php

use yii\db\Migration;

/**
 * Class m190206_112710_add_company_and_user_to_task_and_task_run_and_colors
 */
class m190206_112710_add_company_and_user_to_task_and_task_run_and_colors extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('task', 'company_id', $this->integer());
        $this->addColumn('task', 'user_id', $this->integer());

        $this->addColumn('task_run', 'company_id', $this->integer());
        $this->addColumn('task_run', 'user_id', $this->integer());

        $this->addColumn('color_preferences', 'company_id', $this->integer());

        $this->update('task', ['company_id' => 1, 'user_id' => 1]);
        $this->update('task_run', ['company_id' => 1, 'user_id' => 1]);
        $this->update('color_preferences', ['company_id' => 1]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190206_112710_add_company_and_user_to_task_and_task_run_and_colors cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190206_112710_add_company_and_user_to_task_and_task_run_and_colors cannot be reverted.\n";

        return false;
    }
    */
}
