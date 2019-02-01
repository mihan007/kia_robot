<?php

use yii\db\Migration;

/**
 * Class m190201_110953_add_notified_column_to_task_run
 */
class m190201_110953_add_notified_column_to_task_run extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('task_run', 'notified', $this->boolean()->defaultValue(false));
        $this->update('task_run', ['notified' => 1]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('task_run', 'notified');
    }
}
