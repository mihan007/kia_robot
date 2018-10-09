<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use app\models\Model;
use kartik\depdrop\DepDrop;

/* @var $this yii\web\View */
/* @var $model app\models\Task */
/* @var $form yii\widgets\ActiveForm */
/* @var array $manufactureCodes */
/* @var array $colorsInside */
/* @var array $colorsOutside */

?>

    <div class="task-form">

        <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($model, 'model_name')->hiddenInput(['id' => 'model_name'])->label(false) ?>

        <?= $form->field($model, 'model_value')->dropDownList(ArrayHelper::map(Model::find()->all(), 'value', 'name'), [
            'id' => 'model_id',
            'prompt' => 'Выберите модель...'
        ]) ?>

        <?= $form->field($model, 'manufacture_code_name')->hiddenInput(['id' => 'manufacture_code_name'])->label(false) ?>

        <?= $form->field($model, 'manufacture_code_value')->dropDownList($manufactureCodes, [
            'id' => 'manufacture_code_id',
            'prompt' => 'Выберите код производителя...',
            'disabled' => $model->isNewRecord ? true : false
        ]) ?>

        <?= $form->field($model, 'color_inside_name')->hiddenInput(['id' => 'color_inside_name'])->label(false) ?>

        <?= $form->field($model, 'color_inside_value')->dropDownList($colorsInside, [
            'id' => 'color_inside_id',
            'prompt' => 'Выберите цвет салона...',
            'disabled' => $model->isNewRecord ? true : false
        ]) ?>

        <?= $form->field($model, 'color_outside_name')->hiddenInput(['id' => 'color_outside_name'])->label(false) ?>

        <?= $form->field($model, 'color_outside_value')->dropDownList($colorsOutside, [
            'id' => 'color_outside_id',
            'prompt' => 'Выберите цвет кузова...',
            'disabled' => $model->isNewRecord ? true : false
        ]) ?>

        <?= $form->field($model, 'amount')->textInput() ?>

        <div class="form-group">
            <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success']) ?>
        </div>

        <?php ActiveForm::end(); ?>

    </div>

<?php $this->registerJs("
    $('#model_id').on('change', function() {   
        $('#model_name').val($('#model_id option:selected').text());
        
        $.post('" . Url::to(['/task/manufactures']) . "', {model_id:$(this).val()})
            .done(function(response) {
                $('#manufacture_code_id').find('option').remove();
                $('#manufacture_code_id').removeAttr('disabled');
                var resp = JSON.parse(response);               
                for (var i in resp.output) {
                    $('#manufacture_code_id')
                        .append($('<option></option>').attr('value', resp.output[i].id).text(resp.output[i].name));
                }
                $('#manufacture_code_id').change();
            });
            
        $.post('" . Url::to(['/task/colors-inside']) . "', {model_id:$(this).val()})
            .done(function(response) {
                $('#color_inside_id').find('option').remove();
                $('#color_inside_id').removeAttr('disabled');
                var resp = JSON.parse(response);               
                for (var i in resp.output) {
                    $('#color_inside_id')
                        .append($('<option></option>').attr('value', resp.output[i].id).text(resp.output[i].name));
                }
                $('#color_inside_id').change();
            });
            
        $.post('" . Url::to(['/task/colors-outside']) . "', {model_id:$(this).val()})
            .done(function(response) {
                $('#color_outside_id').find('option').remove();
                $('#color_outside_id').removeAttr('disabled');
                var resp = JSON.parse(response);               
                for (var i in resp.output) {
                    $('#color_outside_id')
                        .append($('<option></option>').attr('value', resp.output[i].id).text(resp.output[i].name));
                }
                $('#color_outside_id').change();
            });
    });
    
    $('#manufacture_code_id').on('change', function() {
        $('#manufacture_code_name').val($('#manufacture_code_id option:selected').text());
    });
    
    $('#color_inside_id').on('change', function() {
        $('#color_inside_name').val($('#color_inside_id option:selected').text());        
    });
    
    $('#color_outside_id').on('change', function() {
        $('#color_outside_name').val($('#color_outside_id option:selected').text());        
    });
");