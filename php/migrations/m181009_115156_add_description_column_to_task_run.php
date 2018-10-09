<?php

use yii\db\Migration;

/**
 * Class m181009_115156_add_description_column_to_task_run
 */
class m181009_115156_add_description_column_to_task_run extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('task_run', 'description', $this->text());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('task_run', 'description');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181009_115156_add_description_column_to_task_run cannot be reverted.\n";

        return false;
    }
    */
}
