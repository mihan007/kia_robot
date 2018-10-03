<?php

use yii\db\Migration;

/**
 * Class m181003_130022_add_manufacture_code_table
 */
class m181003_130022_add_manufacture_code_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('manufacture_code', [
            'id' => $this->primaryKey(),
            'name' => $this->string(),
            'value' => $this->string(),
            'model_id' => $this->integer()
        ]);

        $this->addForeignKey('fk_model', 'manufacture_code', 'model_id', 'model', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('manufacture_code');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181003_130022_add_manufacture_code_table cannot be reverted.\n";

        return false;
    }
    */
}
