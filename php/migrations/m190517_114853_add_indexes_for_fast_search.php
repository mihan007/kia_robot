<?php

use yii\db\Migration;

/**
 * Class m190517_114853_add_indexes_for_fast_search
 */
class m190517_114853_add_indexes_for_fast_search extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createIndex('task_run_started_at_idx', 'task_run', 'started_at');
        $this->createIndex('task_run_amount_ordered_idx', 'task_run', 'amount_ordered');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190517_114853_add_indexes_for_fast_search cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190517_114853_add_indexes_for_fast_search cannot be reverted.\n";

        return false;
    }
    */
}
