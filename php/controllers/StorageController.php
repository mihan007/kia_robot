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
    private $searchModel;

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
        $filterItems = [];
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
                'model_name' => $item->model,
                'model' => $this->getModelName($item->model),
                'manufacture_code' => $item->manufacture_code,
                'description' => $item->description,
                'color_outside' => $item->color_outside,
                'color_inside' => $item->color_inside,
                'year' => $item->year,
                'storage_code' => $item->storage_code
            ];
            $filterItems = $this->analyzeForFilterItems($filterItems, $item);
        }
        $this->searchModel = [
            'model' => Yii::$app->request->get('model', ''),
            'manufacture_code' => Yii::$app->request->get('manufacture_code', ''),
            'description' => Yii::$app->request->get('description', ''),
            'color_outside' => Yii::$app->request->get('color_outside', ''),
            'color_inside' => Yii::$app->request->get('color_inside', ''),
            'year' => Yii::$app->request->get('year', ''),
            'storage_code' => Yii::$app->request->get('storage_code', '')
        ];

        $filteredResultData = array_filter($data, [$this, 'filterData']);

        $provider = new ArrayDataProvider([
            'allModels' => $filteredResultData,
            'pagination' => [
                'pageSize' => 50,
            ],
        ]);

        return $this->render('show', [
            'dataProvider' => $provider,
            'start' => date('d.m.Y', strtotime($periodStart)),
            'end' => date('d.m.Y', strtotime($periodEnd)),
            'searchModel' => $this->searchModel,
            'filterItems' => $filterItems
        ]);
    }

    private function filterData($item)
    {
        $filterByModelValue = $this->searchModel['model'];
        if (strlen($filterByModelValue) > 0) {
            $modelFilter = ($item['model_name'] == $filterByModelValue);
        } else {
            $modelFilter = true;
        }

        $filterByManufactureCodeValue = $this->searchModel['manufacture_code'];
        if (strlen($filterByManufactureCodeValue) > 0) {
            $manufacureCodeFilter = (strpos($item['manufacture_code'], $filterByManufactureCodeValue) !== false);
        } else {
            $manufacureCodeFilter = true;
        }

        $filterByDescriptionValue = $this->searchModel['description'];
        if (strlen($filterByDescriptionValue) > 0) {
            $descriptionFilter = (strpos($item['description'], $filterByDescriptionValue) !== false);
        } else {
            $descriptionFilter = true;
        }

        $filterByColorOutsideValue = $this->searchModel['color_outside'];
        if (strlen($filterByColorOutsideValue) > 0) {
            $colorOutsideFilter = ($item['color_outside'] == $filterByColorOutsideValue);
        } else {
            $colorOutsideFilter = true;
        }

        $filterByColorInsideValue = $this->searchModel['color_inside'];
        if (strlen($filterByColorInsideValue) > 0) {
            $colorInsideFilter = ($item['color_inside'] == $filterByColorInsideValue);
        } else {
            $colorInsideFilter = true;
        }

        $filterByYearValue = $this->searchModel['year'];
        if (strlen($filterByYearValue) > 0) {
            $yearFilter = ($item['year'] == $filterByYearValue);
        } else {
            $yearFilter = true;
        }

        $filterByStorageCodeValue = $this->searchModel['storage_code'];
        if (strlen($filterByStorageCodeValue) > 0) {
            $storageCodeFilter = (strpos($item['storage_code'], $filterByStorageCodeValue) !== false);
        } else {
            $storageCodeFilter = true;
        }

        return $modelFilter && $manufacureCodeFilter && $descriptionFilter && $colorOutsideFilter && $colorInsideFilter && $yearFilter && $storageCodeFilter;
    }

    public function analyzeForFilterItems($filterItems, $item)
    {
        if (!isset($filterItems['model'])) {
            $filterItems['model'] = [
                '' => '-- Все --'
            ];
        }
        if (!isset($filterItems['color_outside'])) {
            $filterItems['color_outside'] = [
                '' => '-- Все --'
            ];
        }
        if (!isset($filterItems['color_inside'])) {
            $filterItems['color_inside'] = [
                '' => '-- Все --'
            ];
        }
        if (!isset($filterItems['year'])) {
            $filterItems['year'] = [
                '' => '-- Все --'
            ];
        }
        if (!isset($filterItems['model'][$item->model])) {
            $filterItems['model'][$item->model] = $this->getModelName($item->model);
        }
        if (!in_array($item->color_outside, $filterItems['color_outside'])) {
            $filterItems['color_outside'][$item->color_outside] = $item->color_outside;
        }
        if (!in_array($item->color_inside, $filterItems['color_inside'])) {
            $filterItems['color_inside'][$item->color_inside] = $item->color_inside;
        }
        if (!in_array($item->year, $filterItems['year'])) {
            if ($item->year == 0) {
                $filterItems['year'][] = '-Пусто-';
            } else {
                $filterItems['year'][$item->year] = $item->year;
            }
        }

        return $filterItems;
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

        return $this->modelNames[$value];
    }
}