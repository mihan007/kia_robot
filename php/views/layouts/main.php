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
        if (\Yii::$app->user->isAdmin || \Yii::$app->user->isLeadManager) {
            $items[] = ['label' => 'Настройки', 'url' => ['/settings/index']];
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
        <?php if ((!Yii::$app->user->isGuest) && (Yii::$app->user->isLeadManager) && (Yii::$app->user->company->banned_at > 0)): ?>
            <?php echo \yii\bootstrap\Alert::widget([
                'options' => [
                    'class' => 'alert-danger',
                ],
                'body' => "Выполнение ваших задач приостановлено в связи с тем, что текущие логин/пароль к сайту Киа не подходят. 
                           <a href='/index.php?r=settings%2Findex'>Отредактировать</a>",
            ]); ?>
        <?php endif ?>
        <?php if ((!Yii::$app->user->isGuest) && (Yii::$app->user->isManager) && (Yii::$app->user->company->banned_at > 0)): ?>
            <?php echo \yii\bootstrap\Alert::widget([
                'options' => [
                    'class' => 'alert-danger',
                ],
                'body' => "Выполнение ваших задач приостановлено в связи с тем, что текущие логин/пароль к сайту Киа не подходят. 
                           Обратитесь к руководителю для смены логина/пароля к сайту Киа.</a>",
            ]); ?>
        <?php endif ?>
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
