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
                    $result = $company->setNotificationEmail(Yii::$app->request->post('email'));
                    if ($result === true) {
                        $company->save(false);
                        Yii::$app->getSession()->setFlash('success', 'Email для уведомления успешно сохранены');
                    } else {
                        Yii::$app->getSession()->setFlash('error', 'Ошибка сохранения email для уведомления. Следующие email некорректны: '.implode(', ', $result));
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
                $result = $company->setNotificationEmail(Yii::$app->request->post('email'));
                if ($result === true) {
                    $company->save(false);
                    Yii::$app->getSession()->setFlash('success', 'Email для уведомления успешно сохранены');
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