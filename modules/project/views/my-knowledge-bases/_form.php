<?php

/* @var $this yii\web\View */
/* @var $model app\modules\knowledge_base\models\KnowledgeBase */
/* @var $subject_domain_model app\modules\knowledge_base\models\SubjectDomain */
/* @var $form yii\widgets\ActiveForm */

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\modules\knowledge_base\models\KnowledgeBase;
use app\modules\knowledge_base\models\SubjectDomain;
?>

<?= $this->render('_modal_form_add_new_subject_domain', ['subject_domain_model' => $subject_domain_model]) ?>

<!-- Подключение скрипта для модальных форм -->
<?php $this->registerJsFile('/js/modal-form.js', ['position' => yii\web\View::POS_HEAD]) ?>

<script type="text/javascript">
    // Выполнение скрипта при загрузке страницы
    $(document).ready(function() {
        // Обработка нажатия ссылки добавления новой предметной области
        $("#add-new-subject-domain-link").click(function (e) {
            // Скрытие списка ошибок ввода в модальном окне
            $("#add-new-subject-domain-form .error-summary").hide();
        });
    });
</script>

<div class="my-knowledge-base-form">

    <?php $form = ActiveForm::begin([
        'id' => $model->isNewRecord ? 'create-my-knowledge-base-form' : 'update-my-knowledge-base-form',
    ]); ?>

    <?= $form->errorSummary($model); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'subject_domain')->dropDownList(SubjectDomain::getAllSubjectDomainsArray()) ?>

    <!-- Ссылка добавления новой предметной области -->
    <h5>
        <a id='add-new-subject-domain-link' href="#" data-toggle="modal" data-target="#addNewSubjectDomainModalForm">
            <?= Yii::t('app', 'MY_KNOWLEDGE_BASES_PAGE_ADD_NEW_SUBJECT_DOMAIN') ?>
        </a>
    </h5>

    <?= $form->field($model, 'type')->dropDownList(KnowledgeBase::getTypesArray()) ?>

    <?= $form->field($model, 'status')->dropDownList(KnowledgeBase::getStatusesArray()) ?>

    <?= $form->field($model, 'description')->textarea(['maxlength' => true, 'rows'=>6]) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'BUTTON_CREATE') : Yii::t('app', 'BUTTON_UPDATE'),
            ['class' => 'btn btn-success', 'name'=>$model->isNewRecord ? 'create-my-knowledge-base-button' :
                'update-my-knowledge-base-button']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>