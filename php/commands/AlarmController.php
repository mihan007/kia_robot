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
            ->where(['deleted_at' => 0])
            ->orderBy(['id' => 'ASC'])
            ->all();
        $viewData = [];
        foreach ($tasks as $task) {
            $taskRuns = TaskRun::find()
                ->where(['task_id' => $task->id])
                ->andWhere(['>', 'created_at', date('Y-m-d') . ' 00:00:00'])
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

    public function actionSummary($date = false)
    {
        if ($date) {
            $yesterday = strtotime($date);
            $yesterdayStartOfDay = date('Y-m-d', $yesterday) . " 00:00:00";
            $yesterdayEndOfDay = date('Y-m-d', $yesterday) . " 23:59:59";
        } else {
            $yesterday = time() - 24 * 3600;
            $yesterdayStartOfDay = date('Y-m-d', $yesterday) . " 00:00:00";
            $yesterdayEndOfDay = date('Y-m-d', $yesterday) . " 23:59:59";
        }
        /**
         * @var TaskRun[] $taskRuns
         */
        $taskRuns = TaskRun::find()
            ->where(['>=', 'created_at', $yesterdayStartOfDay])
            ->andWhere(['<=', 'created_at', $yesterdayEndOfDay])
            ->all();
        $tasks = [];
        $greatTotal = 0;
        foreach ($taskRuns as $taskRun) {
            if (!isset($tasks[$taskRun->task_id])) {
                $tasks[$taskRun->task_id] = [
                    'create_date' => \Yii::$app->formatter->format($taskRun->task->created_at, 'datetime'),
                    'description' => $taskRun->task->getDescription(),
                    'total' => $taskRun->amount_ordered,
                    'count' => 1
                ];
                $greatTotal += $taskRun->amount_ordered;
            } else {
                $tasks[$taskRun->task_id]['total'] += $taskRun->amount_ordered;
                $tasks[$taskRun->task_id]['count']++;
                $greatTotal += $taskRun->amount_ordered;
            }
        }

        $from = [
            'mailer@turbodealer.ru' => 'Робот Турбодилера'
        ];
        $to = [
            'mihan007@ya.ru' => 'Куклин Михаил'
        ];
        $subject = 'Робот Аларма: отчет о заказанных авто за '.date('d.m.Y', $yesterday);
        \Yii::$app->mailer->compose('/email/summary', [
            'data' => $tasks,
            'date' => date('d.m.Y', $yesterday),
            'greatTotal' => $greatTotal
        ])
            ->setFrom($from)
            ->setTo($to)
            ->setSubject($subject)
            ->send();
    }
}
