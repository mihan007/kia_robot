<?php

use yii\db\Migration;

/**
 * Class m190227_144449_add_storage_table
 */
class m190227_144449_add_storage_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('storage_session', [
            'id' => $this->primaryKey(),
            'started_at' => $this->dateTime(),
            'finished_at' => $this->dateTime()
        ]);

        $this->createTable('storage', [
            'id' => $this->primaryKey(),
            'storage_session_id' => $this->integer(),
            'model' => $this->string(),
            'manufacture_code' => $this->string(),
            'description' => $this->string(),
            'color_outside' => $this->string(),
            'color_inside' => $this->string(),
            'year' => $this->integer(),
            'storage_code' => $this->string(),
            'available' => $this->integer(),
            'reserved' => $this->integer(),
            'page' => $this->integer(),
            'created_at' => $this->dateTime(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('storage');
    }
}
