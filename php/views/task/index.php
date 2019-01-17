<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Текущие задачи';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="task-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Добавить задачу', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            'id',
            [
                'label' => 'Дата создания',
                'class' => 'yii\grid\DataColumn',
                'format' => 'raw',
                'value' => function ($data) {
                    return Yii::$app->formatter->format($data->created_at, 'datetime');
                },
            ],
            'model_name',
            'manufacture_code_name',
            'color_inside_name',
            'color_outside_name',
            'amount',
            [
                'label' => 'Альтернативы',
                'class' => 'yii\grid\DataColumn',
                'format' => 'raw',
                'value' => function ($model) {
                    return $model->more_auto ? 'Да' : 'Нет';
                },
            ],

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
</div>
