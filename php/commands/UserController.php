<?php

namespace app\commands;

use app\models\SignupForm;
use app\models\User;
use app\models\Task;
use Yii;
use yii\console\Controller;

class UserController extends Controller
{
    public function actionAddSuperAdmin($email)
    {
        $signupForm = new SignupForm();
        $signupForm->username = $email;
        $signupForm->email = $email;
        $signupForm->password = Yii::$app->security->generateRandomString();

        if ($user = $signupForm->signup()) {
            $auth = Yii::$app->authManager;
            $authorRole = $auth->getRole('admin');
            $auth->assign($authorRole, $user->getId());
            echo "User {$email} added with password {$signupForm->password}\n";
        } else {
            echo "User not created: " . var_export($signupForm->errors, true).PHP_EOL;
        }
    }

    /**
     * Setup new password for user with given email
     *
     * @param $email
     * @throws \yii\base\Exception
     */
    public function actionResetPassword($email)
    {
        $user = User::findOne(['email' => $email]);
        if (!$user) {
            echo "User with email {$email} not found\n";
            die;
        }
        $newPassword = Yii::$app->security->generateRandomString();
        $user->setPassword($newPassword);
        $user->save(false);
        echo "Password for user {$email} changed to {$newPassword}\n";
    }
}