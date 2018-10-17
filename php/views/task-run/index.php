<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Запуск задач';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="task-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            'id',
            [
                'label' => 'Дата завершения',
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
            'amount_ordered',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
</div>
