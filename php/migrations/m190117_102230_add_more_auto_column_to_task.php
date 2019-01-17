<?php

use yii\db\Migration;

/**
 * Class m190117_102230_add_more_auto_column_to_task
 */
class m190117_102230_add_more_auto_column_to_task extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('task', 'more_auto', $this->boolean()->defaultValue(false));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('task', 'more_auto');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190117_102230_add_more_auto_column_to_task cannot be reverted.\n";

        return false;
    }
    */
}
