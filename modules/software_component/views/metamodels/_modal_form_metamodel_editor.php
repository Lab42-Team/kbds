<?php

/* @var $model app\modules\software_component\models\Metamodel */
/* @var $metarelation app\modules\software_component\models\Metarelation */
/* @var $metareference app\modules\software_component\models\Metareference */

use yii\helpers\Html;
use yii\bootstrap\Modal;
use yii\bootstrap\Button;
use yii\widgets\ActiveForm;
use app\modules\main\models\Lang;
use app\modules\software_component\models\Metarelation;

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

<!-- Модальное окно добавления нового отношения по идентификатору между метаклассами -->
<?php Modal::begin([
    'id' => 'addRelationModalForm',
    'header' => '<h3>' . Yii::t('app', 'METAMODEL_EDITOR_PAGE_ADD_RELATION') . '</h3>',
]); ?>

    <!-- Скрипт модального окна -->
    <script type="text/javascript">
        // Выполнение скрипта при загрузке страницы
        $(document).ready(function() {
            // Обработка нажатия кнопки сохранения
            $("#add-relation-button").click(function (e) {
                e.preventDefault();
                var form = $("#add-relation-model-form");
                // Ajax-запрос
                $.ajax({
                    url: "<?= Yii::$app->request->baseUrl . '/' . Lang::getCurrent()->url .
                        '/metamodels/add-relation' ?>",
                    type: "post",
                    data: form.serialize(),
                    dataType: "json",
                    success: function(data) {
                        // Если валидация прошла успешно (нет ошибок ввода)
                        if (data['error_status'] == false) {
                            // Скрывание модального окна
                            $("#addRelationModalForm").modal('hide');
                            // Добавление связи между метаатрибутами
                            var current_connection = instance.connect({
                                source: data['left_metaattribute_id'],
                                target: data['right_metaattribute_id'],
                                anchors: [
                                    ["Continuous", { faces:[ "left", "right" ] } ],
                                    ["Continuous", { faces:[ "left", "right" ] } ]
                                ],
                                endpoint: ["Dot", { radius: 2 }],
                                endpointStyles:[
                                    { fillStyle: "#316b31" },
                                    { fillStyle: "#316b31" }
                                ],
                                paintStyle: {
                                    strokeStyle: "#00ff00",
                                    lineWidth: 2,
                                    outlineColor: "transparent",
                                    outlineWidth: 4
                                }
                            });
                            // Добавление слоя наименования связи
                            current_connection.getOverlay("label").setLabel(data['name']);
                        } else {
                            // Отображение списка ошибок валидации
                            viewErrors(".error-summary", data['errors']);
                        }
                    },
                    error: function() {
                        alert('Error!');
                    }
                });
            });
        });
    </script>

    <div class="modal-body">
        <p style="font-size: 14px">
            <?php echo Yii::t('app', 'METAMODEL_EDITOR_PAGE_ADD_RELATION_TEXT'); ?>
        </p>
    </div>

    <?php $form = ActiveForm::begin([
        'id' => 'add-relation-model-form',
        'enableAjaxValidation'=>true,
        'enableClientValidation'=>true,
    ]); ?>

        <?= $form->errorSummary($metarelation); ?>

        <?= $form->field($metarelation, 'name')->textInput(['maxlength' => true]) ?>

        <?= $form->field($metarelation, 'type')
            ->hiddenInput(['maxlength' => true, 'value' => Metarelation::REFERENCE])->label(false) ?>

        <?= $form->field($metarelation, 'metamodel')
            ->hiddenInput(['maxlength' => true, 'value' => $model->id])->label(false) ?>

        <?= $form->field($metarelation, 'left_metaclass')->hiddenInput(['maxlength' => true])->label(false) ?>

        <?= $form->field($metarelation, 'right_metaclass')->hiddenInput(['maxlength' => true])->label(false) ?>

        <?= $form->field($metareference, 'left_metaattribute')->dropDownList(array()) ?>

        <?= $form->field($metareference, 'right_metaattribute')->dropDownList(array()) ?>

        <?= Button::widget([
            'label' => Yii::t('app', 'BUTTON_SAVE'),
            'options' => [
                'class' => 'btn-success',
                'style' => 'margin:5px',
                'id' => 'add-relation-button'
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

<!-- Модальное окно редактирования отношения по идентификатору между метаклассами -->
<?php Modal::begin([
    'id' => 'editRelationModalForm',
    'header' => '<h3>' . Yii::t('app', 'METAMODEL_EDITOR_PAGE_EDIT_RELATION') . '</h3>',
]); ?>

    <!-- Скрипт модального окна -->
    <script type="text/javascript">
        // Выполнение скрипта при загрузке страницы
        $(document).ready(function() {
            // Обработка нажатия кнопки сохранения
            $("#edit-relation-button").click(function (e) {
                e.preventDefault();
                var form = $("#edit-relation-model-form");
                // Ajax-запрос
                $.ajax({
                    url: "<?= Yii::$app->request->baseUrl . '/' . Lang::getCurrent()->url .
                        '/metamodels/edit-relation' ?>",
                    type: "post",
                    data: form.serialize() + "&metarelation_id=" + current_metarelation_id +
                        "&metareference_id=" + current_metareference_id,
                    dataType: "json",
                    success: function(data) {
                        // Если валидация прошла успешно (нет ошибок ввода)
                        if (data['error_status'] == false) {
                            // Скрывание модального окна
                            $("#editRelationModalForm").modal('hide');
                            // Удаление отношения между метаатрибутами
                            instance.detach(window.current_connection);
                            // Добавление связи между метаатрибутами
                            var current_connection = instance.connect({
                                source: data['left_metaattribute_id'],
                                target: data['right_metaattribute_id'],
                                anchors: [
                                    ["Continuous", { faces:[ "left", "right" ] } ],
                                    ["Continuous", { faces:[ "left", "right" ] } ]
                                ],
                                endpoint: ["Dot", { radius: 2 }],
                                endpointStyles:[
                                    { fillStyle: "#316b31" },
                                    { fillStyle: "#316b31" }
                                ],
                                paintStyle: {
                                    strokeStyle: "#00ff00",
                                    lineWidth: 2,
                                    outlineColor: "transparent",
                                    outlineWidth: 4
                                }
                            });
                            // Добавление слоя наименования связи
                            current_connection.getOverlay("label").setLabel(data['name']);
                        } else {
                            // Отображение списка ошибок валидации
                            viewErrors(".error-summary", data['errors']);
                        }
                    },
                    error: function() {
                        alert('Error!');
                    }
                });
            });
        });
    </script>

    <div class="modal-body">
        <p style="font-size: 14px">
            <?php echo Yii::t('app', 'METAMODEL_EDITOR_PAGE_EDIT_RELATION_TEXT'); ?>
        </p>
    </div>

    <?php $form = ActiveForm::begin([
        'id' => 'edit-relation-model-form',
        'enableAjaxValidation'=>true,
        'enableClientValidation'=>true,
    ]); ?>

        <?= $form->errorSummary($metarelation); ?>

        <?= $form->field($metarelation, 'name')->textInput(['maxlength' => true]) ?>

        <?= $form->field($metarelation, 'type')
            ->hiddenInput(['maxlength' => true, 'value' => Metarelation::REFERENCE])->label(false) ?>

        <?= $form->field($metarelation, 'metamodel')
            ->hiddenInput(['maxlength' => true, 'value' => $model->id])->label(false) ?>

        <?= $form->field($metarelation, 'left_metaclass')->hiddenInput(['maxlength' => true])->label(false) ?>

        <?= $form->field($metarelation, 'right_metaclass')->hiddenInput(['maxlength' => true])->label(false) ?>

        <?= $form->field($metareference, 'left_metaattribute')->dropDownList(array()) ?>

        <?= $form->field($metareference, 'right_metaattribute')->dropDownList(array()) ?>

        <?= Button::widget([
            'label' => Yii::t('app', 'BUTTON_SAVE'),
            'options' => [
                'class' => 'btn-success',
                'style' => 'margin:5px',
                'id' => 'edit-relation-button'
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

<!-- Модальное окно для удаления отношения по идентификатору между метаклассами -->
<?php Modal::begin([
    'id' => 'deleteRelationModalForm',
    'header' => '<h3>' . Yii::t('app', 'METAMODEL_EDITOR_PAGE_DELETE_RELATION') . '</h3>',
]); ?>

    <!-- Скрипт модального окна -->
    <script type="text/javascript">
        $(document).ready(function() {
            // Обработка нажатия кнопки удаления
            $("#delete-relation-button").click(function (e) {
                e.preventDefault();
                // Запоминаем id текущих метаатрибутов участвующих в данной связи
                var left_attribute_id = current_left_attribute_id.replace(/attribute-/g, '');
                var right_attribute_id = current_right_attribute_id.replace(/attribute-/g, '');
                // Ajax-запрос
                $.ajax({
                    url: "<?= Yii::$app->request->baseUrl . '/' . Lang::getCurrent()->url .
                        '/metamodels/delete-relation' ?>",
                    type: "post",
                    data: "YII_CSRF_TOKEN=<?= Yii::$app->request->csrfToken ?>&left_attribute_id=" + left_attribute_id +
                    "&right_attribute_id=" + right_attribute_id,
                    dataType: "json",
                    success: function() {
                        // Скрывание модального окна
                        $("#deleteRelationModalForm").modal('hide');
                        // Удаление отношения между метаатрибутами
                        instance.detach(current_connection);
                        // Формирование текста с сообщением
                        document.getElementById("message-text").lastChild.nodeValue =
                            "<?= Yii::t('app', 'METAMODEL_EDITOR_PAGE_MESSAGE_DELETE_RELATION') ?>";
                        // Вызов модального окна с сообщением
                        $("#viewMessageModalForm").modal("show");
                    },
                    error: function() {
                        alert('Error!');
                    }
                });
            });
        });
    </script>

    <div class="modal-body">
        <p style="font-size: 14px">
            <?php echo Yii::t('app', 'METAMODEL_EDITOR_PAGE_DELETE_RELATION_TEXT'); ?>
        </p>
    </div>

    <?php $form = ActiveForm::begin([
        'id' => 'delete-relation-model-form',
    ]); ?>

        <?= Button::widget([
            'label' => Yii::t('app', 'BUTTON_DELETE'),
            'options' => [
                'class' => 'btn-success',
                'style' => 'margin:5px',
                'id' => 'delete-relation-button'
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

<!-- Модальное окно для вывода сообщения -->
<?php Modal::begin([
    'id' => 'viewMessageModalForm',
    'header' => '<h3>' . Yii::t('app', 'METAMODELS_PAGE_METAMODEL_EDITOR') . '</h3>',
]); ?>

    <div class="modal-body">
        <p id="message-text" style="font-size: 14px">
        </p>
    </div>

    <?php $form = ActiveForm::begin([
        'id' => 'view-message-model-form',
    ]); ?>

        <?= Button::widget([
            'label' => Yii::t('app', 'BUTTON_OK'),
            'options' => [
                'class' => 'btn-success',
                'style' => 'margin:5px',
                'data-dismiss'=>'modal'
            ]
        ]); ?>

    <?php ActiveForm::end(); ?>

<?php Modal::end(); ?>