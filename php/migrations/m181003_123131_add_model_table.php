<?php

use yii\db\Migration;

/**
 * Class m181003_123131_add_model_table
 */
class m181003_123131_add_model_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('model', [
            'id' => $this->primaryKey(),
            'name' => $this->string(),
            'value' => $this->string(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('model');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181003_123131_add_model_table cannot be reverted.\n";

        return false;
    }
    */
}
