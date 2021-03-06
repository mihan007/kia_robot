<?php
/**
 * @var \app\models\Company $company
 */
?>
<p>При попытке входа на портал Киа компанией <?= $company->name ?> текущие логин и пароль не подошли. Выполнение всех текущих задач приостановлено.</p>
<p>Вы можете указать новые логин и пароль компании в <a href="<?= Yii::$app->params['domainMain']?>/index.php?r=company/update&id=<?=$company->id ?>" ?>настройках системы</a>.</p>
<p>После указания новых логина и пароля выполнение задач будет возобновлено.</p>