<?php
namespace app\components;

use Yii;

/**
 * Extended yii\web\User
 *
 * This allows us to do "Yii::$app->user->something" by adding getters
 * like "public function getSomething()"
 *
 * So we can use variables and functions directly in `Yii::$app->user`
 */
class User extends \yii\web\User
{
    public function getUsername()
    {
        return \Yii::$app->user->identity->username;
    }

    public function getEmail()
    {
        return \Yii::$app->user->identity->email;
    }

    public function getCompanyId()
    {
        return \Yii::$app->user->identity->company_id;
    }

    public function getIsAdmin()
    {
        return in_array('admin', \Yii::$app->user->identity->getRoles());
    }

    public function getIsLeadManager()
    {
        return in_array('leadManager', \Yii::$app->user->identity->getRoles());
    }

    public function getIsManager()
    {
        return in_array('manager', \Yii::$app->user->identity->getRoles());
    }
}