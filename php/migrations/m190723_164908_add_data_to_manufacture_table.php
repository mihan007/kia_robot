<?php

use yii\db\Migration;

/**
 * Class m190723_164908_add_data_to_manufacture_table
 */
class m190723_164908_add_data_to_manufacture_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $codes = [
            'H0S4K4617DD181', 'H0S4K4617DD184', 'H0S4D2617DD176', 'H0S4D261FDD178',
            'H0S4K4617DD176', 'H0S4K461FDD178', 'H0S4K461FDD181', 'H0S4D2617DD177',
            'H0S4D261FDD179', 'H0S4D261FGG130', 'H0S4D261FGG128'
        ];

        foreach ($codes as $code) {
            Yii::$app->db->createCommand()->insert('manufacture_code', [
                'name' => $code,
                'value' => $code,
                'model_id' => 11,
            ])->execute();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190723_164908_add_data_to_manufacture_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190723_164908_add_data_to_manufacture_table cannot be reverted.\n";

        return false;
    }
    */
}
