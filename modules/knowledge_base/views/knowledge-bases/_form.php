<?php

/* @var $this yii\web\View */
/* @var $model app\modules\knowledge_base\models\KnowledgeBase */
/* @var $form yii\widgets\ActiveForm */

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\modules\knowledge_base\models\KnowledgeBase;
use app\modules\knowledge_base\models\SubjectDomain;
?>

<div class="knowledge-base-form">

    <?php $form = ActiveForm::begin([
        'id' => $model->isNewRecord ? 'create-knowledge-base-form' : 'update-knowledge-base-form',
    ]); ?>

    <?= $form->errorSummary($model); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'subject_domain')->dropDownList(SubjectDomain::getAllSubjectDomainsArray()) ?>

    <?= $form->field($model, 'type')->dropDownList(KnowledgeBase::getTypesArray()) ?>

    <?= $form->field($model, 'status')->dropDownList(KnowledgeBase::getStatusesArray()) ?>

    <?= $form->field($model, 'description')->textarea(['maxlength' => true, 'rows'=>6]) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'BUTTON_CREATE') : Yii::t('app', 'BUTTON_UPDATE'),
            ['class' => 'btn btn-success', 'name'=>$model->isNewRecord ? 'create-knowledge-base-button' :
                'update-knowledge-base-button']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
