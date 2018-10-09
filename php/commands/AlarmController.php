<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use app\models\Task;
use app\models\TaskRun;
use yii\console\Controller;
use yii\console\ExitCode;

/**
 * This command echoes the first argument that you have entered.
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class AlarmController extends Controller
{
    public function actionNotification()
    {
        $tasks = Task::find()
            ->where(['deleted_at' => null])
            ->orderBy(['id' => 'ASC'])
            ->all();
        $viewData = [];
        foreach ($tasks as $task) {
            $taskRuns = TaskRun::find()
                ->where(['task_id' => $task->id])
                ->andWhere(['>', 'created_at', date('Y-m-d').' 00:00:00'])
                ->all();
            $item = [
                'task' => $task,
                'taskRuns' => $taskRuns
            ];
            $viewData[] = $item;
        }

        $from = [
            'mailer@turbodealer.ru' => 'Робот Турбодилера'
        ];
        $to = [
            'mihan007@ya.ru' => 'Куклин Михаил',
            'ae@alarm-motors.ru' => 'Евстюшкин Александр'
        ];
        $subject = 'Результат работы системы автозаказа';
        \Yii::$app->mailer->compose('/email/robot', [
            'data' => $viewData
        ])
            ->setFrom($from)
            ->setTo($to)
            ->setSubject($subject)
            ->send();
    }
}
