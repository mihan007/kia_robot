<?php


namespace app\controllers;


use yii\web\Controller;
use yii\web\HttpException;

class HelpController extends Controller
{
    public function actionIndex()
    {
        if (\Yii::$app->user->isAdmin) {
            $this->redirect(['/help/topic', 'topic' => 'turbodealer']);
        } elseif (\Yii::$app->user->isLeadManager) {
            $this->redirect(['/help/topic', 'topic' => 'leadManager']);
        } else {
            $this->redirect(['/help/topic', 'topic' => 'manager']);
        }
    }

    public function actionTopic($topic)
    {
        if (!$this->checkAccess($topic)) {
            throw new HttpException(403, 'Доступ запрещен');
        }
        return $this->render('_layout', ['topic' => $topic]);
    }

    private function checkAccess($topic)
    {
        if (\Yii::$app->user->isGuest) {
            return false;
        }
        switch ($topic) {
            case 'turbodealer':
                if (!\Yii::$app->user->isAdmin) {
                    return false;
                }
                return true;
            case 'leadManager':
                if (!\Yii::$app->user->isAdmin && !\Yii::$app->user->isLeadManager) {
                    return false;
                }
                return true;
        }
        return true;
    }
}