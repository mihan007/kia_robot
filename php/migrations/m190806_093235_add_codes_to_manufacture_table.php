<?php

use yii\db\Migration;

/**
 * Class m190806_093235_add_codes_to_manufacture_table
 */
class m190806_093235_add_codes_to_manufacture_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $codes = [
            'H0S4D261FDD178',
            'H0S4D2617DD177',
            'H0S4D261FDD179',
            'H0S4D2617DD190',
            'H0S4D261FDD191',
            'H0S4D261FGG128',
            'H0S4D261FGG130',
            'H0S6D2617DD186',
            'H0S6D261FDD188',
            'H0S6D2617DD187',
            'H0S6D261FDD189',
            'H0S6D261FGG129',
            'H0S6D261FGG131'
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
        echo "m190806_093235_add_codes_to_manufacture_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190806_093235_add_codes_to_manufacture_table cannot be reverted.\n";

        return false;
    }
    */
}
