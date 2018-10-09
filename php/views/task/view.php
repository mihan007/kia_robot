<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Task */

$this->title = "Задача #" . $model->id;
if ($model->deleted_at == null) {
    $this->params['breadcrumbs'][] = ['label' => 'Текущие задачи', 'url' => ['index']];
} else {
    $this->params['breadcrumbs'][] = ['label' => 'Архив задач', 'url' => ['archive']];
}
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="task-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php if ($model->deleted_at == null): ?>
        <p>
            <?= Html::a('Редактировать', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
            <?= Html::a('Удалить', ['delete', 'id' => $model->id], [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => 'Вы уверены, что хотите удалить текущую задачу?',
                    'method' => 'post',
                ],
            ]) ?>
        </p>
    <?php endif ?>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'model_name',
            'manufacture_code_name',
            'color_inside_name',
            'color_outside_name',
            'amount',
        ],
    ]) ?>

</div>
