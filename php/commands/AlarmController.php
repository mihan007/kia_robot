<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use app\models\Model;
use app\models\Task;
use app\models\TaskRun;
use app\models\Report;
use yii\console\Controller;

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
            'robot@turbodealer.ru' => 'Робот Турбодилера'
        ];
        $to = [
            'mk@turbodealer.ru' => 'Куклин Михаил',
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
            'robot@turbodealer.ru' => 'Робот Турбодилера',
        ];
        $to = [
            'mk@turbodealer.ru' => 'Куклин Михаил',
            'reports_turbo@mail.ru' => 'Сборщик почты',
            'is@turbodealer.ru' => 'Сняткова Ирина',
            'dav.kirill.86@gmail.com' => 'Давыдовский Кирилл'
        ];
        $subject = 'Робот Аларма: отчет о заказанных авто за '.date('d.m.Y', $yesterday);
        $viewData = [
            'data' => $tasks,
            'date' => date('d.m.Y', $yesterday),
            'greatTotal' => $greatTotal
        ];
        $this->saveSummaryReport($subject, $viewData);

        \Yii::$app->mailer->compose('/email/summary', $viewData)
            ->setFrom($from)
            ->setTo($to)
            ->setSubject($subject)
            ->send();
    }

    public function saveSummaryReport($subject, $viewData)
    {
        $reportPath = \Yii::getAlias('@app/../../web/static/reports/');
        $subfolderName = 'alarm';
        if (!is_dir($reportPath. $subfolderName)) {
            mkdir($reportPath. $subfolderName);
        }

        $fullReportPath = realpath($reportPath.$subfolderName.'/');
        if (substr($fullReportPath, -1) !== '/') {
            $fullReportPath .= '/';
        }
        $reportName = 'alarm_summary_last.html';
        $string = \Yii::$app->mailer->render('/email/summary', $viewData, '@app/mail/layouts/html');

        echo "Store report(".sizeof($string).") to {$fullReportPath}{$reportName}\n";
        file_put_contents($fullReportPath . $reportName, $string);

        $archiveName = "alarm_summary_".date('Y_m_d_H_i_s').".html";
        $archiveReportPath = $fullReportPath.$archiveName;
        file_put_contents($archiveReportPath, $string);

        $report = new Report();
        $report->section = "alarm";
        $report->subject = $subject;
        $report->url = \Yii::$app->params['turboDomainMain'] . "/static/reports/{$subfolderName}/".$archiveName;
        $report->save();
    }

    public function actionFields()
    {
        $from = [
            'robot@turbodealer.ru' => 'Робот Турбодилера',
        ];
        $to = [
            'mk@turbodealer.ru' => 'Куклин Михаил',
            'reports_turbo@mail.ru' => 'Сборщик почты',
            'is@turbodealer.ru' => 'Сняткова Ирина',
            'dav.kirill.86@gmail.com' => 'Давыдовский Кирилл'
        ];
        $subject = 'Робот Аларма: отчет о комплектациях за '.date('d.m.Y');
        $models = Model::find()->all();
        $viewData = [
            'models' => $models,
            'header' => $subject,
        ];
        $this->saveFieldsReport($subject, $viewData);

        \Yii::$app->mailer->compose('/email/fields', $viewData)
            ->setFrom($from)
            ->setTo($to)
            ->setSubject($subject)
            ->send();
    }

    public function saveFieldsReport($subject, $viewData)
    {
        $reportPath = \Yii::getAlias('@app/../../web/static/reports/');
        $subfolderName = 'alarm';
        if (!is_dir($reportPath. $subfolderName)) {
            mkdir($reportPath. $subfolderName);
        }

        $fullReportPath = realpath($reportPath.$subfolderName.'/');
        if (substr($fullReportPath, -1) !== '/') {
            $fullReportPath .= '/';
        }
        $reportName = 'alarm_fields_last.html';
        $string = \Yii::$app->mailer->render('/email/fields', $viewData, '@app/mail/layouts/html');

        echo "Store report(".sizeof($string).") to {$fullReportPath}{$reportName}\n";
        file_put_contents($fullReportPath . $reportName, $string);

        $archiveName = "alarm_fields_".date('Y_m_d_H_i_s').".html";
        $archiveReportPath = $fullReportPath.$archiveName;
        file_put_contents($archiveReportPath, $string);

        $report = new Report();
        $report->section = "alarm";
        $report->subject = $subject;
        $report->url = \Yii::$app->params['turboDomainMain'] . "/static/reports/{$subfolderName}/".$archiveName;
        $report->save();
    }
}
