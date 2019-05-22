<?php
/**
 * @var array $result
 */

use yii\helpers\Url;

?>
<?php foreach ($result as $item): ?>
    <?php $mainTask = $item['task']; ?>
  <h2>Задача #<?php echo $mainTask->id ?> от <?= Yii::$app->formatter->format($mainTask->created_at, 'datetime') ?></h2>
  <ul>
    <li><b>Компания:</b>&nbsp;<?= $mainTask->company->name ?></li>
    <li><b>Модель:</b>&nbsp;<?= $mainTask->model_name ?></li>
    <li><b>Код производителя:</b>&nbsp;<?= $mainTask->manufacture_code_name ?></li>
    <li><b>Цвет Салон:</b>&nbsp;<?= $mainTask->color_inside_name ?></li>
    <li><b>Цвет Кузов:</b>&nbsp;<?= $mainTask->color_outside_name ?></li>
    <li><b>План, шт.:</b>&nbsp;<?= $mainTask->amount ?></li>
    <li><b>Факт, шт.:</b>&nbsp;<?= $mainTask->getOrdered() ?></li>
    <li><b>Просмотр:</b> <?= Url::to(['task/view', 'id' => $mainTask->id]) ?></li>
  </ul>
  <table align="center" border="1">
    <tr valign="middle">
      <td align="center">Дата</td>
      <td align="center">Доступно</td>
      <td align="center">Зарезервировано</td>
      <td align="center">Модель</td>
      <td align="center">Код производителя</td>
      <td align="center">Цвет Салон</td>
      <td align="center">Цвет Кузов</td>
      <td align="center">Год</td>
      <td align="center">Код склада</td>
      <td align="center">Страница</td>
    </tr>
      <?php foreach ($item['storageItems'] as $storageItem): ?>
        <tr valign="middle">
          <td align="center"><?= Yii::$app->formatter->asDatetime($storageItem->created_at) ?></td>
          <td align="center"><?= $storageItem->available ?></td>
          <td align="center"><?= $storageItem->reserved ?></td>
          <td align="center"><?= $storageItem->model ?></td>
          <td align="center"><?= $storageItem->manufacture_code ?></td>
          <td align="center"><?= $storageItem->color_inside ?></td>
          <td align="center"><?= $storageItem->color_outside ?></td>
          <td align="center"><?= $storageItem->year ?></td>
          <td align="center"><?= $storageItem->storage_code ?></td>
          <td align="center"><?= $storageItem->page ?></td>
        </tr>
      <?php endforeach ?>
  </table>
  <hr>
<?php endforeach; ?>
