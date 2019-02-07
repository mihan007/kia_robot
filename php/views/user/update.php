<?php

use yii\helpers\Html;
use yii\web\View;


/* @var $this yii\web\View */
/* @var $signupForm app\models\SignupForm */

$this->title = 'Редактировать сотрудника';
$this->params['breadcrumbs'][] = ['label' => \Yii::$app->user->company->name];
$this->params['breadcrumbs'][] = ['label' => 'Сотрудники', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $signupForm->username];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="company-user-create">

    <h1>Редактировать сотрудника</h1>

    <?= $this->render('_form', [
        'signupForm' => $signupForm,
    ]) ?>

</div>
