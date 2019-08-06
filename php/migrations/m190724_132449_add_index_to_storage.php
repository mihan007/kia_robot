<?php

use yii\db\Migration;

/**
 * Class m190724_132449_add_index_to_storage
 */
class m190724_132449_add_index_to_storage extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createIndex('storage_date', 'storage', 'created_at');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190724_132449_add_index_to_storage cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190724_132449_add_index_to_storage cannot be reverted.\n";

        return false;
    }
    */
}
