<?php

namespace app\controllers;

use app\models\ColorInside;
use app\models\ColorOutside;
use app\models\ManufactureCode;
use Yii;
use app\models\Task;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
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
        $dataProvider = new ActiveDataProvider([
            'query' => Task::find()->where(['deleted_at' => null])->orderBy('id DESC'),
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
        $dataProvider = new ActiveDataProvider([
            'query' => Task::find()->where(['not', ['deleted_at' => null]])->orderBy('id DESC'),
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
        return $this->render('view', [
            'model' => $this->findModel($id),
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
        $manufactureCodes = [];
        $colorsInside = [];
        $colorsOutside = [];

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index']);
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
        $model = Model::findOne(['value' => $task->model_value]);
        $manufactureCodes = ArrayHelper::map(ManufactureCode::findAll(['model_id' => $model->id]), 'value', 'name');
        $colorsInside = ArrayHelper::map(ColorInside::findAll(['model_id' => $model->id]), 'value', 'name');
        $colorsOutside = ArrayHelper::map(ColorOutside::findAll(['model_id' => $model->id]), 'value', 'name');

        if ($task->load(Yii::$app->request->post()) && $task->save()) {
            return $this->redirect(['index']);
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
        $manufactures = ArrayHelper::map(ManufactureCode::findAll(['model_id'=>$model->id]), 'value', 'name');
        $out = [];
        foreach ($manufactures as $key => $value) {
            $out[] = [
                'id' => $key,
                'name' => $value
            ];
        }
        echo Json::encode(['output'=>$out, 'selected'=>'']);
    }

    public function actionColorsInside()
    {
        $modelValue = $_POST['model_id'];
        $model = Model::findOne(['value' => $modelValue]);
        $items = ArrayHelper::map(ColorInside::findAll(['model_id'=>$model->id]), 'value', 'name');
        $out = [];
        foreach ($items as $key => $value) {
            $out[] = [
                'id' => $key,
                'name' => $value
            ];
        }
        echo Json::encode(['output'=>$out, 'selected'=>'']);
    }

    public function actionColorsOutside()
    {
        $modelValue = $_POST['model_id'];
        $model = Model::findOne(['value' => $modelValue]);
        $items = ArrayHelper::map(ColorOutside::findAll(['model_id'=>$model->id]), 'value', 'name');
        $out = [];
        foreach ($items as $key => $value) {
            $out[] = [
                'id' => $key,
                'name' => $value
            ];
        }
        echo Json::encode(['output'=>$out, 'selected'=>'']);
    }
}
