<?php

use yii\db\Migration;

/**
 * Class m190522_130600_add_notified_to_storage
 */
class m190522_130600_add_notified_to_storage extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('storage', 'notified', $this->boolean()->defaultValue(false));
        $this->createIndex('storage_notified_idx', 'storage', 'notified');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190522_130600_add_notified_to_storage cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190522_130600_add_notified_to_storage cannot be reverted.\n";

        return false;
    }
    */
}
