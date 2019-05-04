<?php

/* @var $model app\modules\knowledge_base\models\KnowledgeBase */

use yii\helpers\Html;
use yii\bootstrap\Modal;
use yii\bootstrap\Button;
use yii\widgets\ActiveForm;

Modal::begin([
    'id' => 'removeMyKnowledgeBaseModalForm',
    'header' => '<h3>' . Yii::t('app', 'KNOWLEDGE_BASES_PAGE_DELETE_KNOWLEDGE_BASE') . '</h3>',
]); ?>

    <div class="modal-body">
        <p style="font-size: 14px">
            <?php echo Yii::t('app', 'KNOWLEDGE_BASES_PAGE_MODAL_FORM_TEXT'); ?>
        </p>
    </div>

<?php $form = ActiveForm::begin([
    'id' => 'delete-my-knowledge-base-form',
    'method' => 'post',
    'action' => ['/my-knowledge-bases/delete/' . $model->id],
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