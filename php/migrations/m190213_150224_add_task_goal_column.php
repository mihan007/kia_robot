<?php

use yii\db\Migration;

/**
 * Class m190213_150224_add_task_goal_column
 */
class m190213_150224_add_task_goal_column extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('task', 'goal', $this->integer()->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('task', 'goal');
    }
}
