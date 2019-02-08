<?php
/**
 * @var $result array
 */

use yii\helpers\Url;

?>
<?php foreach ($result as $similarGroup): ?>
    <hr>
    <?php
    /**
     * @var $mainTask app\models\Task
     */
    $mainTask = $similarGroup['main'];
    ?>
    <h2>Основная задача #<?php echo $mainTask->id ?> от
        <?= Yii::$app->formatter->format($mainTask->created_at, 'datetime') ?></h2>
    <ul>
        <li><b>Компания:</b>&nbsp;<?= $mainTask->company->name ?></li>
        <li><b>Модель:</b>&nbsp;<?= $mainTask->model_name ?></li>
        <li><b>Код производителя:</b>&nbsp;<?= $mainTask->manufacture_code_name ?></li>
        <li><b>Цвет Салон:</b>&nbsp;<?= $mainTask->color_inside_name ?></li>
        <li><b>Цвет Кузов:</b>&nbsp;<?= $mainTask->color_outside_name ?></li>
        <li><b>План, шт.:</b>&nbsp;<?= $mainTask->amount ?></li>
        <li><b>Факт, шт.:</b>&nbsp;<?= $mainTask->getAmountOrdered() ?></li>
        <li><b>Просмотр:</b> <?= Url::to(['task/view', 'id' => $mainTask->id]) ?></li>
    </ul>
    <h3>Похожие задачи</h3>
    <?php
    /**
     * @var $similarTask \app\models\Task
     */
    ?>
    <?php foreach ($similarGroup['similar'] as $i => $similarTask): ?>
        <?php $order = $i + 1; ?>
        <h4>Похожая задача №<?= $order ?>, id #<?php echo $similarTask->id ?> от
            <?= Yii::$app->formatter->format($similarTask->created_at, 'datetime') ?></h4>
        <ul>
            <li><b>Компания:</b>&nbsp;<?= $similarTask->company->name ?></li>
            <li><b>Модель:</b>&nbsp;<?= $similarTask->model_name ?></li>
            <li><b>Код производителя:</b>&nbsp;<?= $similarTask->manufacture_code_name ?></li>
            <li><b>Цвет Салон:</b>&nbsp;<?= $similarTask->color_inside_name ?></li>
            <li><b>Цвет Кузов:</b>&nbsp;<?= $similarTask->color_outside_name ?></li>
            <li><b>План, шт.:</b>&nbsp;<?= $similarTask->amount ?></li>
            <li><b>Факт, шт.:</b>&nbsp;<?= $similarTask->getAmountOrdered() ?></li>
            <li><b>Просмотр:</b> <?= Url::to(['task/view', 'id' => $similarTask->id]) ?></li>
        </ul>
    <?php endforeach ?>
<?php endforeach; ?>