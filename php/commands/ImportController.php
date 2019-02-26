<?php

namespace app\commands;

use app\models\ColorOutside;
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

    public function actionCodes2()
    {
        $filePath = \Yii::getAlias('@app/files/kia_codes') . "/2019_02_26_kia_codes.csv";
        $content = file_get_contents($filePath);
        $rows = explode("\r", $content);
        $processedModelValues = [];
        foreach ($rows as $row) {
            $columns = explode(";", $row);
            $modelValue = $columns[1];
            $manufactureCode1 = $columns[2];
            $manufactureCode2 = $columns[3];
            $manufactureCode3 = $columns[4];

            $manufactureCode = $manufactureCode1.$manufactureCode2.$manufactureCode3;

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

    public function actionColors()
    {
        $colors = "1D, ABB, FRD, PGU, UD, WD, 1K, 3D, 4SS, 9H, 9S, A2E, A2R, A3D, AA3, AA9, AAP, AAQ, AAW, AAY, ABP, ABT, AE3, AJR, AUB, AYB, B2R, B2Y, B3L, B4U, BBL, BLA, C2S, C4S, CB7, CR5, CSS, CU3, D5U, D7U, D9B, DN9, DRG, E5B, E6S, EAB, G4N, G7A, H4R, H8G, HW2, IM, K3G, K3N, K3R, K3U, KCS, KDG, KLG, L2B, L2E, L5S, M5G, M6B, M9Y, MBN, MSL, MST, MYB, MZH, N4U, NBM, P2M, R4R, RHM, RNG, S4N, S7Y, SN4, SWP, U4G, UAA, W4Y, AH1, AH4, AH6, AH7";
        $colorsArray = explode(",", $colors);
        $models = Model::find()->all();
        foreach ($colorsArray as $color) {
            $normalizedColor = trim($color);
            foreach ($models as $model) {
                if (strlen($model->value) == 0) {
                    continue;
                }
                $colorModel = ColorOutside::findOne(['model_id' => $model->id, 'value' => $normalizedColor]);
                if (!$colorModel) {
                    $colorModel = new ColorOutside();
                    $colorModel->name = $normalizedColor;
                    $colorModel->value = $normalizedColor;
                    $colorModel->model_id = $model->id;
                    $colorModel->save();
                    $this->log("New outside color code for model {$model->value} added: {$normalizedColor}({$colorModel->id})");
                } else {
                    $this->log("Outside color code {$normalizedColor}({$colorModel->id}) for model {$model->value} already exists");
                }
            }
        }
    }
}