<?php

/* @var $this yii\web\View */
/* @var $model app\modules\software_component\models\SoftwareComponent */
/* @var $form yii\widgets\ActiveForm */

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\modules\software_component\models\SoftwareComponent;
?>

<div class="software-component-form">

    <?php $form = ActiveForm::begin([
        'id' => $model->isNewRecord ? 'create-software-component-form' : 'update-software-component-form',
    ]); ?>

    <?= $form->errorSummary($model); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?php if ($model->type == SoftwareComponent::TYPE_INTEGRATED_CLIPS_GENERATION_COMPONENT ||
              $model->type == SoftwareComponent::TYPE_INTEGRATED_OWL_GENERATION_COMPONENT): ?>
        <?= $form->field($model, 'type')->dropDownList(SoftwareComponent::getAllTypesArray(),
            ['disabled'=>'disabled']) ?>
        <?= $form->field($model, 'status')->dropDownList(SoftwareComponent::getStatusesArray(),
            ['disabled'=>'disabled']) ?>
    <?php endif; ?>

    <?php if ($model->type != SoftwareComponent::TYPE_INTEGRATED_CLIPS_GENERATION_COMPONENT &&
              $model->type != SoftwareComponent::TYPE_INTEGRATED_OWL_GENERATION_COMPONENT): ?>
        <?= $form->field($model, 'type')->dropDownList(SoftwareComponent::getTypesArray()) ?>
        <?= $model->isNewRecord ? '' :
            $form->field($model, 'status')->dropDownList(SoftwareComponent::getStatusesArray()) ?>
    <?php endif; ?>

    <?= $form->field($model, 'description')->textarea(['maxlength' => true, 'rows'=>6]) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'BUTTON_CREATE') : Yii::t('app', 'BUTTON_UPDATE'),
            ['class' => 'btn btn-success', 'name'=>$model->isNewRecord ? 'create-software-component-button' :
                'update-software-component-button']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
