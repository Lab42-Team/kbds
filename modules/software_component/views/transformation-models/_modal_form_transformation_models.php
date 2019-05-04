<?php

/* @var $model app\modules\software_component\models\TransformationModel */

use yii\helpers\Html;
use yii\bootstrap\Modal;
use yii\bootstrap\Button;
use yii\widgets\ActiveForm;

Modal::begin([
    'id' => 'removeTransformationModelModalForm',
    'header' => '<h3>' . Yii::t('app', 'TRANSFORMATION_MODELS_PAGE_DELETE_TRANSFORMATION_MODEL') . '</h3>',
]); ?>

    <div class="modal-body">
        <p style="font-size: 14px">
            <?php echo Yii::t('app', 'TRANSFORMATION_MODELS_PAGE_MODAL_FORM_TEXT'); ?>
        </p>
    </div>

<?php $form = ActiveForm::begin([
    'id' => 'delete-transformation-model-form',
    'method' => 'post',
    'action' => ['/transformation-models/delete/' . $model->id],
    'enableAjaxValidation'=>true,
    'enableClientValidation'=>true,
]); ?>

<?= Html::submitButton(Yii::t('app', 'BUTTON_DELETE'), ['class' => 'btn btn-success']) ?>

<?= Button::widget([
    'label' => Yii::t('app', 'BUTTON_CANCEL'),
    'options' => [
        'class' => 'btn-danger',
        'style' => 'margin:5px',
        'data-dismiss'=>'modal'
    ]
]); ?>

<?php ActiveForm::end(); ?>

<?php Modal::end(); ?>