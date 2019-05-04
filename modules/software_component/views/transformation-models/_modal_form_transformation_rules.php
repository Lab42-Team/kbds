<?php

/* @var $model app\modules\software_component\models\TransformationModel */
/* @var $software_component app\modules\software_component\models\SoftwareComponent */
/* @var $transformation_rule_model app\modules\software_component\models\TransformationRule */
/* @var $transformation_body_model app\modules\software_component\models\TransformationBody */

use yii\helpers\Html;
use yii\bootstrap\Modal;
use yii\bootstrap\Button;
use yii\widgets\ActiveForm;
use app\modules\main\models\Lang;
use app\modules\software_component\models\TransformationRule;
use app\modules\software_component\models\SoftwareComponent;

/* Модальное окно удаления модели трансформации */
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

<!-- Модальное окно добавления нового соответствия между метаклассами -->
<?php Modal::begin([
    'id' => 'addClassConnectionModalForm',
    'header' => '<h3>' . Yii::t('app', 'TRANSFORMATION_EDITOR_PAGE_ADD_NEW_CONNECTION') . '</h3>',
]); ?>

    <!-- Скрипт модального окна -->
    <script type="text/javascript">
        // Выполнение скрипта при загрузке страницы
        $(document).ready(function() {
            // Обработка нажатия кнопки сохранения
            $("#add-class-connection-button").click(function (e) {
                e.preventDefault();
                var form = $("#add-class-connection-model-form");
                // Ajax-запрос
                $.ajax({
                    url: "<?= Yii::$app->request->baseUrl . '/' . Lang::getCurrent()->url .
                        '/transformation-models/add-class-connection' ?>",
                    type: "post",
                    data: form.serialize() + "&software_component_id=<?= $software_component->id ?>",
                    dataType: "json",
                    success: function(data) {
                        // Если валидация прошла успешно (нет ошибок ввода)
                        if (data['error_status'] == false) {
                            // Скрывание модального окна
                            $("#addClassConnectionModalForm").modal('hide');
                            // Добавление соответствия между метаклассами
                            priority = data['priority'];
                            instance.connect({
                                source: data['source_id'],
                                target: data['target_id'],
                                anchors: [[1, 0, 0, 0, 0, 11], [0, 0, 0, 0, 0, 11]],
                                endpointStyles:[
                                    { width: 10, height: 10, fillStyle: "#00f" },
                                    { width: 10, height: 10, fillStyle: "#00f" }
                                ],
                                overlays : [
                                    [ "Label", {
                                        id: "label",
                                        cssClass: "aLabel"
                                    }]
                                ]
                            });
                            // Добавление еще одной точки связывания к исходному метаклассу
                            instance.addEndpoint(jsPlumb.getSelector("#" + data['source_id']),
                                { anchor: [1, 0, 0, 0, 0, 11] }, class_endpoint);
                            // Добавление еще одной точки связывания к целевому метаклассу
                            instance.addEndpoint(jsPlumb.getSelector("#" + data['target_id']),
                                { anchor: [0, 0, 0, 0, 0, 11], isSource: false }, class_endpoint);
                            // Кнопка генерации программного компонента
                            var software_component_generation_button = $("#software-component-generation-button");
                            // Активация кнопки генерации программного компонента
                            software_component_generation_button.prop("disabled", false);
                            // Если статус устаревшего программного компонента и кнопка генерации
                            // программного компонента зеленая, то изменение этой кнопки
                            if (data['software_component_status'] == "<?= SoftwareComponent::STATUS_OUTDATED ?>" &&
                                software_component_generation_button.hasClass("btn-success")) {
                                software_component_generation_button.text('');
                                software_component_generation_button.append(
                                    '<span class="glyphicon glyphicon-floppy-save"></span> ');
                                software_component_generation_button.append(
                                    '<?= Yii::t('app', 'BUTTON_REGENERATE_SOFTWARE_COMPONENT') ?>');
                                software_component_generation_button.toggleClass('btn-success btn-warning');
                            }
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
            <?php echo Yii::t('app', 'TRANSFORMATION_EDITOR_PAGE_ADD_NEW_CONNECTION_TEXT'); ?>
        </p>
    </div>

    <?php $form = ActiveForm::begin([
        'id' => 'add-class-connection-model-form',
        'enableAjaxValidation'=>true,
        'enableClientValidation'=>true,
    ]); ?>

        <?= $form->errorSummary($transformation_rule_model); ?>

        <?= $form->field($transformation_rule_model, 'type')
            ->hiddenInput(['maxlength' => true, 'value' => TransformationRule::TYPE_SIMPLE_RULE])->label(false) ?>

        <?= $form->field($transformation_rule_model, 'transformation_model')
            ->hiddenInput(['maxlength' => true, 'value' => $model->id])->label(false) ?>

        <?= $form->field($transformation_rule_model, 'source_metaclass')->hiddenInput(['maxlength' => true])
            ->label(false) ?>

        <?= $form->field($transformation_rule_model, 'target_metaclass')->hiddenInput(['maxlength' => true])
            ->label(false) ?>

        <?= $form->field($transformation_rule_model, 'priority')
            ->textInput(['maxlength' => true, 'value' => '1']) ?>

        <p class="help-block">
            <?= Yii::t('app', 'TRANSFORMATION_EDITOR_PAGE_ADD_CONNECTION_PRIORITY') ?>
        </p>

        <?= Button::widget([
            'label' => Yii::t('app', 'BUTTON_SAVE'),
            'options' => [
                'class' => 'btn-success',
                'style' => 'margin:5px',
                'id' => 'add-class-connection-button'
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

<!-- Модальное окно изменения соответствия между метаклассами -->
<?php Modal::begin([
    'id' => 'editClassConnectionModalForm',
    'header' => '<h3>' . Yii::t('app', 'TRANSFORMATION_EDITOR_PAGE_EDIT_CONNECTION') . '</h3>',
]); ?>

    <!-- Скрипт модального окна -->
    <script type="text/javascript">
        // Выполнение скрипта при загрузке страницы
        $(document).ready(function() {
            // Обработка нажатия кнопки сохранения
            $("#edit-class-connection-button").click(function (e) {
                e.preventDefault();
                var form = $("#edit-class-connection-model-form");
                // Ajax-запрос
                $.ajax({
                    url: "<?= Yii::$app->request->baseUrl . '/' . Lang::getCurrent()->url .
                        '/transformation-models/edit-class-connection' ?>",
                    type: "post",
                    data: form.serialize() + "&transformation_rule_id=" + transformation_rule_id +
                        "&software_component_id=<?= $software_component->id ?>",
                    dataType: "json",
                    success: function(data) {
                        // Если валидация прошла успешно (нет ошибок ввода)
                        if (data['error_status'] == false) {
                            // Скрывание модального окна
                            $("#editClassConnectionModalForm").modal('hide');
                            // Изменение слоя наименования связи
                            current_connection.getOverlay("label").setLabel(data['priority']);
                            // Кнопка генерации программного компонента
                            var software_component_generation_button = $("#software-component-generation-button");
                            // Если статус устаревшего программного компонента и кнопка генерации
                            // программного компонента зеленая, то изменение этой кнопки
                            if (data['software_component_status'] == "<?= SoftwareComponent::STATUS_OUTDATED ?>" &&
                                software_component_generation_button.hasClass("btn-success")) {
                                software_component_generation_button.text('');
                                software_component_generation_button.append(
                                    '<span class="glyphicon glyphicon-floppy-save"></span> ');
                                software_component_generation_button.append(
                                    '<?= Yii::t('app', 'BUTTON_REGENERATE_SOFTWARE_COMPONENT') ?>');
                                software_component_generation_button.toggleClass('btn-success btn-warning');
                            }
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
            <?php echo Yii::t('app', 'TRANSFORMATION_EDITOR_PAGE_EDIT_CONNECTION_TEXT'); ?>
        </p>
    </div>

    <?php $form = ActiveForm::begin([
        'id' => 'edit-class-connection-model-form',
        'enableAjaxValidation'=>true,
        'enableClientValidation'=>true,
    ]); ?>

        <?= $form->errorSummary($transformation_rule_model); ?>

        <?= $form->field($transformation_rule_model, 'type')
            ->hiddenInput(['maxlength' => true, 'value' => TransformationRule::TYPE_SIMPLE_RULE])->label(false) ?>

        <?= $form->field($transformation_rule_model, 'transformation_model')
            ->hiddenInput(['maxlength' => true, 'value' => $model->id])->label(false) ?>

        <?= $form->field($transformation_rule_model, 'source_metaclass')->hiddenInput(['maxlength' => true])
            ->label(false) ?>

        <?= $form->field($transformation_rule_model, 'target_metaclass')->hiddenInput(['maxlength' => true])
            ->label(false) ?>

        <?= $form->field($transformation_rule_model, 'priority')
            ->textInput(['maxlength' => true]) ?>

        <p class="help-block">
            <?= Yii::t('app', 'TRANSFORMATION_EDITOR_PAGE_EDIT_CONNECTION_PRIORITY') ?>
        </p>

        <?= Button::widget([
            'label' => Yii::t('app', 'BUTTON_SAVE'),
            'options' => [
                'class' => 'btn-success',
                'style' => 'margin:5px',
                'id' => 'edit-class-connection-button'
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

<!-- Модальное окно для удаления соответствия между метаклассами -->
<?php Modal::begin([
    'id' => 'deleteClassConnectionModalForm',
    'header' => '<h3>' . Yii::t('app', 'TRANSFORMATION_EDITOR_PAGE_DELETE_CONNECTION') . '</h3>',
]); ?>

    <!-- Скрипт модального окна -->
    <script type="text/javascript">
        $(document).ready(function() {
            // Обработка нажатия кнопки удаления
            $("#delete-class-connection-button").click(function (e) {
                e.preventDefault();
                // Запоминаем id текущих метаклассов участвующих в данном соответствии
                var source_class_id = current_source_class_id.replace(/source-class-/g, '');
                var target_class_id = current_target_class_id.replace(/target-class-/g, '');
                // Ajax-запрос
                $.ajax({
                    url: "<?= Yii::$app->request->baseUrl . '/' . Lang::getCurrent()->url .
                        '/transformation-models/delete-class-connection' ?>",
                    type: "post",
                    data: "YII_CSRF_TOKEN=<?= Yii::$app->request->csrfToken ?>&transformation_model_id=" +
                        "<?= $model->id ?>&source_class_id=" + source_class_id + "&target_class_id=" + target_class_id +
                        "&software_component_id=<?= $software_component->id ?>",
                    dataType: "json",
                    success: function(data) {
                        // Скрывание модального окна
                        $("#deleteClassConnectionModalForm").modal('hide');
                        // Удаление соответствия между метаклассами
                        instance.detach(current_connection);
                        // Массив для связей метаатрибутов
                        var connection_array = [];
                        // Цикл по всем связям
                        $.each(instance.getAllConnections(), function(id, connection) {
                            // Если id левого и правого метаатрибута в данной связи совпадают
                            $.each(data['attribute_id_array'], function(id, attribute) {
                                if("source-attribute-" + attribute['source_metaattribute'] == connection.sourceId &&
                                    "target-attribute-" + attribute['target_metaattribute'] == connection.targetId)
                                    // Добавление связи между метаатрибутами которую необходимо удалить
                                    connection_array.push(connection);
                            });
                        });
                        // Удаление связей между метаатрибутами
                        $.each(connection_array, function(id, connection) {
                            instance.detach(connection);
                        });
                        // Нахождение всех связей у исходного метакласса
                        var exist_connection = false;
                        $.each(instance.getAllConnections(), function(id, connection) {
                            if(current_source_class_id == connection.sourceId)
                                exist_connection = true;
                        });
                        // Если у данного исходного метакласса нет связей с другими метаклассами
                        if(exist_connection == false) {
                            // Скрывание точек связывания метаатрибутов для текущего исходного метакласса
                            var source_attributes = document.getElementById(current_source_class_id).childNodes;
                            [].forEach.call(source_attributes, function(source_attribute) {
                                instance.hide("" + source_attribute.id, true);
                            });
                        }
                        // Нахождение всех связей у целевого метакласса
                        exist_connection = false;
                        $.each(instance.getAllConnections(), function(id, connection) {
                            if(current_target_class_id == connection.targetId)
                                exist_connection = true;
                        });
                        // Если у данного целевого метакласса нет связей с другими метаклассами
                        if(exist_connection == false) {
                            // Скрывание точек связывания метаатрибутов для текущего целевого метакласса
                            var target_attributes = document.getElementById(current_target_class_id).childNodes;
                            [].forEach.call(target_attributes, function(target_attribute) {
                                instance.hide("" + target_attribute.id, true);
                            });
                        }
                        // Кнопка генерации программного компонента
                        var software_component_generation_button = $("#software-component-generation-button");
                        // Если статус устаревшего программного компонента и кнопка генерации
                        // программного компонента зеленая, то изменение этой кнопки
                        if (data['software_component_status'] == "<?= SoftwareComponent::STATUS_OUTDATED ?>" &&
                            software_component_generation_button.hasClass("btn-success")) {
                            software_component_generation_button.text('');
                            software_component_generation_button.append(
                                '<span class="glyphicon glyphicon-floppy-save"></span> ');
                            software_component_generation_button.append(
                                '<?= Yii::t('app', 'BUTTON_REGENERATE_SOFTWARE_COMPONENT') ?>');
                            software_component_generation_button.toggleClass('btn-success btn-warning');
                        }
                        // Если нет ни одной связи между метаклассами
                        if (instance.getAllConnections().length == 0)
                            // Деактивация кнопки генерации программного компонента
                            software_component_generation_button.prop("disabled", true);
                        // Формирование текста с сообщением
                        document.getElementById("message-text").lastChild.nodeValue =
                            "<?= Yii::t('app', 'TRANSFORMATION_EDITOR_PAGE_MESSAGE_DELETE_CONNECTION') ?>";
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
            <?php echo Yii::t('app', 'TRANSFORMATION_EDITOR_PAGE_DELETE_CONNECTION_TEXT'); ?>
        </p>
    </div>

    <?php $form = ActiveForm::begin([
        'id' => 'delete-class-connection-model-form',
    ]); ?>

        <?= Button::widget([
            'label' => Yii::t('app', 'BUTTON_DELETE'),
            'options' => [
                'class' => 'btn-success',
                'style' => 'margin:5px',
                'id' => 'delete-class-connection-button'
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

<!-- Модальное окно добавления нового соответствия между метаатрибутами -->
<?php Modal::begin([
    'id' => 'addAttributeConnectionModalForm',
    'header' => '<h3>' . Yii::t('app', 'TRANSFORMATION_EDITOR_PAGE_ADD_NEW_CONNECTION') . '</h3>',
]); ?>

    <!-- Скрипт модального окна -->
    <script type="text/javascript">
        // Выполнение скрипта при загрузке страницы
        $(document).ready(function() {
            // Обработка нажатия кнопки сохранения
            $("#save-attribute-connection-button").click(function (e) {
                e.preventDefault();
                var form = $("#add-attribute-connection-model-form");
                // Запоминаем id текущих метаклассов участвующих в данном соответствии
                var source_class_id = current_source_class_id.replace(/source-class-/g, '');
                var target_class_id = current_target_class_id.replace(/target-class-/g, '');
                // Ajax-запрос
                $.ajax({
                    url: "<?= Yii::$app->request->baseUrl . '/' . Lang::getCurrent()->url .
                        '/transformation-models/add-attribute-connection' ?>",
                    type: "post",
                    data: form.serialize() + "&transformation_model_id=<?= $model->id ?>" +
                        "&source_class_id=" + source_class_id + "&target_class_id=" + target_class_id +
                        "&software_component_id=<?= $software_component->id ?>",
                    dataType: "json",
                    success: function(data) {
                        // Если валидация прошла успешно (нет ошибок ввода)
                        if (data['error_status'] == false) {
                            // Скрывание модального окна
                            $("#addAttributeConnectionModalForm").modal('hide');
                            // Добавление соответствия между метаатрибутами
                            instance.connect({
                                source: data['source_id'],
                                target: data['target_id'],
                                anchors: [[1, 0, 0, 0, 0, 11], [0, 0, 0, 0, 0, 11]],
                                endpoint: ["Dot", { radius: 5 }],
                                endpointStyles:[
                                    { fillStyle: "#316b31" },
                                    { fillStyle: "#316b31" }
                                ],
                                paintStyle:{ strokeStyle: "#316b31", lineWidth: 4 }
                            });
                            // Добавление еще одной точи связывания к метаатрибуту исходного метакласса
                            instance.addEndpoint(jsPlumb.getSelector("#" + data['source_id']),
                                { anchor: [1, 0, 0, 0, 0, 11] }, attribute_endpoint);
                            // Добавление еще одной точки связывания к метаатрибуту целевого метакласса
                            instance.addEndpoint(jsPlumb.getSelector("#" + data['target_id']),
                                { anchor: [0, 0, 0, 0, 0, 11], isSource: false }, attribute_endpoint);
                            // Кнопка генерации программного компонента
                            var software_component_generation_button = $("#software-component-generation-button");
                            // Если статус устаревшего программного компонента и кнопка генерации
                            // программного компонента зеленая, то изменение этой кнопки
                            if (data['software_component_status'] == "<?= SoftwareComponent::STATUS_OUTDATED ?>" &&
                                software_component_generation_button.hasClass("btn-success")) {
                                software_component_generation_button.text('');
                                software_component_generation_button.append(
                                    '<span class="glyphicon glyphicon-floppy-save"></span> ');
                                software_component_generation_button.append(
                                    '<?= Yii::t('app', 'BUTTON_REGENERATE_SOFTWARE_COMPONENT') ?>');
                                software_component_generation_button.toggleClass('btn-success btn-warning');
                            }
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
            <?php echo Yii::t('app', 'TRANSFORMATION_EDITOR_PAGE_ADD_NEW_CONNECTION_TEXT'); ?>
        </p>
    </div>

    <?php $form = ActiveForm::begin([
        'id' => 'add-attribute-connection-model-form',
        'enableAjaxValidation'=>true,
        'enableClientValidation'=>true,
    ]); ?>

        <?= $form->errorSummary($transformation_body_model); ?>

        <?= $form->field($transformation_body_model, 'source_metaattribute')->hiddenInput(['maxlength' => true])
            ->label(false) ?>

        <?= $form->field($transformation_body_model, 'target_metaattribute')->hiddenInput(['maxlength' => true])
            ->label(false) ?>

        <?= Button::widget([
            'label' => Yii::t('app', 'BUTTON_SAVE'),
            'options' => [
                'class' => 'btn-success',
                'style' => 'margin:5px',
                'id' => 'save-attribute-connection-button'
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

<!-- Модальное окно для удаления соответствия между метаатрибутами -->
<?php Modal::begin([
    'id' => 'deleteAttributeConnectionModalForm',
    'header' => '<h3>' . Yii::t('app', 'TRANSFORMATION_EDITOR_PAGE_DELETE_CONNECTION') . '</h3>',
]); ?>

    <!-- Скрипт модального окна -->
    <script type="text/javascript">
        $(document).ready(function() {
            // Обработка нажатия кнопки удаления
            $("#delete-attribute-connection-button").click(function (e) {
                e.preventDefault();
                // Запоминаем id текущих метаатрибутов участвующих в данном соответствии
                var source_attribute_id = current_source_attribute_id.replace(/source-attribute-/g, '');
                var target_attribute_id = current_target_attribute_id.replace(/target-attribute-/g, '');
                // Запоминаем id метаклассов которым принадлежат данные метаатрибуты
                var source_class_id = current_source_class_id.replace(/source-class-/g, '');
                var target_class_id = current_target_class_id.replace(/target-class-/g, '');
                // Ajax-запрос
                $.ajax({
                    url: "<?= Yii::$app->request->baseUrl . '/' . Lang::getCurrent()->url .
                        '/transformation-models/delete-attribute-connection' ?>",
                    type: "post",
                    data: "YII_CSRF_TOKEN=<?= Yii::$app->request->csrfToken ?>" +
                        "&transformation_model_id=<?= $model->id ?>" +
                        "&source_class_id=" + source_class_id + "&target_class_id=" + target_class_id +
                        "&source_attribute_id=" + source_attribute_id + "&target_attribute_id=" + target_attribute_id +
                        "&software_component_id=<?= $software_component->id ?>",
                    dataType: "json",
                    success: function(data) {
                        // Скрывание модального окна
                        $("#deleteAttributeConnectionModalForm").modal('hide');
                        // Удаление соответствия между метаатрибутами
                        instance.detach(current_connection);
                        // Формирование текста с сообщением
                        document.getElementById("message-text").lastChild.nodeValue =
                            "<?= Yii::t('app', 'TRANSFORMATION_EDITOR_PAGE_MESSAGE_DELETE_CONNECTION') ?>";
                        // Вызов модального окна с сообщением
                        $("#viewMessageModalForm").modal("show");
                        // Кнопка генерации программного компонента
                        var software_component_generation_button = $("#software-component-generation-button");
                        // Если статус устаревшего программного компонента и кнопка генерации
                        // программного компонента зеленая, то изменение этой кнопки
                        if (data['software_component_status'] == "<?= SoftwareComponent::STATUS_OUTDATED ?>" &&
                            software_component_generation_button.hasClass("btn-success")) {
                            software_component_generation_button.text('');
                            software_component_generation_button.append(
                                '<span class="glyphicon glyphicon-floppy-save"></span> ');
                            software_component_generation_button.append(
                                '<?= Yii::t('app', 'BUTTON_REGENERATE_SOFTWARE_COMPONENT') ?>');
                            software_component_generation_button.toggleClass('btn-success btn-warning');
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
            <?php echo Yii::t('app', 'TRANSFORMATION_EDITOR_PAGE_DELETE_CONNECTION_TEXT'); ?>
        </p>
    </div>

    <?php $form = ActiveForm::begin([
        'id' => 'delete-connection-model-form',
    ]); ?>

        <?= Button::widget([
            'label' => Yii::t('app', 'BUTTON_DELETE'),
            'options' => [
                'class' => 'btn-success',
                'style' => 'margin:5px',
                'id' => 'delete-attribute-connection-button'
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
    'header' => '<h3>' . Yii::t('app', 'TRANSFORMATION_MODELS_PAGE_TRANSFORMATION_EDITOR') . '</h3>',
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

<!-- Модальное окно для подтверждения генерации программного компонента -->
<?php Modal::begin([
    'id' => 'generateSoftwareComponentModalForm',
    'header' => '<h3>' . Yii::t('app', 'TRANSFORMATION_MODELS_PAGE_TRANSFORMATION_EDITOR') . '</h3>',
]); ?>

    <div class="modal-body">
        <p style="font-size: 14px">
            <?php echo Yii::t('app', 'TRANSFORMATION_EDITOR_PAGE_GENERATE_SOFTWARE_COMPONENT_TEXT'); ?>
        </p>
    </div>

    <?php $form = ActiveForm::begin([
        'id' => 'generate-software-component-model-form',
        'method' => 'post',
        'action' => ['/transformation-models/generate-software-component/' . $model->id],
        'enableAjaxValidation'=>true,
        'enableClientValidation'=>true,
    ]); ?>

        <?= Html::submitButton(Yii::t('app', 'BUTTON_GENERATE'), ['class' => 'btn btn-success']) ?>

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