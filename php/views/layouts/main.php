<?php

/* @var $this \yii\web\View */
/* @var $content string */

use app\widgets\Alert;
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use app\assets\AppAsset;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php $this->registerCsrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>

<div class="wrap">
    <?php
    NavBar::begin([
        'brandLabel' => Yii::$app->name,
        'brandUrl' => Yii::$app->homeUrl,
        'options' => [
            'class' => 'navbar-inverse navbar-fixed-top',
        ],
    ]);
    $items = [];
    $items[] = ['label' => 'Домой', 'url' => ['/site/index']];
    if (!Yii::$app->user->isGuest) {
        if (\Yii::$app->user->can('manageCompany')) {
            $items[] = ['label' => 'Дилеры', 'url' => ['/company/index']];
        }
        $items[] = ['label' => 'Текущие задачи', 'url' => ['/task/index']];
        $items[] = ['label' => 'Архив задач', 'url' => ['/task/archive']];
        $items[] = ['label' => 'Запуски задач', 'url' => ['/task-run/index']];
        $items[] = ['label' => 'Приоритет цветов', 'url' => ['/color-preferences/index']];
        if (\Yii::$app->user->isLeadManager) {
            $items[] = ['label' => 'Сотрудники', 'url' => ['/user/index']];
        }
        $items[] = ['label' => 'Помощь', 'url' => ['/help/index']];
    }
    $items[] = Yii::$app->user->isGuest ?  ['label' => 'Войти', 'url' => ['/site/login']] :
                '<li>'
                . Html::beginForm(['/site/logout'], 'post')
                . Html::submitButton(
                    'Выйти (' . Yii::$app->user->identity->username . ')',
                    ['class' => 'btn btn-link logout']
                )
                . Html::endForm()
                . '</li>';
    echo Nav::widget([
        'options' => ['class' => 'navbar-nav navbar-right'],
        'items' => $items
    ]);
    NavBar::end();
    ?>

    <div class="container">
        <?= Breadcrumbs::widget([
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
        <?= Alert::widget() ?>
        <?= $content ?>
    </div>
</div>

<footer class="footer">
    <div class="container">
        <p class="pull-left">&copy; Турбодилер <?= date('Y') ?></p>
    </div>
</footer>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
