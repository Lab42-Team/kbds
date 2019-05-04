<?php

/* @var $this yii\web\View */
/* @var $model app\modules\software_component\models\TransformationModel */
/* @var $form yii\widgets\ActiveForm */

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\modules\software_component\models\Metamodel;
use app\modules\software_component\models\SoftwareComponent;
?>

<script type="text/javascript">
    // Действие при загрузки страницы
    $(document).ready(function() {
        // Скрытие слоя дополнительного (вычисляемого) поля (список всех типов программных компонентов)
        $(".field-transformationmodel-additional_field").hide();
        // Скрытие слоя скрытого поля исходной метамодели
        $(".field-transformationmodel-source_metamodel").hide();
        // Скрытие слоя скрытого поля целевой метамодели
        $(".field-transformationmodel-target_metamodel").hide();
        // Дополнительное (вычисляемое) поле (список всех типов программных компонентов)
        var select = document.getElementById("transformationmodel-additional_field");
        // Дополнительное (вычисляемое) поле (список всех исходных метамоделей)
        var source_metamodel_list = document.getElementById("transformationmodel-source_metamodel_name");
        // Дополнительное (вычисляемое) поле (список всех целевых метамоделей)
        var target_metamodel_list = document.getElementById("transformationmodel-target_metamodel_name");

        // Текущий выбранный программный компонент
        var current_value = $("#transformationmodel-software_component :selected").val();
        // Обход списка id всех программных компонентов
        for (var i = 0; i < select.options.length; i++) {
            if (select.options[i].value == current_value) {
                select.options[i].selected = true;
                // Текущее значение id выбранного программного компонента
                var current_id = $("#transformationmodel-additional_field :selected").text();
            }
        }
        // Скрытие метамоделей в списке исходных и целевых метамоделей
        if (current_value == '') {
            for (var j = 1; j < source_metamodel_list.options.length; j++)
                source_metamodel_list.options[j].setAttribute('disabled', true);
            for (var k = 1; k < target_metamodel_list.options.length; k++)
                target_metamodel_list.options[k].setAttribute('disabled', true);
        } else {
            // Скрытие метамоделей по умолчанию в списке всех исходных метамоделей
            source_metamodel_list.options[1].setAttribute('disabled', true);
            source_metamodel_list.options[2].setAttribute('disabled', true);
            source_metamodel_list.options[3].setAttribute('disabled', true);
            source_metamodel_list.options[4].setAttribute('disabled', true);
        }
        // Подстановка соответствующих значений id в дополнительные поля исходной и целевой метамодели
        if (current_id == "<?= SoftwareComponent::TYPE_INTEGRATED_ONT_ANALYSIS_COMPONENT ?>") {
            $("#transformationmodel-source_metamodel").val(<?= $model->source_metamodel ?>);
            $("#transformationmodel-target_metamodel").val(1);
            $("#transformationmodel-source_metamodel_name").val(<?= $model->source_metamodel ?>);
            $("#transformationmodel-target_metamodel_name").val(1);
            source_metamodel_list.disabled = false;
            target_metamodel_list.disabled = true;
        }
        if (current_id == "<?= SoftwareComponent::TYPE_INTEGRATED_RULE_ANALYSIS_COMPONENT ?>") {
            $("#transformationmodel-source_metamodel").val(<?= $model->source_metamodel ?>);
            $("#transformationmodel-target_metamodel").val(2);
            $("#transformationmodel-source_metamodel_name").val(<?= $model->source_metamodel ?>);
            $("#transformationmodel-target_metamodel_name").val(2);
            source_metamodel_list.disabled = false;
            target_metamodel_list.disabled = true;
        }
        if (current_id == "<?= SoftwareComponent::TYPE_INTEGRATED_OWL_GENERATION_COMPONENT ?>") {
            $("#transformationmodel-source_metamodel").val(1);
            $("#transformationmodel-target_metamodel").val(4);
            $("#transformationmodel-source_metamodel_name").val(1);
            $("#transformationmodel-target_metamodel_name").val(4);
            source_metamodel_list.disabled = true;
            target_metamodel_list.disabled = true;
        }
        if (current_id == "<?= SoftwareComponent::TYPE_INTEGRATED_CLIPS_GENERATION_COMPONENT ?>") {
            $("#transformationmodel-source_metamodel").val(2);
            $("#transformationmodel-target_metamodel").val(3);
            $("#transformationmodel-source_metamodel_name").val(2);
            $("#transformationmodel-target_metamodel_name").val(3);
            source_metamodel_list.disabled = true;
            target_metamodel_list.disabled = true;
        }
        if (current_id == "<?= SoftwareComponent::TYPE_AUTONOMOUS_OWL_GENERATION_COMPONENT ?>") {
            $("#transformationmodel-source_metamodel").val(<?= $model->source_metamodel ?>);
            $("#transformationmodel-target_metamodel").val(4);
            $("#transformationmodel-source_metamodel_name").val(<?= $model->source_metamodel ?>);
            $("#transformationmodel-target_metamodel_name").val(4);
            source_metamodel_list.disabled = false;
            target_metamodel_list.disabled = true;
        }
        if (current_id == "<?= SoftwareComponent::TYPE_AUTONOMOUS_CLIPS_GENERATION_COMPONENT ?>") {
            $("#transformationmodel-source_metamodel").val(<?= $model->source_metamodel ?>);
            $("#transformationmodel-target_metamodel").val(3);
            $("#transformationmodel-source_metamodel_name").val(<?= $model->source_metamodel ?>);
            $("#transformationmodel-target_metamodel_name").val(3);
            source_metamodel_list.disabled = false;
            target_metamodel_list.disabled = true;
        }
        // Действие при выборе программного компонента
        $("#transformationmodel-software_component").change(function() {
            var value = $("#transformationmodel-software_component :selected").val();
            for (var i = 0; i < select.options.length; i++) {
                if (select.options[i].value == value) {
                    select.options[i].selected = true;
                    var id = $("#transformationmodel-additional_field :selected").text();
                }
            }
            // Удаление значений полей, если программный компонент не выбран
            if (value == '') {
                $("#transformationmodel-source_metamodel").val('');
                $("#transformationmodel-target_metamodel").val('');
                $("#transformationmodel-source_metamodel_name").val('');
                $("#transformationmodel-target_metamodel_name").val('');
                source_metamodel_list.disabled = false;
                target_metamodel_list.disabled = false;
                // Скрытие метамоделей в списке исходных и целевых метамоделей
                for (var j = 1; j < source_metamodel_list.options.length; j++)
                    source_metamodel_list.options[j].setAttribute('disabled', true);
                for (var k = 1; k < target_metamodel_list.options.length; k++)
                    target_metamodel_list.options[k].setAttribute('disabled', true);
            } else {
                // Отображение всех метамоделей в списке всех исходных метамоделей
                for (var n = 1; n < source_metamodel_list.options.length; n++)
                    source_metamodel_list.options[n].removeAttribute('disabled');
                // Скрытие метамоделей по умолчанию в списке всех исходных метамоделей
                source_metamodel_list.options[1].setAttribute('disabled', true);
                source_metamodel_list.options[2].setAttribute('disabled', true);
                source_metamodel_list.options[3].setAttribute('disabled', true);
                source_metamodel_list.options[4].setAttribute('disabled', true);
            }
            // Подстановка соответствующих значений id в дополнительные поля исходной и целевой метамодели
            if (id == "<?= SoftwareComponent::TYPE_INTEGRATED_ONT_ANALYSIS_COMPONENT ?>") {
                $("#transformationmodel-source_metamodel").val('');
                $("#transformationmodel-target_metamodel").val(1);
                $("#transformationmodel-source_metamodel_name").val('');
                $("#transformationmodel-target_metamodel_name").val(1);
                source_metamodel_list.disabled = false;
                target_metamodel_list.disabled = true;
            }
            if (id == "<?= SoftwareComponent::TYPE_INTEGRATED_RULE_ANALYSIS_COMPONENT ?>") {
                $("#transformationmodel-source_metamodel").val('');
                $("#transformationmodel-target_metamodel").val(2);
                $("#transformationmodel-source_metamodel_name").val('');
                $("#transformationmodel-target_metamodel_name").val(2);
                source_metamodel_list.disabled = false;
                target_metamodel_list.disabled = true;
            }
            if (id == "<?= SoftwareComponent::TYPE_INTEGRATED_OWL_GENERATION_COMPONENT ?>") {
                $("#transformationmodel-source_metamodel").val(1);
                $("#transformationmodel-target_metamodel").val(4);
                $("#transformationmodel-source_metamodel_name").val(1);
                $("#transformationmodel-target_metamodel_name").val(4);
                source_metamodel_list.disabled = true;
                target_metamodel_list.disabled = true;
            }
            if (id == "<?= SoftwareComponent::TYPE_INTEGRATED_CLIPS_GENERATION_COMPONENT ?>") {
                $("#transformationmodel-source_metamodel").val(2);
                $("#transformationmodel-target_metamodel").val(3);
                $("#transformationmodel-source_metamodel_name").val(2);
                $("#transformationmodel-target_metamodel_name").val(3);
                source_metamodel_list.disabled = true;
                target_metamodel_list.disabled = true;
            }
            if (id == "<?= SoftwareComponent::TYPE_AUTONOMOUS_OWL_GENERATION_COMPONENT ?>") {
                $("#transformationmodel-source_metamodel").val('');
                $("#transformationmodel-target_metamodel").val(4);
                $("#transformationmodel-source_metamodel_name").val('');
                $("#transformationmodel-target_metamodel_name").val(4);
                source_metamodel_list.disabled = false;
                target_metamodel_list.disabled = true;
            }
            if (id == "<?= SoftwareComponent::TYPE_AUTONOMOUS_CLIPS_GENERATION_COMPONENT ?>") {
                $("#transformationmodel-source_metamodel").val('');
                $("#transformationmodel-target_metamodel").val(3);
                $("#transformationmodel-source_metamodel_name").val('');
                $("#transformationmodel-target_metamodel_name").val(3);
                source_metamodel_list.disabled = false;
                target_metamodel_list.disabled = true;
            }
        });
        // Действие при выборе исходной метамодели
        $("#transformationmodel-source_metamodel_name").change(function() {
            // Подстановка id исходной метамодели в скрытое поля
            var current_id = $("#transformationmodel-source_metamodel_name :selected").val();
            $("#transformationmodel-source_metamodel").val(current_id);
        });
    });
</script>

<div class="transformation-model-form">

    <?php $form = ActiveForm::begin([
        'id' => $model->isNewRecord ? 'create-transformation-model-form' : 'update-transformation-model-form',
    ]); ?>

    <?= $form->errorSummary($model); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'software_component')->dropDownList(
        $model->isNewRecord ? SoftwareComponent::getAllDesignSoftwareComponentsArray() :
            SoftwareComponent::getAllSoftwareComponentsArray(),
        ['prompt' => Yii::t('app', 'TRANSFORMATION_MODEL_NOT_SELECTED_SOFTWARE_COMPONENT')]
    ) ?>

    <?= $form->field($model, 'additional_field')->dropDownList(
        SoftwareComponent::getAllSoftwareComponentTypesArray(),
        ['prompt' => Yii::t('app', 'TRANSFORMATION_MODEL_NOT_SELECTED_SOFTWARE_COMPONENT')]
    ) ?>

    <?= $form->field($model, 'source_metamodel_name')->dropDownList(
        Metamodel::getAllMetamodelsArray(),
        ['prompt' => Yii::t('app', 'TRANSFORMATION_MODEL_NOT_SELECTED_SOURCE_METAMODEL')]
    ) ?>

    <?= $form->field($model, 'target_metamodel_name')->dropDownList(
        Metamodel::getAllMetamodelsArray(),
        ['prompt' => Yii::t('app', 'TRANSFORMATION_MODEL_NOT_SELECTED_TARGET_METAMODEL')]
    ) ?>

    <h4><span class="label label-info"><?= Yii::t('app', 'TRANSFORMATION_MODEL_METAMODELS_NOTICE') ?></span></h4>

    <?= $form->field($model, 'source_metamodel')->hiddenInput() ?>

    <?= $form->field($model, 'target_metamodel')->hiddenInput() ?>

    <?= $form->field($model, 'description')->textarea(['maxlength' => true, 'rows'=>6]) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'BUTTON_CREATE') : Yii::t('app', 'BUTTON_UPDATE'),
            ['id' => 'create-transformation-model-button', 'class' => 'btn btn-success',
                'name'=>$model->isNewRecord ? 'create-transformation-model-button' :
                    'update-transformation-model-button']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>