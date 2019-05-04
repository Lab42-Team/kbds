<?php

/* @var $model app\modules\software_component\models\Metamodel */

use yii\helpers\Html;
use yii\bootstrap\Modal;
use yii\bootstrap\Button;
use yii\widgets\ActiveForm;

/* Модальное окно удаления метамодели */
Modal::begin([
    'id' => 'removeMetamodelModalForm',
    'header' => '<h3>' . Yii::t('app', 'METAMODELS_PAGE_DELETE_METAMODEL') . '</h3>',
]); ?>

    <div class="modal-body">
        <p style="font-size: 14px">
            <?php echo Yii::t('app', 'METAMODELS_PAGE_MODAL_FORM_TEXT'); ?>
        </p>
    </div>

    <?php $form = ActiveForm::begin([
        'id' => 'delete-metamodel-form',
        'method' => 'post',
        'action' => ['/metamodels/delete/' . $model->id],
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