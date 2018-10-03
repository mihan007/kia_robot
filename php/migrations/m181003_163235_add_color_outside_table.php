<?php

use yii\db\Migration;

/**
 * Class m181003_163235_add_color_outside_table
 */
class m181003_163235_add_color_outside_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('color_outside', [
            'id' => $this->primaryKey(),
            'name' => $this->string(),
            'value' => $this->string(),
            'model_id' => $this->integer()
        ]);

        $this->addForeignKey('fk_color_outside_model', 'color_outside', 'model_id', 'model', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('color_outside');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181003_163235_add_color_outside_table cannot be reverted.\n";

        return false;
    }
    */
}
