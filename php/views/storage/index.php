<?php

/* @var $this yii\web\View */

use yii\data\ActiveDataProvider;
use yii\web\View;
use \kartik\daterange\DateRangePicker;

/* @var $dataProvider yii\data\ActiveDataProvider */

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