<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $colorPreferences app\models\ColorPreferences[] */
?>
<?php
$this->title = 'Приоритет цветов';
$this->params['breadcrumbs'][] = Yii::$app->user->company->name;
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="color-preferences-form">

    <p>Заполните приоритеты заказа цветов, введя цвета через запятую, где перый цвета - с наивысшим приоритетом и далее
        по убыванию</p>
    <?php $form = ActiveForm::begin(); ?>
    <table width="100%" border="0" class="table table-striped">
        <tr>
            <th>Модель</th>
            <th>Цвета</th>
        </tr>
        <?php foreach ($colorPreferences as $colorPreference): ?>
            <tr>
                <td width="25%">
                    <?= Html::textInput('model_name[' . $colorPreference->model_value . ']', $colorPreference->model_name,
                        ['class' => 'form-control', 'readonly' => true]); ?>
                </td>
                <td width="75%">
                    <?= Html::textInput('colors[' . $colorPreference->model_value . ']', $colorPreference->colors, ['class' => 'form-control']); ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>

    <div class="form-group">
        <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
