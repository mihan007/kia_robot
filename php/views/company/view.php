<?php

use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Company */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Дилеры', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="company-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Редактировать', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Удалить', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Вы действительно хотите удалить дилера?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'name',
            'statusLabel',
            'createdAt',
        ],
    ]) ?>

    <h2>Пользователи дилера</h2>

    <p>
        <?= Html::a('Добавить пользователя', ['create-user', 'company_id' => $model->id], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $usersProvider,
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
                        $url ='index.php?r=company/update-user&id='.$model->id;
                        return $url;
                    }
                    if ($action === 'delete') {
                        $url ='index.php?r=company/delete-user&id='.$model->id;
                        return $url;
                    }
                }
            ],
        ],
    ]); ?>

</div>
