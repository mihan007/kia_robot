<?php

use yii\helpers\Url;

/* @var $this yii\web\View */

$this->title = 'Робот для Киа';
?>
<div class="site-index">

    <div class="jumbotron">
        <h1>Умеет заказывать</h1>
        <?php if (Yii::$app->user->isGuest): ?>
            <p><a class="btn btn-lg btn-success" href="<?php echo Url::to(['/site/login']) ?>">Войти</a></p>
        <?php else: ?>
            <p><a class="btn btn-lg btn-success" href="<?php echo Url::to(['/task/index']) ?>">Перейти к текущим задачам</a></p>
        <?php endif ?>
    </div>
</div>
