<?php

use yii\helpers\Html;
use yii\web\View;


/* @var $this yii\web\View */
/* @var $company app\models\Company */
/* @var $signupForm app\models\SignupForm */

$this->title = 'Добавить пользователя';
$this->params['breadcrumbs'][] = ['label' => 'Дилеры', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $company->name, 'url' => ['view', 'id' => $company->id]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="company-user-create">

    <h1>Добавить пользователя для дилера <?= $company->name ?></h1>

    <?= $this->render('_formUser', [
        'company' => $company,
        'signupForm' => $signupForm,
    ]) ?>

</div>
