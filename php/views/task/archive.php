<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Архив задач';
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
    [
        'label' => 'Дата удаления',
        'class' => 'yii\grid\DataColumn',
        'format' => 'raw',
        'value' => function ($data) {
            return Yii::$app->formatter->format($data->deleted_at, 'datetime');
        },
    ],
    'goalLabel',
    'model_name',
    'manufacture_code_name',
    'color_inside_name',
    'color_outside_name',
    'moreAutoLabel',
    'amount',
    'ordered',
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
$columns[] = [
    'class' => 'yii\grid\ActionColumn',
    'template' => '{view}',
];
?>
<div class="task-index">

    <h1><?= Html::encode($this->title) ?></h1>

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
