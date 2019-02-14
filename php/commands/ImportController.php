<?php

namespace app\commands;

use app\models\ManufactureCode;
use app\models\Model;
use yii\console\Controller;

class ImportController extends Controller
{
    private function log($line)
    {
        $date = date('Y-m-d H:i:s');
        echo "[$date] $line\n";
    }

    public function actionCodes()
    {
        $filePath = \Yii::getAlias('@app/files/kia_codes') . "/2019_02_14_kia_codes.csv";
        $content = file_get_contents($filePath);
        $rows = explode("\r", $content);
        $processedModelValues = [];
        foreach ($rows as $row) {
            $columns = explode(";", $row);
            $modelValue = $columns[1];
            $manufactureCode = $columns[2];

            $this->addOrSkip($modelValue, $manufactureCode);
            $processedModelValues[] = $modelValue;
        }
        $this->findNonMatched($processedModelValues);
    }

    private function addOrSkip($modelValue, $manufactureCode)
    {
        $model = Model::findOne(['value' => $modelValue]);
        $manufacture = ManufactureCode::findOne(['model_id' => $model->id, 'value' => $manufactureCode]);
        if (!$manufacture) {
            $manufacture = new ManufactureCode();
            $manufacture->name = $manufactureCode;
            $manufacture->value = $manufactureCode;
            $manufacture->model_id = $model->id;
            $manufacture->save();
            $this->log("New manufacture code for model {$modelValue} added: {$manufactureCode}({$manufacture->id})");
        } else {
            $this->log("Manufacture code {$manufactureCode}({$manufacture->id}) for model {$modelValue} already exists");
        }
    }

    private function findNonMatched($existed)
    {
        $models = Model::find()->all();
        foreach ($models as $model)
        {
            if (!in_array($model->value, $existed)) {
                $this->log("For {$model->name}($model->value) codes not presented");
            }
        }
    }
}