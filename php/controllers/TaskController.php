<?php

namespace app\controllers;

use app\models\ColorInside;
use app\models\ColorOutside;
use app\models\ManufactureCode;
use Yii;
use app\models\Task;
use app\models\TaskRun;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use app\models\Model;
use yii\helpers\Json;

/**
 * TaskController implements the CRUD actions for Task model.
 */
class TaskController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Task models.
     * @return mixed
     */
    public function actionIndex()
    {
        if (Yii::$app->user->isAdmin) {
            $query = Task::find()->where(['deleted_at' => 0])->orderBy('id DESC');
        } else if (Yii::$app->user->isLeadManager) {
            $query = Task::find()->where([
                'deleted_at' => 0,
                'company_id' => Yii::$app->user->companyId
            ])->orderBy('id DESC');
        } else {
            $query = Task::find()->where([
                'deleted_at' => 0,
                'user_id' => Yii::$app->user->id
            ])->orderBy('id DESC');
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Lists all Task models.
     * @return mixed
     */
    public function actionArchive()
    {
        if (Yii::$app->user->isAdmin) {
            $query = Task::find()
                ->where(['not', ['deleted_at' => 0]])
                ->orderBy('id DESC');
        } else if (Yii::$app->user->isLeadManager) {
            $query = Task::find()
                ->where(['not', ['deleted_at' => 0]])
                ->andWhere([
                    'company_id' => Yii::$app->user->companyId
                ])->orderBy('id DESC');
        } else {
            $query = Task::find()
                ->where(['not', ['deleted_at' => 0]])
                ->andWhere([
                    'user_id' => Yii::$app->user->id
                ])->orderBy('id DESC');
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        return $this->render('archive', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Task model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $task = $this->findModel($id);
        if (Yii::$app->user->isAdmin) {
            $query = TaskRun::find()
                ->select(TaskRun::fieldsToSelect())
                ->where(['task_id' => $task->id])
                ->orderBy('id DESC');
        } else if (Yii::$app->user->isLeadManager) {
            if ($task->company_id != Yii::$app->user->companyId) {
                throw new HttpException(403, 'Доступ запрещен');
            }

            $query = TaskRun::find()
                ->select(TaskRun::fieldsToSelect())
                ->where([
                'task_id' => $task->id,
                'company_id' => Yii::$app->user->companyId
            ])->orderBy('id DESC');
        } else {
            if ($task->user_id != Yii::$app->user->id) {
                throw new HttpException(403, 'Доступ запрещен');
            }

            $query = TaskRun::find()
                ->select(TaskRun::fieldsToSelect())
                ->where([
                'task_id' => $task->id,
                'user_id' => Yii::$app->user->id
            ])->orderBy('id DESC');
        }
        $taskRunDataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        return $this->render('view', [
            'model' => $task,
            'taskRunDataProvider' => $taskRunDataProvider
        ]);
    }

    /**
     * Creates a new Task model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Task();
        $model->goal = Task::GOAL_SPECIFIC;
        $manufactureCodes = [];
        $colorsInside = [];
        $colorsOutside = [];

        if ($model->load(Yii::$app->request->post())) {
            $model->company_id = Yii::$app->user->companyId;
            $model->user_id = Yii::$app->user->id;
            if ($model->save()) {
                return $this->redirect(['index']);
            }
        }

        return $this->render('create', [
            'model' => $model,
            'manufactureCodes' => $manufactureCodes,
            'colorsInside' => $colorsInside,
            'colorsOutside' => $colorsOutside
        ]);
    }

    /**
     * Updates an existing Task model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $task = $this->findModel($id);
        $access = false;
        if (Yii::$app->user->isAdmin) {
            $access = true;
        } elseif (Yii::$app->user->isLeadManager) {
            if (Yii::$app->user->companyId == $task->company_id) {
                $access = true;
            }
        } else {
            if (Yii::$app->user->id == $task->user_id) {
                $access = true;
            }
        }
        if (!$access) {
            throw new HttpException(403, 'Доступ запрещен');
        }
        $model = Model::findOne(['value' => $task->model_value]);
        $manufactureCodes = ArrayHelper::map(ManufactureCode::findAll(['model_id' => $model->id]), 'value', 'name');
        $colorsInside = ArrayHelper::map(ColorInside::findAll(['model_id' => $model->id]), 'value', 'name');
        $colorsOutside = ArrayHelper::map(ColorOutside::findAll(['model_id' => $model->id]), 'value', 'name');

        if ($task->load(Yii::$app->request->post())) {
            $task->company_id = Yii::$app->user->companyId;
            $task->user_id = Yii::$app->user->id;
            if ($task->save()) {
                return $this->redirect(['index']);
            }
        }

        return $this->render('update', [
            'model' => $task,
            'manufactureCodes' => $manufactureCodes,
            'colorsInside' => $colorsInside,
            'colorsOutside' => $colorsOutside
        ]);
    }

    /**
     * Deletes an existing Task model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $access = false;
        if (Yii::$app->user->isAdmin) {
            $access = true;
        } elseif (Yii::$app->user->isLeadManager) {
            if (Yii::$app->user->companyId == $model->company_id) {
                $access = true;
            }
        } else {
            if (Yii::$app->user->id == $model->user_id) {
                $access = true;
            }
        }
        if (!$access) {
            throw new HttpException(403, 'Доступ запрещен');
        }
        $model->deleted_at = time();
        $model->save();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Task model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Task the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Task::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionManufactures()
    {
        $modelValue = $_POST['model_id'];
        $model = Model::findOne(['value' => $modelValue]);
        $manufactures = ArrayHelper::map(
            ManufactureCode::find()->where(['model_id' => $model->id])->orderBy(['name' => SORT_ASC])->all(),
            'value', 'name');
        $out = [];
        foreach ($manufactures as $key => $value) {
            $out[] = [
                'id' => $key,
                'name' => $value
            ];
        }
        return Json::encode(['output' => $out, 'selected' => '']);
    }

    public function actionColorsInside()
    {
        $modelValue = $_POST['model_id'];
        $model = Model::findOne(['value' => $modelValue]);
        $items = ArrayHelper::map(
            ColorInside::find()->where(['model_id' => $model->id])->orderBy(['name' => SORT_ASC])->all(),
            'value', 'name');
        $out = [];
        foreach ($items as $key => $value) {
            $out[] = [
                'id' => $key,
                'name' => $value
            ];
        }
        echo Json::encode(['output' => $out, 'selected' => '']);
    }

    public function actionColorsOutside()
    {
        $modelValue = $_POST['model_id'];
        $model = Model::findOne(['value' => $modelValue]);
        $items = ArrayHelper::map(
            ColorOutside::find()->where(['model_id' => $model->id])->orderBy(['name' => SORT_ASC])->all(),
            'value', 'name');
        $out = [];
        foreach ($items as $key => $value) {
            $out[] = [
                'id' => $key,
                'name' => $value
            ];
        }
        echo Json::encode(['output' => $out, 'selected' => '']);
    }
}
