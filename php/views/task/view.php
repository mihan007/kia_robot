<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $model app\models\Task */
/* @var $taskRunDataProvider yii\data\ActiveDataProvider */

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
            [
                'label' => 'Дата создания',
                'value' => Yii::$app->formatter->format($model->created_at, 'datetime')
            ],
            'model_name',
            'manufacture_code_name',
            'color_inside_name',
            'color_outside_name',
            'amount',
        ],
    ]) ?>

    <h2>Запуски задачи</h2>

    <?= GridView::widget([
        'dataProvider' => $taskRunDataProvider,
        'columns' => [
            'id',
            [
                'label' => 'Дата запуска',
                'class' => 'yii\grid\DataColumn',
                'format' => 'raw',
                'value' => function ($data) {
                    return Yii::$app->formatter->format(strtotime($data->created_at)-3*3600, 'datetime');
                },
            ],
            'model_name',
            'manufacture_code_name',
            'color_inside_name',
            'color_outside_name',
            'amount',

            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view}',
                'urlCreator' => function ($action, $model, $key, $index) {
                    if ($action === 'view') {
                        $url = Url::to(['task-run/view', 'id' => $model->id]);
                        return $url;
                    }
                }
            ],
        ],
    ]); ?>
</div>
