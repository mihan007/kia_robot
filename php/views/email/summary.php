<?php
/**
 * @var $date
 * @var $data
 * @var $greatTotal
 */
?>
<style>
    th {
        font-weight: bold;
    }
</style>
<h1>Отчет о заказанных авто за <?php echo $date ?></h1>
<?php if (sizeof($data) > 0): ?>
    <table border="1">
        <thead>
        <tr>
            <th>Дата создания заказа</th>
            <th>ID заказа</th>
            <th>Заказ</th>
            <th>Количество запусков</th>
            <th>Заказано авто, шт</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($data as $taskInfo): ?>
        <tr valign="middle">
            <td><?php echo $taskInfo['create_date'] ?></td>
            <td><?php echo $taskInfo['id'] ?></td>
            <td><?php echo $taskInfo['description'] ?></td>
            <td align="center"><?php echo $taskInfo['count'] ?></td>
            <td align="center"><?php echo $taskInfo['total'] ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" align="left"><b>Итого</b></td>
                <td align="center"><b><?php echo $greatTotal ?></b></td>
            </tr>
        </tfoot>
    </table>
<?php else: ?>
    Ничего не заказывали
<?php endif; ?>
