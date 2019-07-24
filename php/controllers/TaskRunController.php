<?php

namespace app\controllers;

use Yii;
use app\models\TaskRun;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * TaskRunController implements the CRUD actions for TaskRun model.
 */
class TaskRunController extends Controller
{
    /**
     * Lists all Task models.
     * @return mixed
     */
    public function actionIndex()
    {
        if (Yii::$app->user->isAdmin) {
            $query = TaskRun::find()->select(TaskRun::fieldsToSelect())->orderBy('id DESC');
        } elseif (Yii::$app->user->isLeadManager) {
            $query = TaskRun::find()->select(TaskRun::fieldsToSelect())->where(['company_id' => Yii::$app->user->companyId])->orderBy('id DESC');
        } else {
            $query = TaskRun::find()->select(TaskRun::fieldsToSelect())->where(['user_id' => Yii::$app->user->id])->orderBy('id DESC');
        }
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single TaskRun model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $taskRun = $this->findModel($id);
        $task = $taskRun->task;
        if (Yii::$app->user->isAdmin) {

        } else if (Yii::$app->user->isLeadManager) {
            if ($taskRun->company_id != Yii::$app->user->companyId) {
                throw new HttpException(403, 'Доступ запрещен');
            }
        } else {
            if ($taskRun->user_id != Yii::$app->user->id) {
                throw new HttpException(403, 'Доступ запрещен');
            }
        }

        return $this->render('view', [
            'task' => $task,
            'taskRun' => $taskRun,
        ]);
    }

    /**
     * Finds the TaskRun model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return TaskRun the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = TaskRun::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
