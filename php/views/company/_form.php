<?php

use app\models\Company;
use app\models\SignupForm;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $company app\models\Company */
/* @var $signupForm app\models\SignupForm */
?>

<div class="company-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->errorSummary($model); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'kia_login')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'kia_password')->passwordInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'status')->radioList([
            \app\models\Company::STATUS_ACTIVE => 'Активен',
            \app\models\Company::STATUS_INACTIVE => 'Неактивен',
        ]);
    ?>

    <div class="form-group">
        <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
