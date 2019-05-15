<?php

use yii\db\Migration;

/**
 * Class m190515_142150_add_indexes_to_storage
 */
class m190515_142150_add_indexes_to_storage extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createIndex("storage_created_at_idx", 'storage', 'created_at');
        $this->createIndex("storage_filters_idx", 'storage', [
            'model',
            'manufacture_code',
            'color_outside',
            'color_inside',
            'year'
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190515_142150_add_indexes_to_storage cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190515_142150_add_indexes_to_storage cannot be reverted.\n";

        return false;
    }
    */
}
