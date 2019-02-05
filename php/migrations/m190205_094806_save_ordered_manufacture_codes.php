<?php

use yii\db\Migration;

/**
 * Class m190205_094806_save_ordered_manufacture_codes
 */
class m190205_094806_save_ordered_manufacture_codes extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('task_run', 'ordered_manufacture_codes', 'text');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('task_run', 'ordered_manufacture_codes');
    }
}
