<?php

use yii\helpers\Html;
use yii\web\View;


/* @var $this yii\web\View */
/* @var $signupForm app\models\SignupForm */
$this->title = 'Добавить пользователя';
$this->params['breadcrumbs'][] = ['label' => \Yii::$app->user->company->name];
$this->params['breadcrumbs'][] = ['label' => 'Пользователи', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="company-user-create">

    <h1>Добавить пользователя</h1>

    <?= $this->render('_form', [
        'signupForm' => $signupForm,
    ]) ?>

</div>
