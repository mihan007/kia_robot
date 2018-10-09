<?php

use yii\db\Migration;

/**
 * Class m181009_123451_alter_task_run_screenshot
 */
class m181009_123451_alter_task_run_screenshot extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('task_run_screenshot', 'created_at',  $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m181009_123451_alter_task_run_screenshot cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181009_123451_alter_task_run_screenshot cannot be reverted.\n";

        return false;
    }
    */
}
