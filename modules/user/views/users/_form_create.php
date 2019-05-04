<?php

/* @var $this yii\web\View */
/* @var $model app\modules\user\models\User */
/* @var $form yii\widgets\ActiveForm */

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\modules\user\models\User;
?>

<div class="create-user-form">

    <?php $form = ActiveForm::begin([
        'enableAjaxValidation' => true,
        'enableClientValidation' => true
    ]); ?>

    <?= $form->errorSummary($model); ?>

    <?= $form->field($model, 'username')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'password')->passwordInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'first_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'last_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'middle_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'email')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'status')->dropDownList(User::getStatusesArray()) ?>

    <?= $form->field($model, 'role')->dropDownList(User::getRolesArray()) ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'BUTTON_CREATE'),
            ['class' => 'btn btn-success', 'name'=>'create-user-button']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
