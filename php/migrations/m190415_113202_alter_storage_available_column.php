<?php

use yii\db\Migration;

/**
 * Class m190415_113202_alter_storage_available_column
 */
class m190415_113202_alter_storage_available_column extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('storage', 'available', $this->string(20));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190415_113202_alter_storage_available_column cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190415_113202_alter_storage_available_column cannot be reverted.\n";

        return false;
    }
    */
}
