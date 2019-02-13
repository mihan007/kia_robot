<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use app\models\Company;
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
class EmailController extends Controller
{
    public function actionNotification()
    {
        $companies = Company::find()
            ->where(['status' => 1])
            ->all();

        foreach ($companies as $company) {
            $taskRuns = TaskRun::find()
                ->where(['notified' => 0])
                ->andWhere(['company_id' => $company->id])
                ->orderBy(['id' => 'ASC'])
                ->all();

            $from = [
                'robot@turbodealer.ru' => 'Робот Турбодилера'
            ];
            $to = [
                'mk@turbodealer.ru',
            ];
            $companyNotification = $company->notification_email;
            if (strlen($companyNotification) == 0) {
                echo "No notification email set for company {$company->name}({$company->id}) as of now, skip\n";
            } else {
                $companyEmails = explode(',', $company->notification_email);
                foreach ($companyEmails as $companyEmail) {
                    $to[] = $companyEmail;
                }
            }

            $filtered = [];
            foreach ($taskRuns as $taskRun) {
                if ($taskRun->amount_ordered > 0) {
                    $filtered[] = $taskRun;
                }
                $taskRun->notified = 1;
                $taskRun->update(false, ['notified']);
            }

            if (sizeof($filtered) > 0) {
                echo "Sending notification about ".sizeof($filtered)." task runs for company {$company->name}({$company->id})\n";
                $subject = 'Результат работы системы автозаказа';
                \Yii::$app->mailer->compose('/email/robot', [
                    'taskRuns' => $filtered
                ])
                    ->setFrom($from)
                    ->setTo($to)
                    ->setSubject($subject)
                    ->send();
            } else {
                echo "No notification as of now for company {$company->name}({$company->id})\n";
            }
        }
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
        $subject = 'Робот Аларма: отчет о заказанных авто за ' . date('d.m.Y', $yesterday);
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
        if (!is_dir($reportPath . $subfolderName)) {
            mkdir($reportPath . $subfolderName);
        }

        $fullReportPath = realpath($reportPath . $subfolderName . '/');
        if (substr($fullReportPath, -1) !== '/') {
            $fullReportPath .= '/';
        }
        $reportName = 'alarm_summary_last.html';
        $string = \Yii::$app->mailer->render('/email/summary', $viewData, '@app/mail/layouts/html');

        echo "Store report(" . sizeof($string) . ") to {$fullReportPath}{$reportName}\n";
        file_put_contents($fullReportPath . $reportName, $string);

        $archiveName = "alarm_summary_" . date('Y_m_d_H_i_s') . ".html";
        $archiveReportPath = $fullReportPath . $archiveName;
        file_put_contents($archiveReportPath, $string);

        $report = new Report();
        $report->section = "alarm";
        $report->subject = $subject;
        $report->url = \Yii::$app->params['turboDomainMain'] . "/static/reports/{$subfolderName}/" . $archiveName;
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
        $subject = 'Робот Аларма: отчет о комплектациях за ' . date('d.m.Y');
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
        if (!is_dir($reportPath . $subfolderName)) {
            mkdir($reportPath . $subfolderName);
        }

        $fullReportPath = realpath($reportPath . $subfolderName . '/');
        if (substr($fullReportPath, -1) !== '/') {
            $fullReportPath .= '/';
        }
        $reportName = 'alarm_fields_last.html';
        $string = \Yii::$app->mailer->render('/email/fields', $viewData, '@app/mail/layouts/html');

        echo "Store report(" . sizeof($string) . ") to {$fullReportPath}{$reportName}\n";
        file_put_contents($fullReportPath . $reportName, $string);

        $archiveName = "alarm_fields_" . date('Y_m_d_H_i_s') . ".html";
        $archiveReportPath = $fullReportPath . $archiveName;
        file_put_contents($archiveReportPath, $string);

        $report = new Report();
        $report->section = "alarm";
        $report->subject = $subject;
        $report->url = \Yii::$app->params['turboDomainMain'] . "/static/reports/{$subfolderName}/" . $archiveName;
        $report->save();
    }
}