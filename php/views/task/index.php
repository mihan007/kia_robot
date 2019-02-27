<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Текущие задачи';
$this->params['breadcrumbs'][] = $this->title;
$columns = [
    [
        'label' => 'Дата создания',
        'class' => 'yii\grid\DataColumn',
        'format' => 'raw',
        'value' => function ($data) {
            return Yii::$app->formatter->format($data->created_at, 'datetime');
        },
    ],
    'goalLabel',
    'model_name',
    'manufacture_code_name',
    'color_inside_name',
    'color_outside_name',
    'amount',
    'ordered',
    [
        'label' => 'Альтернативы',
        'class' => 'yii\grid\DataColumn',
        'format' => 'raw',
        'value' => function ($model) {
            return $model->more_auto ? 'Да' : 'Нет';
        },
    ],
    'client_name'
];
if (Yii::$app->user->isAdmin) {
    $columns[] = [
        'label' => 'Дилер',
        'class' => 'yii\grid\DataColumn',
        'format' => 'raw',
        'value' => function ($model) {
            return $model->company->name;
        },
    ];
}
if (Yii::$app->user->isAdmin || Yii::$app->user->isLeadManager) {
    $columns[] = [
        'label' => 'Сотрудник',
        'class' => 'yii\grid\DataColumn',
        'format' => 'raw',
        'value' => function ($model) {
            return $model->user->username;
        },
    ];
}
$columns[] = ['class' => 'yii\grid\ActionColumn'];
?>
<div class="task-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php if (!Yii::$app->user->isAdmin): ?>
        <p>
            <?= Html::a('Добавить задачу', ['create'], ['class' => 'btn btn-success']) ?>
        </p>
    <?php endif ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => $columns,
        'rowOptions' => function ($model) {
            if ($model->ordered >= $model->amount) {
                return ['class' => 'green'];
            }
        },
    ]); ?>
</div>
