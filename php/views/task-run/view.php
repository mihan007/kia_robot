<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $task app\models\Task */
/* @var $taskRun app\models\TaskRun */

$this->title = 'Запуск задачи #' . $task->id . " от " . Yii::$app->formatter->format($task->created_at, 'datetime');
if ($task->deleted_at == null) {
    $this->params['breadcrumbs'][] = ['label' => 'Текущие задачи', 'url' => ['index']];
} else {
    $this->params['breadcrumbs'][] = ['label' => 'Архив задач', 'url' => ['archive']];
}

$this->params['breadcrumbs'][] = ['label' => 'Задача #' . $task->id, 'url' => ['task/view', 'id' => $task->id]];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
$items = [];
foreach ($taskRun->taskRunScreenshots as $screenshot) {
    $screenshotPos = strpos($screenshot->filepath, 'screenshots');
    $relativePath = substr($screenshot->filepath, $screenshotPos);
    $normalizedUrl = Yii::$app->params['domainMain']."/".$relativePath;
    $item = [
        'url' => $normalizedUrl,
        'src' => $normalizedUrl,
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
                'label' => 'Дата старта',
                'class' => 'yii\grid\DataColumn',
                'format' => 'raw',
                'value' => function ($data) {
                    return Yii::$app->formatter->asDatetime($data->started_at);
                },
            ],
            [
                'label' => 'Дата завершения',
                'class' => 'yii\grid\DataColumn',
                'format' => 'raw',
                'value' => function ($data) {
                    return Yii::$app->formatter->asDatetime($data->finished_at);
                },
            ],
            'task.goalLabel',
            'model_name',
            'manufacture_code_name',
            'color_inside_name',
            'color_outside_name',
            'task.moreAutoLabel',
            'amount',
            'amount_ordered'
        ],
    ]) ?>

    <?php if ($taskRun->tooOld): ?>
        <b>Скриншоты доступны только в течении недели</b>
    <?php else: ?>
        <?= dosamigos\gallery\Carousel::widget(['items' => $items]); ?>
    <?php endif ?>

    <h2>Лог</h2>

    <?php echo $taskRun->description ?>

</div>
