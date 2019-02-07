<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Дилеры';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="company-index">

    <h2>Пользователи</h2>

    <p>
        <?= Html::a('Добавить пользователя', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'username',
            'email',
            'createdAt',
            'role',

            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{update}{delete}',
                'urlCreator' => function ($action, $model, $key, $index) {
                    if ($action === 'update') {
                        $url ='index.php?r=user/update&id='.$model->id;
                        return $url;
                    }
                    if ($action === 'delete') {
                        $url ='index.php?r=user/delete&id='.$model->id;
                        return $url;
                    }
                }
            ],
        ],
    ]); ?>
</div>
