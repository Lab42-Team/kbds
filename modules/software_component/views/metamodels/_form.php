<?php

/* @var $this yii\web\View */
/* @var $model app\modules\software_component\models\Metamodel */
/* @var $form yii\widgets\ActiveForm */

use yii\helpers\Html;
use yii\widgets\ActiveForm;
?>

<div class="metamodel-form">

    <?php $form = ActiveForm::begin([
        'id' => $model->isNewRecord ? 'create-metamodel-form' : 'update-metamodel-form',
    ]); ?>

    <?= $form->errorSummary($model); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'description')->textarea(['maxlength' => true, 'rows'=>6]) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'BUTTON_CREATE') : Yii::t('app', 'BUTTON_UPDATE'),
            ['class' => 'btn btn-success', 'name'=>$model->isNewRecord ? 'create-metamodel-button' :
                'update-metamodel-button']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
