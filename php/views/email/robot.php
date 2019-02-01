<?php
/**
 * @var \app\models\TaskRun[] $taskRuns
 */
use yii\helpers\Url;
?>

<?php foreach ($taskRuns as $taskRun): ?>
    <h2>Задача #<?php echo $taskRun->task->id ?> от <?= Yii::$app->formatter->format($taskRun->task->created_at, 'datetime') ?></h2>
    <h3>Запуск от <?= Yii::$app->formatter->format(strtotime($taskRun->created_at)-3*3600, 'datetime') ?></h2></h3>
    <ul>
        <li><b>Модель:</b>&nbsp;<?= $taskRun->model_name ?></li>
        <li><b>Код производителя:</b>&nbsp;<?= $taskRun->manufacture_code_name ?></li>
        <li><b>Цвет Салон:</b>&nbsp;<?= $taskRun->color_inside_name ?></li>
        <li><b>Цвет Кузов:</b>&nbsp;<?= $taskRun->color_outside_name ?></li>
        <li><b>План, шт.:</b>&nbsp;<?= $taskRun->amount ?></li>
        <li><b>Факт, шт.:</b>&nbsp;<?= $taskRun->amount_ordered ?></li>
    </ul>
    <?php $url = Url::to(['task-run/view', 'id' => $taskRun->id]); ?>
    <?= "Детали со скриншотами можно увидеть по ссылке: <a href='$url'>$url</a>" ?>
    <hr>
<?php endforeach; ?>
