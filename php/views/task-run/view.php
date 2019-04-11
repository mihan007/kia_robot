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
    $normalizedUrl = str_replace('/var/www/html/turbo.prod/alarm-robot/js/', '', $screenshot->filepath);
    $normalizedUrl = str_replace('/Users/mihan007/Sites/alarm-robot/js/', '', $normalizedUrl);
    $normalizedUrl = str_replace('/Users/mihan007/Projects/alarm-robot/js/', '', $normalizedUrl);
    $normalizedUrl = str_replace('../php/web', Yii::$app->params['domainMain'], $normalizedUrl);
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
                'label' => 'Дата запуска',
                'class' => 'yii\grid\DataColumn',
                'format' => 'raw',
                'value' => function ($data) {
                    return Yii::$app->formatter->asDatetime(strtotime($data->created_at));
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

    <?= dosamigos\gallery\Carousel::widget(['items' => $items]); ?>

    <h2>Лог</h2>

    <?php echo $taskRun->description ?>

</div>
