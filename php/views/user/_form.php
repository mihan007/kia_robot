<?php

use app\models\Company;
use app\models\SignupForm;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $company app\models\Company */
/* @var $signupForm app\models\SignupForm */
?>

<div class="company-user-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->errorSummary($signupForm); ?>

    <?= $form->field($signupForm, 'username')->textInput(['maxlength' => true]) ?>
    <?= $form->field($signupForm, 'email')->textInput(['maxlength' => true]) ?>
    <?= $form->field($signupForm, 'password')->passwordInput(['maxlength' => true]) ?>
    <?= $form->field($signupForm, 'role')->radioList([
            \app\models\User::ROLE_LEAD_MANAGER => 'Руководитель',
            \app\models\User::ROLE_MANAGER => 'Менеджер',
        ]);
    ?>

    <div class="form-group">
        <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
