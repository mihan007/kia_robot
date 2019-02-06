<?php

namespace app\commands;

use app\models\SignupForm;
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
}