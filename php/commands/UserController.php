<?php

namespace app\commands;

use app\models\SignupForm;
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

    public function actionAboutSimilarTask()
    {
        $yesterday = time() - 24*3600;
        $start = date('Y-m-d 00:00:00', $yesterday);
        $end = date('Y-m-d 23:59:59', $yesterday);

        $startTimestamp = strtotime($start);
        $endTimestamp = strtotime($end);

        $tasks = Task::find()
            ->where(['>=', 'created_at', $startTimestamp])
            ->andWhere(['<=', 'created_at', $endTimestamp])
            ->all();

        $processed = [];
        $result = [];
        foreach ($tasks as $task) {
            if (in_array($task->id, $processed)) {
                continue;
            }
            $processed[] = $task->id;
            $suitableForCompare = (strlen($task->model_value)>0) && (strlen($task->manufacture_code_value)>0);
            if (!$suitableForCompare) {
                continue;
            }
            $similarTasks = Task::find()
                ->where(['>=', 'created_at', $startTimestamp])
                ->andWhere(['<=', 'created_at', $endTimestamp])
                ->andWhere(['!=', 'id', $task->id])
                ->andWhere(['=', 'model_value', $task->model_value])
                ->andWhere(['=', 'manufacture_code_value', $task->manufacture_code_value])
                ->andWhere(['!=', 'company_id', $task->company_id])
                ->all();
            $similarGroup = [];
            if (sizeof($similarTasks)>0) {
                $similarGroup = [
                    'main' => $task,
                    'similar' => []
                ];
            }
            foreach ($similarTasks as $similarTask) {
                $similarGroup['similar'][] = $similarTask;
                $processed[] = $similarTask->id;
            }
            if (sizeof($similarGroup)>0) {
                $result[] = $similarGroup;
            }
        }
        if (sizeof($result)>0) {
            $from = [
                'robot@turbodealer.ru' => 'Робот Турбодилера'
            ];
            $to = [
                'mk@turbodealer.ru',
//                'is@turbodealer.ru',
//                'dav.kirill.86@gmail.com'
            ];
            echo "Sending notification about ".sizeof($result)." similar tasks\n";
            $subject = 'Найдены похожие задачи';
            \Yii::$app->mailer->compose('/email/similar', [
                'result' => $result
            ])
                ->setFrom($from)
                ->setTo($to)
                ->setSubject($subject)
                ->send();
        } else {
            echo "No similar task found\n";
        }
    }
}