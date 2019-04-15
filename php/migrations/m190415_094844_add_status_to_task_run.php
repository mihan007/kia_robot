<?php

use yii\db\Migration;

/**
 * Class m190415_094844_add_status_to_task_run
 */
class m190415_094844_add_status_to_task_run extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('task_run', 'status', $this->integer()->defaultValue(0));
        $this->update('task_run', ['status' => \app\models\TaskRun::STATUS_SUCCESS], ['is not', 'amount_ordered', null]);
        $this->update('task_run', ['status' => \app\models\TaskRun::STATUS_ERROR], ['is', 'amount_ordered', null]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190415_094844_add_status_to_task_run cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190415_094844_add_status_to_task_run cannot be reverted.\n";

        return false;
    }
    */
}
