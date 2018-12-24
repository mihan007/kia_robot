<?php
/**
 * Created by PhpStorm.
 * User: mihan007
 * Date: 2018-12-24
 * Time: 12:58
 *
 * @var \app\models\Model[] $models
 * @var string $header
 */
?>
<h1><?php echo $header ?></h1>
<?php foreach ($models as $model): ?>
    <h2>Модель <?= $model->name ?><?= (strlen($model->value)>0) ? "&nbsp;(" . $model->value . ")" : "" ?></h2>
    <h3>Код производителя:</h3>
    <ol>
        <?php foreach ($model->manufactureCodes as $manufactureCode): ?>
            <li><?= $manufactureCode->name ?><?= (strlen($manufactureCode->value)>0) ? "&nbsp;(".$manufactureCode->value.")" : "" ?></li>
        <?php endforeach; ?>
    </ol>
    <h3>Цвет салона:</h3>
    <ol>
        <?php foreach ($model->colorInsides as $colorInside): ?>
            <li><?= $colorInside->name ?><?= (strlen($colorInside->value)>0) ? "&nbsp;(".$colorInside->value.")" : "" ?></li>
        <?php endforeach; ?>
    </ol>
    <h3>Цвет кузова:</h3>
    <ol>
        <?php foreach ($model->colorOutsides as $colorOutside): ?>
            <li><?= $colorOutside->name ?><?= (strlen($colorOutside->value)>0) ? "&nbsp;(".$colorOutside->value.")" : "" ?></li>
        <?php endforeach; ?>
    </ol>
<?php endforeach; ?>
