<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $task app\models\Task */
/* @var $taskRun app\models\TaskRun */

$this->title = 'Запуск задачи #' . $task->id . " от " . Yii::$app->formatter->format($task->created_at, 'datetime');
$this->params['breadcrumbs'][] = ['label' => 'Текущие задачи', 'url' => ['task/index']];
$this->params['breadcrumbs'][] = ['label' => 'Задача #' . $task->id, 'url' => ['task/view', 'id' => $task->id]];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
$items = [];
foreach ($taskRun->taskRunScreenshots as $screenshot) {
    $item = [
        'url' => str_replace('../php/web', Yii::$app->params['domainMail'], $screenshot->filepath),
        'src' => str_replace('../php/web', Yii::$app->params['domainMail'], $screenshot->filepath),
        'options' => ['title' => $screenshot->name]
    ];
    $items[] = $item;
}
?>
<div class="task-run-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= DetailView::widget([
        'model' => $taskRun,
        'attributes' => [
            [
                'label' => 'Исходная задача',
                'class' => 'yii\grid\DataColumn',
                'format' => 'raw',
                'value' => function ($data) {
                    return Html::a('Задача #' . $data->task->id, ['task/view', 'id' => $data->task->id]);
                },
            ],
            [
                'label' => 'Дата запуска',
                'class' => 'yii\grid\DataColumn',
                'format' => 'raw',
                'value' => function ($data) {
                    return Yii::$app->formatter->format(strtotime($data->created_at), 'datetime');
                },
            ],
            'model_name',
            'manufacture_code_name',
            'color_inside_name',
            'color_outside_name',
            'amount'
        ],
    ]) ?>

    <?= dosamigos\gallery\Carousel::widget(['items' => $items]); ?>

    <h2>Лог</h2>

    <?php echo $taskRun->description ?>

</div>
