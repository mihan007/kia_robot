<?php

namespace app\controllers;

use app\models\Company;
use app\models\Model;
use Yii;
use app\models\ColorPreferences;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\HttpException;
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
        if (Yii::$app->user->isAdmin) {
            $dataProvider = new ActiveDataProvider([
                'query' => Company::find()->where('status != ' . Company::STATUS_DELETED),
            ]);

            return $this->render('companyList', [
                'dataProvider' => $dataProvider,
            ]);
        } else {
            $dataProvider = new ActiveDataProvider([
                'query' => ColorPreferences::find()->where(['company_id' => Yii::$app->user->companyId]),
            ]);

            return $this->render('index', [
                'dataProvider' => $dataProvider,
                'company' => Yii::$app->user->company
            ]);
        }
    }

    public function actionViewUser($id)
    {
        if (!Yii::$app->user->isAdmin) {
            throw new HttpException(403, 'Доступ запрещен');
        }
        $dataProvider = new ActiveDataProvider([
            'query' => ColorPreferences::find()->where(['company_id' => $id]),
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'company' => Company::findOne($id)
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
        $colorPreferences = ColorPreferences::find()
            ->where(['company_id' => Yii::$app->user->companyId])
            ->all();
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
            $colorPreference = ColorPreferences::find()
                ->where(['model_value' => $modelValue, 'company_id' => Yii::$app->user->companyId])
                ->one();
            if (!$colorPreference) {
                $colorPreference = new ColorPreferences();
            }
            $colors = $this->normalizeColors($colors);
            $colorPreference->model_name = $modelName;
            $colorPreference->model_value = $modelValue;
            $colorPreference->colors = $colors;
            $colorPreference->company_id = Yii::$app->user->companyId;
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
