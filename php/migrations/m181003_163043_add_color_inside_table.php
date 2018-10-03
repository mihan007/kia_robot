<?php

use yii\db\Migration;

/**
 * Class m181003_163043_add_color_inside_table
 */
class m181003_163043_add_color_inside_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('color_inside', [
            'id' => $this->primaryKey(),
            'name' => $this->string(),
            'value' => $this->string(),
            'model_id' => $this->integer()
        ]);

        $this->addForeignKey('fk_color_model', 'color_inside', 'model_id', 'model', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('color_inside');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181003_163043_add_color_inside_table cannot be reverted.\n";

        return false;
    }
    */
}
