<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use app\models\Company;
use app\models\Model;
use app\models\Storage;
use app\models\Task;
use app\models\TaskRun;
use app\models\Report;
use yii\console\Controller;
use yii\helpers\BaseConsole;
use yii\helpers\Html;
use yii\log\Logger;

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
    /**
     * Рассылает уведомления о заказанных авто
     *
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
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
                'robot@robotzakaz.ru' => 'Робот Киа'
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
                    $to[] = trim($companyEmail);
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
                echo "Sending notification about " . sizeof($filtered) . " task runs for company {$company->name}({$company->id})\n";
                $subject = 'Результат работы системы автозаказа';
                foreach ($to as $userEmail) {
                    try {
                        \Yii::$app->mailer->compose('/email/robot', [
                            'taskRuns' => $filtered
                        ])
                            ->setFrom($from)
                            ->setTo($userEmail)
                            ->setSubject($subject)
                            ->send();
                    } catch (\Exception $exception) {
                        \Yii::error("Error sending email to $userEmail: {$exception->getMessage()}");
                    }
                }
            } else {
                echo "No notification as of now for company {$company->name}({$company->id})\n";
            }
        }
    }

    /**
     * Рассылает отчет о заказанных вчера(по умолчанию) авто
     *
     * @param bool $dateStart
     * @throws \yii\base\InvalidConfigException
     */
    public function actionSummary($dateStart = false, $dateEnd = false)
    {
        if ($dateStart) {
            $dateStartTimestamp = strtotime($dateStart);
            $dateStartStartOfDay = date('Y-m-d', $dateStartTimestamp) . " 00:00:00";

        } else {
            $dateStartTimestamp = time() - 24 * 3600;
            $dateStartStartOfDay = date('Y-m-d', $dateStartTimestamp) . " 00:00:00";

        }
        if ($dateEnd) {
            $dateEndTimestamp = strtotime($dateEnd);
            $dateEndEndOfDay = date('Y-m-d', $dateEndTimestamp) . " 23:59:59";
        } else {
            $dateEndTimestamp = time() - 24 * 3600;
            $dateEndEndOfDay = date('Y-m-d', $dateEndTimestamp) . " 23:59:59";
        }
        /**
         * @var TaskRun[] $taskRuns
         */
        $taskRuns = TaskRun::find()
            ->where(['>=', 'created_at', $dateStartStartOfDay])
            ->andWhere(['<=', 'created_at', $dateEndEndOfDay])
            ->orderBy(['task_id' => SORT_ASC])
            ->all();
        $tasks = [];
        $greatTotal = 0;
        foreach ($taskRuns as $taskRun) {
            if (!isset($tasks[$taskRun->task_id])) {
                $tasks[$taskRun->task_id] = [
                    'create_date' => \Yii::$app->formatter->asDatetime($taskRun->task->created_at),
                    'id' => Html::a($taskRun->task_id,
                        'https://lk.robotzakaz.ru/index.php?r=task%2Fview&id=' . $taskRun->task_id),
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
            'robot@robotzakaz.ru' => 'Робот Киа',
        ];
        $to = [
            'mk@turbodealer.ru' => 'Куклин Михаил',
            'reports_turbo@mail.ru' => 'Сборщик почты',
            'is@turbodealer.ru' => 'Сняткова Ирина',
            'dav.kirill.86@gmail.com' => 'Давыдовский Кирилл'
        ];
        $dateRange = date('d.m.Y', $dateStartTimestamp);
        if (date('d.m.Y', $dateEndTimestamp) != $dateRange) {
            $dateRange .= " - " . date('d.m.Y', $dateEndTimestamp);
        }
        $subject = 'Робот Киа: отчет о заказанных авто за ' . $dateRange;
        $viewData = [
            'data' => $tasks,
            'date' => $dateRange,
            'greatTotal' => $greatTotal
        ];

        \Yii::$app->mailer->compose('/email/summary', $viewData)
            ->setFrom($from)
            ->setTo($to)
            ->setSubject($subject)
            ->send();
    }

    /**
     * Отчет о комплектациях
     */
    public function actionFields()
    {
        $from = [
            'robot@robotzakaz.ru' => 'Робот Киа',
        ];
        $to = [
            'mk@turbodealer.ru' => 'Куклин Михаил',
            'reports_turbo@mail.ru' => 'Сборщик почты',
            'is@turbodealer.ru' => 'Сняткова Ирина',
            'dav.kirill.86@gmail.com' => 'Давыдовский Кирилл'
        ];
        $subject = 'Робот Киа: отчет о комплектациях за ' . date('d.m.Y');
        $models = Model::find()->all();
        $viewData = [
            'models' => $models,
            'header' => $subject,
        ];

        \Yii::$app->mailer->compose('/email/fields', $viewData)
            ->setFrom($from)
            ->setTo($to)
            ->setSubject($subject)
            ->send();
    }

    /**
     * Отчет о схожих задачах за вчера
     */
    public function actionAboutSimilarTask()
    {
        $yesterday = time() - 24 * 3600;
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
            $suitableForCompare = (strlen($task->model_value) > 0) && (strlen($task->manufacture_code_value) > 0);
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
            if (sizeof($similarTasks) > 0) {
                $similarGroup = [
                    'main' => $task,
                    'similar' => []
                ];
            }
            foreach ($similarTasks as $similarTask) {
                $similarGroup['similar'][] = $similarTask;
                $processed[] = $similarTask->id;
            }
            if (sizeof($similarGroup) > 0) {
                $result[] = $similarGroup;
            }
        }
        if (sizeof($result) > 0) {
            $from = [
                'robot@robotzakaz.ru' => 'Робот Киа'
            ];
            $to = [
                'mk@turbodealer.ru',
                'is@turbodealer.ru',
                'dav.kirill.86@gmail.com'
            ];
            echo "Sending notification about " . sizeof($result) . " similar tasks\n";
            $subject = 'Робот Киа: найдены похожие задачи';
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

    /**
     * Отчет о забаненных компаниях
     */
    public function actionBanned()
    {
        $companies = Company::find()
            ->where(['>', 'banned_at', 0])
            ->andWhere(['=', 'notified_about_ban', 0])
            ->all();

        echo "Sending notification about " . sizeof($companies) . " banned companies\n";
        foreach ($companies as $company) {
            echo "Sending notification about {$company->name} banned to company contacts\n";
            $from = [
                'robot@robotzakaz.ru' => 'Робот Киа'
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
            $subject = 'Некорректный логин/пароль к сайту Киа';
            \Yii::$app->mailer->compose('/email/banned', [
                'company' => $company
            ])
                ->setFrom($from)
                ->setTo($to)
                ->setSubject($subject)
                ->send();

            echo "Sending notification about {$company->name} banned to Turbodealer contacts\n";

            $from = [
                'robot@robotzakaz.ru' => 'Робот Киа'
            ];
            $to = [
                'mk@turbodealer.ru',
                'is@turbodealer.ru',
                'dav.kirill.86@gmail.com'
            ];
            $subject = 'Некорректный логин/пароль к сайту Киа у компании ' . $company->name;
            \Yii::$app->mailer->compose('/email/banned_td', [
                'company' => $company
            ])
                ->setFrom($from)
                ->setTo($to)
                ->setSubject($subject)
                ->send();

            $company->notified_about_ban = 1;
            $company->save(false);
        }
    }

    public function actionMatchTasks()
    {
        $result = [];
        $tasks = Task::find()->where(['deleted_at' => 0]);
        /**
         * @var Task $task
         */
        $tasksCount = 0;
        foreach ($tasks->each() as $task) {
            if ($task->ordered >= $task->amount) {
                continue;
            }
            $tasksCount++;
            $searchParams = [];
            if ($task->model_value) {
                $searchParams['model'] = $task->model_value;
            }
            if ($task->manufacture_code_value) {
                $searchParams['manufacture_code'] = $task->manufacture_code_value;
            }
            if ($task->color_outside_value) {
                $searchParams['color_outside'] = $task->color_outside_value;
            }
            if ($task->color_inside_value) {
                $searchParams['color_inside'] = $task->color_inside_value;
            }
            $taskCreatedAt = date('Y-m-d H:i:s', $task->created_at);
            $storageItems = Storage::find()
                ->where($searchParams)
                ->andWhere(['>=', 'created_at', $taskCreatedAt])
                ->orderBy(['created_at' => SORT_DESC])
                ->all();
            if (sizeof($storageItems)>0) {
                $result[] = [
                    'task' => $task,
                    'storageItems' => $storageItems
                ];
            }
            BaseConsole::output("{$tasksCount} tasks processed. Found ".sizeof($result)." items to notify about");
        }
        $from = [
            'robot@robotzakaz.ru' => 'Робот Турбодилера'
        ];
        $to = [
            'mk@turbodealer.ru',
            'is@turbodealer.ru',
            'dav.kirill.86@gmail.com'
        ];
        if (sizeof($result)>0) {
            $subject = 'Нашли совпадение задач со складом';
            \Yii::$app->mailer->compose('/email/storage_matched', [
                'result' => $result
            ])
                ->setFrom($from)
                ->setTo($to)
                ->setSubject($subject)
                ->send();
        } else {
            $subject = 'Не нашли совпадение задач со складом';
            \Yii::$app->mailer->compose('/email/storage_non_matched', [
                'tasksCount' => $tasksCount
            ])
                ->setFrom($from)
                ->setTo($to)
                ->setSubject($subject)
                ->send();
        }
    }
}
