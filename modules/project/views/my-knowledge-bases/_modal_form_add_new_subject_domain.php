<?php

/* @var $subject_domain_model app\modules\knowledge_base\models\SubjectDomain */

use yii\bootstrap\Modal;
use yii\bootstrap\Button;
use yii\widgets\ActiveForm;
use app\modules\main\models\Lang;

Modal::begin([
    'id' => 'addNewSubjectDomainModalForm',
    'header' => '<h3>' . Yii::t('app', 'MY_KNOWLEDGE_BASES_PAGE_ADD_NEW_SUBJECT_DOMAIN') . '</h3>',
]); ?>

    <!-- Скрипт модального окна -->
    <script type="text/javascript">
        // Выполнение скрипта при загрузке страницы
        $(document).ready(function() {
            // Обработка нажатия кнопки сохранения
            $("#add-new-subject-domain-button").click(function (e) {
                e.preventDefault();
                var form = $("#add-new-subject-domain-form");
                // Ajax-запрос
                $.ajax({
                    url: "<?= Yii::$app->request->baseUrl . '/' . Lang::getCurrent()->url .
                        '/my-knowledge-bases/add-new-subject-domain' ?>",
                    type: "post",
                    data: form.serialize(),
                    dataType: "json",
                    success: function(data) {
                        // Если валидация прошла успешно (нет ошибок ввода)
                        if (data['error_status'] == false) {
                            // Скрывание модального окна
                            $("#addNewSubjectDomainModalForm").modal("hide");
                            // Добавление новой предметной области в список
                            var select = document.getElementById("knowledgebase-subject_domain");
                            var option = document.createElement("option");
                            option.value = data['id'];
                            option.text = data['name'];
                            select.add(option);
                        } else {
                            // Отображение списка ошибок валидации
                            viewErrors("#add-new-subject-domain-form .error-summary", data['errors']);
                        }
                    },
                    error: function() {
                        alert('Error!');
                    }
                });
            });
        });
    </script>

    <?php $form = ActiveForm::begin([
        'id' => 'add-new-subject-domain-form',
        'enableAjaxValidation'=>true,
        'enableClientValidation'=>true,
    ]); ?>

        <?= $form->errorSummary($subject_domain_model); ?>

        <?= $form->field($subject_domain_model, 'name')->textInput(['maxlength' => true]) ?>

        <?= $form->field($subject_domain_model, 'description')->textarea(['maxlength' => true, 'rows'=>6]) ?>

        <?= Button::widget([
            'label' => Yii::t('app', 'BUTTON_ADD'),
            'options' => [
                'class' => 'btn-success',
                'style' => 'margin:5px',
                'id' => 'add-new-subject-domain-button'
            ]
        ]); ?>

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