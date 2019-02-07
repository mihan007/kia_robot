<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $company app\models\Company */

$this->title = 'Приоритет цветов';
$this->params['breadcrumbs'][] = $company->name;
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="color-preferences-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php if (!Yii::$app->user->isAdmin): ?>
        <p>
            <?= Html::a('Установить приоритеты цветов', ['create'], ['class' => 'btn btn-success']) ?>
        </p>
    <?php endif ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            [
                'attribute' => 'model_name',
                'headerOptions' => ['style' => 'width:20%'],
            ],
            'colorsReadable'
        ],
    ]); ?>
</div>
