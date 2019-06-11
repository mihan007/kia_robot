<?php

use yii\db\Migration;

/**
 * Class m190528_134515_add_push_notification_tag_column_to_company
 */
class m190528_134515_add_push_notification_tag_column_to_company extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('company', 'push_notification_tag', $this->integer());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190528_134515_add_push_notification_tag_column_to_company cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190528_134515_add_push_notification_tag_column_to_company cannot be reverted.\n";

        return false;
    }
    */
}
