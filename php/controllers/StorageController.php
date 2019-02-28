<?php

namespace app\controllers;

use app\models\Model;
use Yii;
use yii\web\Controller;
use app\models\Storage;
use yii\data\ArrayDataProvider;

class StorageController extends Controller
{
    private $modelNames = [];

    public $enableCsrfValidation = false;

    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionProxy()
    {
        $periodByPostSet = Yii::$app->request->post('period') !== null;
        $periodByPost = Yii::$app->request->post('period');
        $periodStart = date('Y-m-d 00:00:00');
        $periodEnd = date('Y-m-d 23:59:59');
        if ($periodByPostSet && $periodByPost) {
            $parts = explode(' до ', $periodByPost);
            $periodStart = date('Y-m-d 00:00:00', strtotime($parts[0]));
            $periodEnd = date('Y-m-d 23:59:59', strtotime($parts[1]));
        }

        return $this->redirect(['/storage/show', 'periodStart' => $periodStart, 'periodEnd' => $periodEnd]);
    }

    public function actionShow($periodStart, $periodEnd)
    {
        $storageItems = Storage::find()
            ->where(['>=', 'created_at', $periodStart])
            ->andWhere(['<=', 'created_at', $periodEnd])
            ->all();
        $data = [];
        $usedKeys = [];
        /**
         * @var Storage[] $storageItems
         */
        foreach ($storageItems as $item) {
            $key = $this->buildKey($item);
            if (in_array($key, $usedKeys)) {
                continue;
            }
            $usedKeys[] = $key;
            $data[] = [
                'model' => $this->getModelName($item->model),
                'manufacture_code' => $item->manufacture_code,
                'description' => $item->description,
                'color_outside' => $item->color_outside,
                'color_inside' => $item->color_inside,
                'year' => $item->year,
                'storage_code' => $item->storage_code
            ];
        }
        $provider = new ArrayDataProvider([
            'allModels' => $data,
            'pagination' => [
                'pageSize' => 10,
            ],
        ]);

        return $this->render('show', [
            'dataProvider' => $provider,
            'start' => date('d.m.Y', strtotime($periodStart)),
            'end' => date('d.m.Y', strtotime($periodEnd)),
        ]);
    }

    /**
     * @param $item Storage
     */
    protected function buildKey($item)
    {
        return "{$item->model}_{$item->manufacture_code}_{$item->color_outside}_{$item->color_inside}_{$item->year}_{$item->storage_code}";
    }

    protected function getModelName($value)
    {
        if (isset($this->modelNames[$value])) {
            return $this->modelNames[$value];
        }
        $model = Model::find()->where(['value' => $value])->one();
        if ($model) {
            $this->modelNames[$value] = $model->name;
        } else {
            $this->modelNames[$value] = $value;
        }
    }
}