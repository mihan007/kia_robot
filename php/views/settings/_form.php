<?php
/* @var $this yii\web\View */

/* @var $company \app\models\Company */

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\bootstrap\Alert;

?>

<?php
$this->title = 'Настройки';
$this->params['breadcrumbs'][] = $company->name;
$this->params['breadcrumbs'][] = $this->title;
?>
<h2>Настройки</h2>
<div class="settings-form">
    <?php $form = ActiveForm::begin(); ?>

    <div class="form-group">
        <label>Логин от сайта Киа</label>
        <?php echo Html::textInput('kia_login', $company->kia_login, ['class' => 'form-control']); ?>
    </div>

    <div class="form-group">
        <label>Пароль от сайта Киа</label>
        <?php echo Html::passwordInput('kia_password', $company->kia_password, ['class' => 'form-control']); ?>
    </div>

    <div class="form-group">
        <label>Email для уведомлений(можно несколько через запятую)</label>
        <?php echo Html::textInput('email', $company->notification_email, ['class' => 'form-control']); ?>
    </div>

    <div class="form-group">
        <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
