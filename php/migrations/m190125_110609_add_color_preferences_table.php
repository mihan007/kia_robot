<?php

use yii\db\Migration;

/**
 * Class m190125_110609_add_color_preferences_table
 */
class m190125_110609_add_color_preferences_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('color_preferences', [
            'id' => $this->primaryKey(),
            'model_name' => $this->string(),
            'model_value' => $this->string(),
            'colors' => $this->string(255)
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('color_preferences');
    }
}
