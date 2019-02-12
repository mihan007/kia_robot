<?php
use yii\helpers\Html;
?>
<h1>Руководителю салона</h1>
<h2>Имеющиеся возможности</h2>
<p>На текущий момент робот поддерживает следующие возможности:</p>
<ul>
    <li><?= Html::a('Управление пользователями', ['user/index'], ['target' => '_blank']) ?></li>
    <li><?= Html::a('Управление задачами', ['task/index'], ['target' => '_blank']) ?></li>
    <li><?= Html::a('Управление приоритетами цветов', ['color-preferences/index'], ['target' => '_blank']) ?></li>
    <li><?= Html::a('Просмотр архива задач', ['task/archive'], ['target' => '_blank']) ?></li>
    <li><?= Html::a('Просмотр запусков задач', ['task-run/index'], ['target' => '_blank']) ?></li>
</ul>
<h3>Управление пользователями</h3>
<p>В данном разделе вы можете добавлять аккаунты для ваших коллег. После добавления коллеги передайте ему логин и пароль, который вы ввели.
    Используя этот логин и пароль, ваш коллега получит доступ к системе.</p>
<p>Если вы установили ему роль "Руководитель", то коллега как и вы
будет иметь возможность управлять пользователями и просматривать/редактировать все задачи всех пользователей салона. Пользователь с ролью "Менеджер"
имеет доступ только к своим задачам. Любой пользователь салона имеет доступ к разделу "Приоритеты цветов".</p>

<h3>Управление задачами</h3>
<p>В данном разделе вы можете добавить/редактировать/удалить любые задачи, созданные вами и вашими коллегами. Задача это набор фильтров на поиск авто с указанием количества авто, которое надо
заказать и применять ли алгоритм "Альтернативы". Детали обработки задач смотрите в разделе <?= Html::a("Как работает робот", ['/help/topic', 'topic' => 'robot']) ?></p>

<h3>Управление приоритетами цветов</h3>
<p>В данном разделе вы можете просмотреть/отредактировать приоритеты заказа цветов кузова для каждой модели. Приоритеты цветов задаются через запятую.
Приоритеты цветов учавствуют в выполнении задач по наполнению склада. Детали обработки задач с учетом приоритета цветов смотрите в разделе <?= Html::a("Как работает робот", ['/help/topic', 'topic' => 'robot']) ?></p>

<h3>Просмотр архива задач</h3>
<p>В данном разделе вы можете просмотреть все задачи, которые когда либо создавали в вашем салоне.</p>

<h3>Просмотр запусков задач</h3>
<p>В данном разделе вы можете просмотреть все запуски робота по выполнению задач. Удобно для просмотра активности робота в течении рабочего дня без привязки к конкретным задачам</p>