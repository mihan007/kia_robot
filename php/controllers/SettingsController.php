<?php


namespace app\controllers;


use app\models\Company;
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\HttpException;

class SettingsController extends Controller
{
    public function actionIndex($company_id = 0)
    {
        if (Yii::$app->user->isAdmin) {
            if ($company_id == 0) {
                $dataProvider = new ActiveDataProvider([
                    'query' => Company::find()->where('status != ' . Company::STATUS_DELETED),
                ]);

                return $this->render('companyList', [
                    'dataProvider' => $dataProvider,
                ]);
            } else {
                $company = Company::findOne($company_id);
                if (!$company) {
                    throw new HttpException(404, 'Компания не найдена');
                }
                if (Yii::$app->request->isPost) {
                    $oldPassword = $company->kia_password;
                    $result = $company->setNotificationEmail(Yii::$app->request->post('email'));
                    $company->kia_login = Yii::$app->request->post('kia_login');
                    $company->kia_password = Yii::$app->request->post('kia_password');
                    $newPassword = $company->kia_password;
                    $successMessage = 'Настройки успешно сохранены';
                    if ($company->banned_at > 0) {
                        $company->banned_at = null;
                        if ($newPassword != $oldPassword) {
                            $successMessage .= '. Так как вы изменили пароль доступа к сайту Киа, то признак закрытия доступа снят и все задачи вскоре начнут работать штатно.';
                        } else {
                            Yii::$app->session->addFlash('warning', "Вам необходимо внести корректный пароль от сайта Киа для дилера {$company->name}, т.к. на текущий момент задачи не выполняются.");
                        }
                    }
                    if ($result === true) {
                        $company->save(false);
                        Yii::$app->getSession()->setFlash('success', $successMessage);
                    } else {
                        Yii::$app->getSession()->setFlash('error', 'Ошибка сохранения email для уведомления. Следующие email некорректны: ' . implode(', ', $result));
                    }
                    return $this->redirect(['settings/index', 'company_id' => $company_id]);
                }

                return $this->render('_form', [
                    'company' => $company,
                ]);
            }
        } elseif (Yii::$app->user->isLeadManager) {
            $company = Company::findOne(Yii::$app->user->companyId);
            if (!$company) {
                throw new HttpException(404, 'Компания не найдена');
            }
            if (Yii::$app->request->isPost) {
                $oldPassword = $company->kia_password;
                $result = $company->setNotificationEmail(Yii::$app->request->post('email'));
                $company->kia_login = Yii::$app->request->post('kia_login');
                $company->kia_password = Yii::$app->request->post('kia_password');
                $newPassword = $company->kia_password;
                $successMessage = 'Настройки успешно сохранены';
                if ($company->banned_at > 0) {
                    $company->banned_at = null;
                    if ($newPassword != $oldPassword) {
                        $successMessage .= '. Так как вы изменили пароль доступа к сайту Киа, то признак закрытия доступа снят и все задачи вскоре начнут работать штатно.';
                    } else {
                        Yii::$app->session->addFlash('warning', "Вам необходимо внести корректный пароль от сайта Киа, т.к. на текущий момент задачи не выполняются.");
                    }
                }
                if ($result === true) {
                    $company->save(false);
                    Yii::$app->getSession()->setFlash('success', $successMessage);
                } else {
                    Yii::$app->getSession()->setFlash('error', 'Ошибка сохранения email для уведомления. Следующие email некорректны: ' . implode(', ', $result));
                }
                return $this->redirect(['settings/index']);
            }

            return $this->render('_form', [
                'company' => $company,
            ]);
        }
    }
}