<?php

use yii\db\Migration;

/**
 * Class m181017_104418_add_amount_ordered_to_task_run
 */
class m181017_104418_add_amount_ordered_to_task_run extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('task_run', 'amount_ordered', $this->integer());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('task_run', 'amount_ordered');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181017_104418_add_amount_ordered_to_task_run cannot be reverted.\n";

        return false;
    }
    */
}
