<?php

namespace app\commands;

use Yii;
use yii\console\Controller;

class RbacController extends Controller
{
    public function actionInit()
    {
        $auth = Yii::$app->authManager;

        // добавляем разрешение "createTask"
        $createTask = $auth->createPermission('createTask');
        $createTask->description = 'Create a task';
        $auth->add($createTask);

        // добавляем разрешение "updateTask"
        $updateTask = $auth->createPermission('updateTask');
        $updateTask->description = 'Update task';
        $auth->add($updateTask);

        // добавляем роль "manager" и даём роли разрешение "createTask"
        $manager = $auth->createRole('manager');
        $auth->add($manager);
        $auth->addChild($manager, $createTask);

        // добавляем роль "leadManager" и даём роли разрешение "updateTask"
        // а также все разрешения роли "manager"
        $leadManager = $auth->createRole('leadManager');
        $auth->add($leadManager);
        $auth->addChild($leadManager, $updateTask);
        $auth->addChild($leadManager, $manager);

        // добавляем роль "admin"
        // а также все разрешения роли "leadManager"
        $admin = $auth->createRole('admin');
        $auth->add($admin);
        $auth->addChild($admin, $leadManager);
    }

    public function actionStage2()
    {
        $auth = Yii::$app->authManager;
        // добавляем разрешение "createTask"
        $manageCompany = $auth->createPermission('manageCompany');
        $manageCompany->description = 'Manage company records';
        $auth->add($manageCompany);

        $admin = $auth->getRole('admin');
        $auth->addChild($admin, $manageCompany);
    }
}