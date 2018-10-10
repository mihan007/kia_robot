<?php
use yii\helpers\Url;
?>

<?php foreach ($data as $item): ?>
    <?php if (sizeof($item['taskRuns']) > 0): ?>
        <h2>Задача #<?php echo $item['task']->id ?>
            от <?= Yii::$app->formatter->format($item['task']->created_at, 'datetime') ?></h2>
        <?php foreach ($item['taskRuns'] as $taskRun): ?>
            <h3>Запуск от <?= Yii::$app->formatter->format(strtotime($taskRun->created_at)-3*3600, 'datetime') ?></h2></h3>
            <?= $taskRun->description ?>
            <?php $url = Url::to(['task-run/view', 'id' => $taskRun->id]); ?>
            <?= "Детали со скриншотами можно увидеть по ссылке: <a href='$url'>$url</a>" ?>
        <?php endforeach; ?>
        <hr>
    <?php endif ?>
<?php endforeach; ?>
