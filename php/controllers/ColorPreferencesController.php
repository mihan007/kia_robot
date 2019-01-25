<?php

namespace app\controllers;

use app\models\Model;
use Yii;
use app\models\ColorPreferences;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * ColorPreferencesController implements the CRUD actions for ColorPreferences model.
 */
class ColorPreferencesController extends Controller
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
     * Lists all ColorPreferences models.
     * @return mixed
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => ColorPreferences::find(),
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Creates a new ColorPreferences model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $colorPreferences = $this->prepareColorPreferences();

        if ($this->saveChanges()) {
            return $this->redirect(['index']);
        }

        return $this->render('_form', [
            'colorPreferences' => $colorPreferences,
        ]);
    }

    public function prepareColorPreferences()
    {
        $colorPreferences = ColorPreferences::find()->all();
        $models = Model::find()->all();
        foreach ($models as $model) {
            if (!$model->value) {
                continue;
            }
            $found = false;
            foreach ($colorPreferences as $colorPreference) {
                if ($colorPreference->model_value == $model->value) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $newColorPreference = new ColorPreferences();
                $newColorPreference->model_name = $model->name;
                $newColorPreference->model_value = $model->value;
                $colorPreferences[] = $newColorPreference;
            }
        }

        return $colorPreferences;
    }

    public function saveChanges()
    {
        if (!isset($_POST['model_name'])) {
            return false;
        }

        foreach ($_POST['model_name'] as $modelValue => $modelName) {
            $colors = $_POST['colors'][$modelValue];
            $colorPreference = ColorPreferences::find()->where(['model_value' => $modelValue])->one();
            if (!$colorPreference) {
                $colorPreference = new ColorPreferences();
            }
            $colors = $this->normalizeColors($colors);
            $colorPreference->model_name = $modelName;
            $colorPreference->model_value = $modelValue;
            $colorPreference->colors = $colors;
            $colorPreference->save();
        }

        return true;
    }

    private function normalizeColors($colors)
    {
        $items = explode(",", $colors);
        $result = [];
        foreach ($items as $item) {
            $result[] = trim($item);
        }

        return implode(",", $result);
    }

    /**
     * Finds the ColorPreferences model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return ColorPreferences the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = ColorPreferences::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
