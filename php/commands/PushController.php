<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use app\models\Company;
use app\models\TaskRun;
use yii\console\Controller;
use yii\helpers\Url;
use yii\httpclient\Client;

/**
 * This command echoes the first argument that you have entered.
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class PushController extends Controller
{
    const PUSH_4_SITE_URL = 'https://push4site.com/interface/send';
    const PUSH_4_SITE_API_KEY = '9a1b61d2b46747508e4cc2fa2a643f2d';

    /**
     * Рассылает уведомления о заказанных авто
     *
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionNotify()
    {
        $companies = Company::find()
            ->where(['status' => 1])
            ->all();

        foreach ($companies as $company) {
            $taskRuns = TaskRun::find()
                ->select(TaskRun::fieldsToSelect())
                ->where(['push_notified' => 0, 'company_id' => $company->id])
                ->andWhere(['>', 'amount_ordered', 0])
                ->orderBy(['id' => 'ASC'])
                ->all();


            $companyNotificationTag = $company->push_notification_tag;
            if (strlen($companyNotificationTag) == 0) {
                echo "No notification tag set for company {$company->name}({$company->id}) as of now, skip\n";
                continue;
            }

            $countOrdered = sizeof($taskRuns);
            if ($countOrdered > 0) {
                echo "Sending notification about " . $countOrdered . " task runs for company {$company->name}({$company->id})\n";
                foreach ($taskRuns as $taskRun) {
                    $client = new Client();
                    $data = $this->getPushParams($countOrdered, $taskRun, $company);
                    $response = $client->createRequest()
                        ->setFormat(Client::FORMAT_JSON)
                        ->setMethod('POST')
                        ->setUrl(self::PUSH_4_SITE_URL)
                        ->setData($data)
                        ->send();
                    $taskRun->updateAttributes(['push_notified' => 1]);
                }
            } else {
                echo "No notification as of now for company {$company->name}({$company->id})\n";
            }
        }
    }

    private function getPushParams($countOrdered, TaskRun $taskRun, Company $company)
    {
        $url = Url::to(['task-run/view', 'id' => $taskRun->id]);
        return [
            'ApiKey' => self::PUSH_4_SITE_API_KEY,
            'Title' => "Заказано $countOrdered авто",
            'Text' => "Успейте подтвердить бронирование на сайте",
            'ClickUrl' => $url,
            'SelectedTags' => [$company->push_notification_tag],
        ];
    }
}
