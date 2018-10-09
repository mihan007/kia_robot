<?php

namespace app\controllers;

use Yii;
use app\models\TaskRun;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * TaskRunController implements the CRUD actions for TaskRun model.
 */
class TaskRunController extends Controller
{
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
