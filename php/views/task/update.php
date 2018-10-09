<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Task */
/* @var array $manufactureCodes */
/* @var array $colorsInside */
/* @var array $colorsOutside */

$this->title = 'Редактировать задачу #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Текущие задачи', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => 'Задача #'.$model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Редактировать';
?>
<div class="task-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'manufactureCodes' => $manufactureCodes,
        'colorsInside' => $colorsInside,
        'colorsOutside' => $colorsOutside
    ]) ?>

</div>
