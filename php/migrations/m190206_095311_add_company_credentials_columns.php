<?php

use yii\db\Migration;

/**
 * Class m190206_095311_add_company_credentials_columns
 */
class m190206_095311_add_company_credentials_columns extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('company', 'kia_login', $this->string()->notNull());
        $this->addColumn('company', 'kia_password', $this->string()->notNull());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('company', 'kia_login');
        $this->dropColumn('company', 'kia_password');
    }
}
