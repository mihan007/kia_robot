<?php

/* @var $this yii\web\View */

use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\web\View;
use \kartik\daterange\DateRangePicker;

/* @var $dataProvider yii\data\ArrayDataProvider */
/* @var $start string */
/* @var $end string */
/* @var $searchModel array */
/* @var $filterItems array */

$this->title = 'Склад';
$this->params['breadcrumbs'][] = $this->title;

?>

<h1><?= $this->title ?></h1>
<div class="row row-conformity">
    <form method="post" action="/index.php?r=storage/proxy">
        <div class="col-lg-4">
            <?php
            echo '<label class="control-label">Период выбора авто со склада</label>';
            echo DateRangePicker::widget([
                'name' => 'period',
                'value' => $start . ' до ' . $end,
                'convertFormat' => true,
                'useWithAddon' => true,
                'presetDropdown' => true,
                'autoUpdateOnInit' => true,
                'pluginOptions' => [
                    'locale' => [
                        'format' => 'd.m.Y',
                        'separator' => ' до ',
                    ],
                    'opens' => 'left'
                ]
            ]);
            ?>
        </div>
        <div style="padding-top: 25px">
            <input type="submit" value="Показать" class="btn btn-success">
        </div>
    </form>
</div>
<h2>На складе</h2>
<div class="row">
    <div class="col-lg-12">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'columns' => [
                [
                    'label' => 'Добавлена',
                    'attribute' => 'created_at',
                    'format' => 'datetime'
                ],
                [
                    'label' => 'Модель',
                    'class' => 'yii\grid\DataColumn',
                    'format' => 'raw',
                    'filter' => Html::dropDownList('model', $searchModel['model'], $filterItems['model'],
                        ['class' => 'form-control']),
                    'value' => function ($data) {
                        return $data['model'];
                    },
                ],
                [
                    'label' => 'Код производителя',
                    'class' => 'yii\grid\DataColumn',
                    'format' => 'raw',
                    'filter' => Html::textInput('manufacture_code', $searchModel['manufacture_code'],
                        ['class' => 'form-control']),
                    'value' => function ($data) {
                        return $data['manufacture_code'];
                    },
                ],
                [
                    'label' => 'Описание',
                    'class' => 'yii\grid\DataColumn',
                    'format' => 'raw',
                    'filter' => Html::textInput('description', $searchModel['description'],
                        ['class' => 'form-control']),
                    'value' => function ($data) {
                        return $data['description'];
                    },
                ],
                [
                    'label' => 'Цвет кузова',
                    'class' => 'yii\grid\DataColumn',
                    'format' => 'raw',
                    'filter' => Html::dropDownList('color_outside', $searchModel['color_outside'],
                        $filterItems['color_outside'], ['class' => 'form-control']),
                    'value' => function ($data) {
                        return $data['color_outside'];
                    },
                ],
                [
                    'label' => 'Цвет салона',
                    'class' => 'yii\grid\DataColumn',
                    'format' => 'raw',
                    'filter' => Html::dropDownList('color_inside', $searchModel['color_inside'],
                        $filterItems['color_inside'], ['class' => 'form-control']),
                    'value' => function ($data) {
                        return $data['color_inside'];
                    },
                ],
                [
                    'label' => 'Год',
                    'class' => 'yii\grid\DataColumn',
                    'format' => 'raw',
                    'filter' => Html::dropDownList('year', $searchModel['year'], $filterItems['year'],
                        ['class' => 'form-control']),
                    'value' => function ($data) {
                        return $data['year'];
                    },
                ],
                [
                    'label' => 'Код склада',
                    'class' => 'yii\grid\DataColumn',
                    'format' => 'raw',
                    'filter' => Html::textInput('storage_code', $searchModel['storage_code'],
                        ['class' => 'form-control']),
                    'value' => function ($data) {
                        return $data['storage_code'];
                    },
                ]
            ],
        ]); ?>
    </div>
</div>