<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Запуск задач';
$this->params['breadcrumbs'][] = $this->title;
$columns = [
    [
        'label' => 'Дата завершения',
        'class' => 'yii\grid\DataColumn',
        'format' => 'raw',
        'value' => function ($data) {
            return Yii::$app->formatter->asDatetime($data->created_at);
        },
    ],
    'model_name',
    'manufacture_code_name',
    'color_inside_name',
    'color_outside_name',
    'amount',
    'amount_ordered',
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

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => $columns,
    ]); ?>
</div>
