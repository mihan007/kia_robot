<?php

use app\models\Task;
use yii\db\Migration;

/**
 * Class m190417_140953_add_worker_column
 */
class m190417_140953_add_worker_column extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('task', 'worker', $this->string(12));
        $this->createIndex('task_worker_idx', 'task', 'worker');
        $tasks = Task::find()->where(['deleted_at' => 0])->all();
        foreach ($tasks as $task) {
            $task->save();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190417_140953_add_worker_column cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190417_140953_add_worker_column cannot be reverted.\n";

        return false;
    }
    */
}
