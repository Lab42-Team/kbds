<?php

/* @var $model app\modules\knowledge_base\models\KnowledgeBase */
/* @var $fact_template_model app\modules\knowledge_base\models\FactTemplate */
/* @var $fact_template_slot_model app\modules\knowledge_base\models\FactTemplateSlot */
/* @var $fact_model app\modules\knowledge_base\models\Fact */
/* @var $fact_slot_model app\modules\knowledge_base\models\FactSlot */
/* @var $rule_template_model app\modules\knowledge_base\models\RuleTemplate */
/* @var $rule_template_condition_models app\modules\knowledge_base\models\RuleTemplateCondition */
/* @var $rule_template_action_models app\modules\knowledge_base\models\RuleTemplateAction */
/* @var $rule_model app\modules\knowledge_base\models\Rule */
/* @var $rule_condition_models app\modules\knowledge_base\models\RuleCondition */
/* @var $rule_action_models app\modules\knowledge_base\models\RuleAction */

use yii\widgets\Pjax;
use yii\bootstrap\Alert;
use yii\bootstrap\Html;
use yii\bootstrap\Modal;
use yii\bootstrap\Button;
use yii\widgets\ActiveForm;
use app\modules\main\models\Lang;
use wbraganca\dynamicform\DynamicFormWidget;
use app\modules\knowledge_base\models\DataType;
use app\modules\knowledge_base\models\FactTemplate;
use app\modules\knowledge_base\models\Fact;
use app\modules\knowledge_base\models\RuleTemplate;
use app\modules\knowledge_base\models\RuleTemplateCondition;
use app\modules\knowledge_base\models\RuleTemplateAction;
use app\modules\knowledge_base\models\RuleCondition;
use app\modules\knowledge_base\models\RuleAction;
?>

<!-- Модальное окно добавления нового шаблона факта -->
<?php Modal::begin([
    'id' => 'addFactTemplateModalForm',
    'header' => '<h3>' . Yii::t('app', 'RVML_EDITOR_PAGE_ADD_NEW_FACT_TEMPLATE') . '</h3>',
]); ?>

    <!-- Скрипт модального окна -->
    <script type="text/javascript">
        // Выполнение скрипта при загрузке страницы
        $(document).ready(function() {
            // Обработка нажатия кнопки сохранения
            $("#add-fact-template-button").click(function(e) {
                var form = $("#add-fact-template-form");
                // Ajax-запрос
                $.ajax({
                    url: "<?= Yii::$app->request->baseUrl . '/' . Lang::getCurrent()->url .
                        '/rvml-editor/add-fact-template/' . $model->id ?>",
                    type: "post",
                    data: form.serialize(),
                    dataType: "json",
                    success: function(data) {
                        // Если валидация прошла успешно (нет ошибок ввода)
                        if (data['success']) {
                            // Скрывание модального окна
                            $("#addFactTemplateModalForm").modal("hide");
                            // Получение дерева элементов БЗ
                            var tree = $("#rvml-tree-view").treeview(true);
                            // Создание нового узла шаблона факта
                            var new_node = [{
                                id: data['id'],
                                text: data['name'],
                                description: data['description'],
                                icon: "glyphicon glyphicon-unchecked",
                                selectedIcon: "glyphicon glyphicon-arrow-right",
                                state: [{ expanded: true, selected: true }]
                            }];
                            // Поиск родительского узла для шаблона факта
                            var parent_node;
                            $.each(tree.getNodes(), function(id, node) {
                                if (node.id == 'fact-templates-node') {
                                    parent_node = node;
                                    // Обновление общего кол-ва шаблонов фактов (увеличение tag на 1)
                                    node.tags[0] += 1;
                                }
                            });
                            // Добавление нового узла шаблона факта в родительский узел шаблонов фактов
                            tree.addNode(new_node, parent_node, false, { silent: true });
                            // Выбор добавленного узла шаблона факта
                            tree.selectNode(new_node, { silent: false });
                        } else {
                            // Отображение ошибок ввода
                            viewErrors("#add-fact-template-form", data);
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
        'id' => 'add-fact-template-form',
        'enableAjaxValidation' => true,
        'enableClientValidation' => true,
    ]); ?>

        <?= $form->errorSummary($fact_template_model); ?>

        <?= $form->field($fact_template_model, 'name')->textInput(['maxlength' => true]) ?>

        <?= $form->field($fact_template_model, 'description')->textarea(['maxlength' => true, 'rows'=>6]) ?>

        <?= Button::widget([
            'label' => Yii::t('app', 'BUTTON_ADD'),
            'options' => [
                'id' => 'add-fact-template-button',
                'class' => 'btn-success',
                'style' => 'margin:5px'
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

<!-- Модальное окно изменения шаблона факта -->
<?php Modal::begin([
    'id' => 'editFactTemplateModalForm',
    'header' => '<h3>' . Yii::t('app', 'RVML_EDITOR_PAGE_EDIT_FACT_TEMPLATE') . '</h3>',
]); ?>

    <!-- Скрипт модального окна -->
    <script type="text/javascript">
        // Выполнение скрипта при загрузке страницы
        $(document).ready(function() {
            // Обработка нажатия кнопки сохранения
            $("#edit-fact-template-button").click(function(e) {
                var form = $("#edit-fact-template-form");
                // Ajax-запрос
                $.ajax({
                    url: "<?= Yii::$app->request->baseUrl . '/' . Lang::getCurrent()->url .
                        '/rvml-editor/edit-fact-template' ?>",
                    type: "post",
                    data: form.serialize() + "&fact_template_id=" + fact_template_id,
                    dataType: "json",
                    success: function(data) {
                        // Если валидация прошла успешно (нет ошибок ввода)
                        if (data['success']) {
                            // Скрывание модального окна
                            $("#editFactTemplateModalForm").modal('hide');
                            // Получение дерева элементов БЗ
                            var tree = $("#rvml-tree-view").treeview(true);
                            // Цикл по всем узлам дерева элементов БЗ
                            $.each(tree.getNodes(), function(id, node) {
                                // Нахождение обновляемого узла шаблона факта
                                if (node.id == data['id']) {
                                    // Формирование массива слотов шаблона факта
                                    var fact_template_slots = [];
                                    $.each(data['fact_template_slots'], function(id, ft_slot) {
                                        var fact_template_slot = {
                                            id: ft_slot['id'],
                                            text: ft_slot['name'],
                                            dataType: ft_slot['data_type'],
                                            defaultValue: ft_slot['default_value'],
                                            description: ft_slot['description'],
                                            selectedIcon: "glyphicon glyphicon-arrow-right",
                                            icon: "glyphicon glyphicon-tag"
                                        };
                                        fact_template_slots.push(fact_template_slot);
                                    });
                                    // Переменная нового узла шаблона факта
                                    var new_node;
                                    // Если текущий (старый) узел шаблона факта является условием в шаблоне правила
                                    if (node.operator)
                                        // Формирование нового узла шаблона факта (условия)
                                        new_node = {
                                            id: data['id'],
                                            ruleTemplateConditionId: node.ruleTemplateConditionId,
                                            operator: node.operator,
                                            text: data['name'],
                                            description: data['description'],
                                            icon: "glyphicon glyphicon-unchecked",
                                            selectedIcon: "glyphicon glyphicon-arrow-right",
                                            nodes: fact_template_slots
                                        };
                                    if (node.function)
                                        // Формирование нового узла шаблона факта (действия)
                                        new_node = {
                                            id: data['id'],
                                            ruleTemplateActionId: node.ruleTemplateActionId,
                                            function: node.function,
                                            text: data['name'],
                                            description: data['description'],
                                            icon: "glyphicon glyphicon-unchecked",
                                            selectedIcon: "glyphicon glyphicon-arrow-right",
                                            nodes: fact_template_slots
                                        };
                                    if (!node.operator && !node.function)
                                        // Формирование нового узла шаблона факта
                                        new_node = {
                                            id: data['id'],
                                            text: data['name'],
                                            description: data['description'],
                                            icon: "glyphicon glyphicon-unchecked",
                                            selectedIcon: "glyphicon glyphicon-arrow-right",
                                            nodes: fact_template_slots
                                        };
                                    // Обновление узла шаблона факта
                                    tree.updateNode(node, new_node, { silent: true });
                                    // Выбор обновленного узла шаблона факта
                                    if (node.level == 2)
                                        tree.selectNode(new_node, { silent: false });
                                }
                                // Обход всех фактов связанных с данным шаблонов факта
                                $.each(data['facts'], function(id, fact) {
                                    // Нахождение факта связанного с текущим шаблоном факта
                                    if (node.factTemplateId == fact['fact_template']) {
                                        // Формирование массива слотов факта
                                        var fact_slots = [];
                                        $.each(data['fact_slots'], function(id, f_slot) {
                                            if (fact['id'] == f_slot['fact']) {
                                                var fact_slot = {
                                                    id: f_slot['id'],
                                                    text: f_slot['name'],
                                                    dataType: f_slot['data_type'],
                                                    value: f_slot['value'],
                                                    description: f_slot['description'],
                                                    selectedIcon: "glyphicon glyphicon-arrow-right",
                                                    icon: "glyphicon glyphicon-tag"
                                                };
                                                fact_slots.push(fact_slot);
                                            }
                                        });
                                        // Переменная нового узла факта
                                        var new_node;
                                        // Если текущий (старый) узел факта является условием в правиле
                                        if (node.operator)
                                            // Формирование нового узла факта (условия)
                                            new_node = {
                                                id: fact['id'],
                                                factTemplateId: fact['fact_template'],
                                                ruleConditionId: node.ruleConditionId,
                                                operator: node.operator,
                                                text: fact['name'],
                                                description: fact['description'],
                                                icon: "glyphicon glyphicon-stop",
                                                selectedIcon: "glyphicon glyphicon-arrow-right",
                                                nodes: fact_slots
                                            };
                                        if (node.function)
                                            // Формирование нового узла факта (действия)
                                            new_node = {
                                                id: fact['id'],
                                                factTemplateId: fact['fact_template'],
                                                ruleActionId: node.ruleActionId,
                                                function: node.function,
                                                text: fact['name'],
                                                description: fact['description'],
                                                icon: "glyphicon glyphicon-stop",
                                                selectedIcon: "glyphicon glyphicon-arrow-right",
                                                nodes: fact_slots
                                            };
                                        if (!node.operator && !node.function)
                                            // Формирование нового узла начального факта
                                            new_node = {
                                                id: fact['id'],
                                                factTemplateId: fact['fact_template'],
                                                text: fact['name'],
                                                description: fact['description'],
                                                icon: "glyphicon glyphicon-stop",
                                                selectedIcon: "glyphicon glyphicon-arrow-right",
                                                nodes: fact_slots
                                            };
                                        // Обновление узла факта
                                        tree.updateNode(node, new_node, { silent: true });
                                    }
                                    return false;
                                });
                            });
                        } else {
                            // Отображение ошибок ввода
                            viewErrors("#edit-fact-template-form", data);
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
        'id' => 'edit-fact-template-form',
        'enableAjaxValidation' => true,
        'enableClientValidation' => true,
    ]); ?>

        <?= Alert::widget([
            'options' => ['class' => 'edit-fact-template-alert alert-warning'],
            'closeButton' => false
        ]); ?>

        <?= $form->errorSummary($fact_template_model); ?>

        <?= $form->field($fact_template_model, 'name')->textInput(['maxlength' => true]) ?>

        <?= $form->field($fact_template_model, 'description')->textarea(['maxlength' => true, 'rows'=>6]) ?>

        <?= Button::widget([
            'label' => Yii::t('app', 'BUTTON_SAVE'),
            'options' => [
                'id' => 'edit-fact-template-button',
                'class' => 'btn-success',
                'style' => 'margin:5px'
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

<!-- Модальное окно удаления шаблона факта -->
<?php Modal::begin([
    'id' => 'deleteFactTemplateModalForm',
    'header' => '<h3>' . Yii::t('app', 'RVML_EDITOR_PAGE_DELETE_FACT_TEMPLATE') . '</h3>',
]); ?>

    <!-- Скрипт модального окна -->
    <script type="text/javascript">
        $(document).ready(function() {
            // Обработка нажатия кнопки удаления
            $("#delete-fact-template-button").click(function(e) {
                e.preventDefault();
                // Ajax-запрос
                $.ajax({
                    url: "<?= Yii::$app->request->baseUrl . '/' . Lang::getCurrent()->url .
                        '/rvml-editor/delete-fact-template' ?>",
                    type: "post",
                    data: "YII_CSRF_TOKEN=<?= Yii::$app->request->csrfToken ?>&fact_template_id=" + fact_template_id,
                    dataType: "json",
                    success: function(data) {
                        // Скрывание модального окна
                        $("#deleteFactTemplateModalForm").modal('hide');
                        // Кнопки на панели инструментов дерева элементов БЗ
                        var add_element_button = document.getElementById("add-element-button");
                        var edit_element_button = document.getElementById("edit-element-button");
                        var delete_element_button = document.getElementById("delete-element-button");
                        // Деактивация кнопок на панели дерева элементов БЗ
                        add_element_button.classList.add("disabled");
                        add_element_button.setAttribute("data-target", "");
                        add_element_button.setAttribute("data-original-title", "");
                        edit_element_button.classList.add("disabled");
                        edit_element_button.setAttribute("data-target", "");
                        edit_element_button.setAttribute("data-original-title", "");
                        delete_element_button.classList.add("disabled");
                        delete_element_button.setAttribute("data-target", "");
                        delete_element_button.setAttribute("data-original-title", "");
                        // Получение дерева элементов БЗ
                        var tree = $("#rvml-tree-view").treeview(true);
                        // Цикл по удаляемым правилам
                        $.each(data['rules'], function(id, rule) {
                            var removed_node;
                            $.each(tree.getNodes(), function(id, node) {
                                if (node.id == rule['id'])
                                    removed_node = node;
                                // Обновление общего кол-ва правил (уменьшение tag на 1)
                                if (node.id == 'rules-node')
                                    node.tags[0] -= 1;
                            });
                            // Удаление узла правила
                            tree.removeNode(removed_node, { silent: true });
                        });
                        // Цикл по удаляемым шаблонам правил
                        $.each(data['rule_templates'], function(id, rule_template) {
                            var removed_node;
                            $.each(tree.getNodes(), function(id, node) {
                                if (node.id == rule_template['id'])
                                    removed_node = node;
                                // Обновление общего кол-ва шаблонов правил (уменьшение tag на 1)
                                if (node.id == 'rule-templates-node')
                                    node.tags[0] -= 1;
                            });
                            // Удаление узла шаблона правила
                            tree.removeNode(removed_node, { silent: true });
                        });
                        // Поиск удаляемого узла начального факта
                        $.each(data['facts'], function(id, fact) {
                            $.each(tree.getNodes(), function(id, node) {
                                if (node.id == fact['id']) {
                                    // Удаление узла факта
                                    tree.removeNode(node, {silent: true});
                                    // Обновление общего кол-ва фактов (уменьшение tag на 1)
                                    tree.getParents(node)[0].tags[0] -= 1;
                                }
                            });
                        });
                        // Поиск удаляемого узла шаблона факта
                        $.each(tree.getNodes(), function(id, node) {
                            if (node.id == data['fact_template_id']) {
                                // Обновление общего кол-ва шаблонов фактов (уменьшение tag на 1)
                                tree.getParents(node)[0].tags[0] -= 1;
                                // Удаление узла шаблона факта
                                tree.removeNode(node, {silent: true});
                            }
                        });
                        // Рабочая область (слой) редактора RVML
                        var production_model = document.getElementById('production-model');
                        // Очистка рабочей области (удаление всех элементов)
                        while(production_model.firstChild)
                            production_model.removeChild(production_model.firstChild);
                        // Формирование текста с сообщением
                        document.getElementById("message-text").lastChild.nodeValue =
                            "<?= Yii::t('app', 'RVML_EDITOR_PAGE_MESSAGE_DELETE_FACT_TEMPLATE') ?>";
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
            <?php echo Yii::t('app', 'RVML_EDITOR_PAGE_MODAL_FORM_DELETE_FACT_TEMPLATE_TEXT'); ?>
        </p>
    </div>

    <?php $form = ActiveForm::begin([
        'id' => 'delete-fact-template-form',
    ]); ?>

        <?= Alert::widget([
            'options' => ['class' => 'delete-fact-template-alert alert-warning'],
            'closeButton' => false
        ]); ?>

        <?= Button::widget([
            'label' => Yii::t('app', 'BUTTON_DELETE'),
            'options' => [
                'id' => 'delete-fact-template-button',
                'class' => 'btn-success',
                'style' => 'margin:5px'
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

<!-- Модальное окно добавления нового слота шаблона факта -->
<?php Modal::begin([
    'id' => 'addFactTemplateSlotModalForm',
    'header' => '<h3>' . Yii::t('app', 'RVML_EDITOR_PAGE_ADD_NEW_FACT_TEMPLATE_SLOT') . '</h3>',
]); ?>

    <!-- Скрипт модального окна -->
    <script type="text/javascript">
        // Выполнение скрипта при загрузке страницы
        $(document).ready(function() {
            // Обработка нажатия кнопки сохранения
            $("#add-fact-template-slot-button").click(function(e) {
                var form = $("#add-fact-template-slot-form");
                // Ajax-запрос
                $.ajax({
                    url: "<?= Yii::$app->request->baseUrl . '/' . Lang::getCurrent()->url .
                        '/rvml-editor/add-fact-template-slot' ?>",
                    type: "post",
                    data: form.serialize() + "&fact_template_id=" + fact_template_id,
                    dataType: "json",
                    success: function(data) {
                        // Если валидация прошла успешно (нет ошибок ввода)
                        if (data['success']) {
                            // Скрывание модального окна
                            $("#addFactTemplateSlotModalForm").modal("hide");
                            // Получение дерева элементов БЗ
                            var tree = $("#rvml-tree-view").treeview(true);
                            // Цикл по всем узлам дерева элементов БЗ
                            $.each(tree.getNodes(), function(id, main_node) {
                                // Если текущий узел это набор шаблонов фактов
                                if (main_node.id == 'fact-templates-node')
                                    $.each(main_node.nodes, function(id, node) {
                                        // Создание нового узла слота шаблона факта
                                        var new_node = {
                                            id: data['fact_template_slot']['id'],
                                            text: data['fact_template_slot']['name'],
                                            dataType: data['fact_template_slot']['data_type'],
                                            defaultValue: data['fact_template_slot']['default_value'],
                                            description: data['fact_template_slot']['description'],
                                            icon: "glyphicon glyphicon-tag",
                                            selectedIcon: "glyphicon glyphicon-arrow-right",
                                            state: [{ selected: true }]
                                        };
                                        if (node.id == fact_template_id) {
                                            // Добавление нового узла слота в родительский узел шаблона факта
                                            tree.addNode(new_node, node, false, { silent: true });
                                            // Выбор добавленного узла слота шаблона факта
                                            tree.selectNode(new_node, { silent: false });
                                        }
                                    });
                                // Если текущий узел это набор шаблонов правил
                                if (main_node.id == 'rule-templates-node')
                                    $.each(main_node.nodes, function(id, rule_node) {
                                        // Создание нового узла слота шаблона факта
                                        var new_node = {
                                            id: data['fact_template_slot']['id'],
                                            text: data['fact_template_slot']['name'],
                                            dataType: data['fact_template_slot']['data_type'],
                                            defaultValue: data['fact_template_slot']['default_value'],
                                            description: data['fact_template_slot']['description'],
                                            icon: "glyphicon glyphicon-tag",
                                            selectedIcon: "glyphicon glyphicon-arrow-right",
                                            state: [{ selected: true }]
                                        };
                                        $.each(rule_node.nodes[0].nodes, function(id, node) {
                                            if (node.id == fact_template_id) {
                                                // Добавление нового узла слота шаблона факта в шаблонах правил
                                                tree.addNode(new_node, node, false, { silent: true });
                                                // Свертывание узлов в шаблонах правил
                                                tree.toggleNodeExpanded(node, {silent: true});
                                            }
                                        });
                                    });
                                // Обход всех фактов связанных с данным шаблоном факта
                                $.each(data['fact_slots'], function(fact_id, fact_slot) {
                                    if (main_node.id == fact_id) {
                                        // Создание нового узла слота факта
                                        var new_node = {
                                            id: fact_slot['id'],
                                            text: fact_slot['name'],
                                            dataType: fact_slot['data_type'],
                                            value: fact_slot['value'],
                                            description: fact_slot['description'],
                                            icon: "glyphicon glyphicon-tag",
                                            selectedIcon: "glyphicon glyphicon-arrow-right",
                                            state: [{ selected: true }]
                                        };
                                        // Добавление нового узла слота в родительский узел факта
                                        tree.addNode(new_node, main_node, false, { silent: true });
                                        // Свертывание узла в который был добавлен новый слот факта
                                        tree.toggleNodeExpanded(main_node, {silent: true});
                                    }
                                });
                            });
                        } else {
                            // Отображение ошибок ввода
                            viewErrors("#add-fact-template-slot-form", data);
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
        'id' => 'add-fact-template-slot-form',
        'enableAjaxValidation' => true,
        'enableClientValidation' => true,
    ]); ?>

        <?= $form->errorSummary($fact_template_slot_model); ?>

        <?= $form->field($fact_template_slot_model, 'name')->textInput(['maxlength' => true]) ?>

        <?= $form->field($fact_template_slot_model, 'data_type')->dropDownList(DataType::getDataTypesArray($model->id)) ?>

        <?= $form->field($fact_template_slot_model, 'default_value')->textInput(['maxlength' => true]) ?>

        <?= $form->field($fact_template_slot_model, 'description')->textarea(['maxlength' => true, 'rows'=>6]) ?>

        <?= Button::widget([
            'label' => Yii::t('app', 'BUTTON_ADD'),
            'options' => [
                'id' => 'add-fact-template-slot-button',
                'class' => 'btn-success',
                'style' => 'margin:5px'
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

<!-- Модальное окно изменения слота шаблона факта -->
<?php Modal::begin([
    'id' => 'editFactTemplateSlotModalForm',
    'header' => '<h3>' . Yii::t('app', 'RVML_EDITOR_PAGE_EDIT_FACT_TEMPLATE_SLOT') . '</h3>',
]); ?>

    <!-- Скрипт модального окна -->
    <script type="text/javascript">
        // Выполнение скрипта при загрузке страницы
        $(document).ready(function() {
            // Обработка нажатия кнопки сохранения
            $("#edit-fact-template-slot-button").click(function(e) {
                var form = $("#edit-fact-template-slot-form");
                // Ajax-запрос
                $.ajax({
                    url: "<?= Yii::$app->request->baseUrl . '/' . Lang::getCurrent()->url .
                        '/rvml-editor/edit-fact-template-slot' ?>",
                    type: "post",
                    data: form.serialize() + "&fact_template_slot_id=" + fact_template_slot_id,
                    dataType: "json",
                    success: function(data) {
                        // Если валидация прошла успешно (нет ошибок ввода)
                        if (data['success']) {
                            // Скрывание модального окна
                            $("#editFactTemplateSlotModalForm").modal("hide");
                            // Получение дерева элементов БЗ
                            var tree = $("#rvml-tree-view").treeview(true);
                            // Цикл по всем узлам дерева элементов БЗ
                            $.each(tree.getNodes(), function(id, node) {
                                // Нахождение обновляемого узла слота шаблона факта
                                if (node.id == data['fact_template_slot']['id']) {
                                    // Формирование нового узла слота шаблона факта
                                    var new_node = {
                                        id: data['fact_template_slot']['id'],
                                        text: data['fact_template_slot']['name'],
                                        dataType: data['fact_template_slot']['data_type'],
                                        defaultValue: data['fact_template_slot']['default_value'],
                                        description: data['fact_template_slot']['description'],
                                        icon: "glyphicon glyphicon-tag",
                                        selectedIcon: "glyphicon glyphicon-arrow-right",
                                        state: [{ selected: true }]
                                    };
                                    // Обновление узла слота шаблона факта
                                    tree.updateNode(node, new_node, { silent: true });
                                    // Выбор обновленного узла слота шаблона факта
                                    if (node.level == 3)
                                        tree.selectNode(new_node, { silent: false });
                                }
                            });
                            // Цикл по изменяемым слотам фактов
                            $.each(data['fact_slots'], function(fact_id, fact_slot) {
                                // Цикл по всем узлам дерева элементов БЗ
                                $.each(tree.getNodes(), function(id, node) {
                                    if (node.id == fact_slot['id']) {
                                        // Создание нового узла слота факта
                                        var new_node = {
                                            id: fact_slot['id'],
                                            text: fact_slot['name'],
                                            dataType: fact_slot['data_type'],
                                            value: fact_slot['value'],
                                            description: fact_slot['description'],
                                            icon: "glyphicon glyphicon-tag",
                                            selectedIcon: "glyphicon glyphicon-arrow-right",
                                            state: [{selected: true}]
                                        };
                                        // Обновление узла слота факта
                                        tree.updateNode(node, new_node, { silent: true });
                                    }
                                });
                            });
                        } else {
                            // Отображение ошибок ввода
                            viewErrors("#edit-fact-template-slot-form", data);
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
        'id' => 'edit-fact-template-slot-form',
        'enableAjaxValidation' => true,
        'enableClientValidation' => true,
    ]); ?>

        <?= Alert::widget([
            'options' => ['class' => 'edit-fact-template-alert alert-warning'],
            'closeButton' => false
        ]); ?>

        <?= $form->errorSummary($fact_template_slot_model); ?>

        <?= $form->field($fact_template_slot_model, 'name')->textInput(['maxlength' => true]) ?>

        <?= $form->field($fact_template_slot_model, 'data_type')->dropDownList(DataType::getDataTypesArray($model->id)) ?>

        <?= $form->field($fact_template_slot_model, 'default_value')->textInput(['maxlength' => true]) ?>

        <?= $form->field($fact_template_slot_model, 'description')->textarea(['maxlength' => true, 'rows'=>6]) ?>

        <?= Button::widget([
            'label' => Yii::t('app', 'BUTTON_SAVE'),
            'options' => [
                'id' => 'edit-fact-template-slot-button',
                'class' => 'btn-success',
                'style' => 'margin:5px'
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

<!-- Модальное окно удаления слота шаблона факта -->
<?php Modal::begin([
    'id' => 'deleteFactTemplateSlotModalForm',
    'header' => '<h3>' . Yii::t('app', 'RVML_EDITOR_PAGE_DELETE_FACT_TEMPLATE_SLOT') . '</h3>',
]); ?>

    <!-- Скрипт модального окна -->
    <script type="text/javascript">
        $(document).ready(function() {
            // Обработка нажатия кнопки удаления
            $("#delete-fact-template-slot-button").click(function(e) {
                e.preventDefault();
                // Ajax-запрос
                $.ajax({
                    url: "<?= Yii::$app->request->baseUrl . '/' . Lang::getCurrent()->url .
                        '/rvml-editor/delete-fact-template-slot' ?>",
                    type: "post",
                    data: "YII_CSRF_TOKEN=<?= Yii::$app->request->csrfToken ?>&fact_template_slot_id=" +
                        fact_template_slot_id,
                    dataType: "json",
                    success: function(data) {
                        // Скрывание модального окна
                        $("#deleteFactTemplateSlotModalForm").modal('hide');
                        // Получение дерева элементов БЗ
                        var tree = $("#rvml-tree-view").treeview(true);
                        // Поиск удаляемого узла слота шаблона факта
                        $.each(tree.getNodes(), function(id, node) {
                            if (node.id == data['fact_template_slot_id']) {
                                // Удаление узла слота шаблона факта
                                tree.removeNode(node, {silent: true});
                                // Выбор обновленного узла шаблона факта
                                if (node.level == 3)
                                    tree.selectNode(tree.getParents(node), { silent: false });
                            }
                        });
                        // Цикл по удаляемым слотам фактов
                        $.each(data['fact_slots'], function(id, fact_slot) {
                            // Цикл по всем узлам дерева элементов БЗ
                            $.each(tree.getNodes(), function(id, node) {
                                if (node.id == fact_slot['id'])
                                    // Удаление узла слота факта
                                    tree.removeNode(node, {silent: true});
                            });
                        });
                        // Формирование текста с сообщением
                        document.getElementById("message-text").lastChild.nodeValue =
                            "<?= Yii::t('app', 'RVML_EDITOR_PAGE_MESSAGE_DELETE_FACT_TEMPLATE_SLOT') ?>";
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
            <?php echo Yii::t('app', 'RVML_EDITOR_PAGE_MODAL_FORM_DELETE_FACT_TEMPLATE_SLOT_TEXT'); ?>
        </p>
    </div>

    <?php $form = ActiveForm::begin([
        'id' => 'delete-fact-template-slot-form',
    ]); ?>

        <?= Alert::widget([
            'options' => ['class' => 'delete-fact-template-alert alert-warning'],
            'closeButton' => false
        ]); ?>

        <?= Button::widget([
            'label' => Yii::t('app', 'BUTTON_DELETE'),
            'options' => [
                'id' => 'delete-fact-template-slot-button',
                'class' => 'btn-success',
                'style' => 'margin:5px'
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

<!-- Модальное окно добавления нового начального факта -->
<?php Modal::begin([
    'id' => 'addInitialFactModalForm',
    'header' => '<h3>' . Yii::t('app', 'RVML_EDITOR_PAGE_ADD_NEW_INITIAL_FACT') . '</h3>',
]); ?>

    <!-- Скрипт модального окна -->
    <script type="text/javascript">
        // Выполнение скрипта при загрузке страницы
        $(document).ready(function() {
            // Поле наименования факта
            var fact_name = document.getElementById("fact-name");
            // Список шаблонов фактов
            var fact_template = document.getElementById("fact-fact_template");
            // Определение наименования факта в соответствии с выбранным шаблоном факта
            for (var i = 0; i < fact_template.options.length; i++)
                if (fact_template.options[i].selected) {
                    fact_name.value = fact_template.options[i].text;
                    fact_name.readOnly = true;
                }
            // Действие при выборе шаблона факта
            $("#fact-fact_template").change(function() {
                for (var i = 0; i < fact_template.options.length; i++)
                    if (fact_template.options[i].selected)
                        fact_name.value = fact_template.options[i].text;
            });
            // Обработка нажатия кнопки сохранения
            $("#add-initial-fact-button").click(function(e) {
                var form = $("#add-initial-fact-form");
                // Ajax-запрос
                $.ajax({
                    url: "<?= Yii::$app->request->baseUrl . '/' . Lang::getCurrent()->url .
                        '/rvml-editor/add-initial-fact/' . $model->id ?>",
                    type: "post",
                    data: form.serialize(),
                    dataType: "json",
                    success: function(data) {
                        // Если валидация прошла успешно (нет ошибок ввода)
                        if (data['success']) {
                            // Скрывание модального окна
                            $("#addInitialFactModalForm").modal("hide");
                            // Получение дерева элементов БЗ
                            var tree = $("#rvml-tree-view").treeview(true);
                            // Формирование массива слотов факта
                            var fact_slots = [];
                            $.each(data['fact_slots'], function(id, f_slot) {
                                var fact_slot = {
                                    id: f_slot['id'],
                                    text: f_slot['name'],
                                    dataType: f_slot['data_type'],
                                    value: f_slot['value'],
                                    description: f_slot['description'],
                                    selectedIcon: "glyphicon glyphicon-arrow-right",
                                    icon: "glyphicon glyphicon-tag"
                                };
                                fact_slots.push(fact_slot);
                            });
                            // Создание нового узла начального факта
                            var new_node = [{
                                id: data['id'],
                                factTemplateId: data['fact_template'],
                                text: data['name'],
                                certaintyFactor: data['certainty_factor'],
                                description: data['description'],
                                icon: "glyphicon glyphicon-stop",
                                selectedIcon: "glyphicon glyphicon-arrow-right",
                                state: [{ expanded: true, selected: true }],
                                nodes: fact_slots
                            }];
                            // Поиск родительского узла для факта
                            var parent_node;
                            $.each(tree.getNodes(), function(id, node) {
                                if (node.id == 'initial-facts-node') {
                                    parent_node = node;
                                    // Обновление общего кол-ва начальных фактов (увеличение tag на 1)
                                    node.tags[0] += 1;
                                }
                            });
                            // Добавление нового узла начального факта в родительский узел шаблонов фактов
                            tree.addNode(new_node, parent_node, false, { silent: true });
                            // Выбор добавленного узла начального факта
                            tree.selectNode(new_node, { silent: false });
                        } else {
                            // Отображение ошибок ввода
                            viewErrors("#add-initial-fact-form", data);
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
        'id' => 'add-initial-fact-form',
        'enableAjaxValidation' => true,
        'enableClientValidation' => true,
    ]); ?>

        <?= $form->errorSummary($fact_model); ?>

        <?= $form->field($fact_model, 'fact_template')->dropDownList(FactTemplate::getFactTemplatesArray($model->id)) ?>

        <h5><span class="label label-info"><?= Yii::t('app', 'FACT_MODEL_NAME_NOTICE') ?></span></h5>

        <?= $form->field($fact_model, 'name')->textInput(['maxlength' => true]) ?>

        <?= $form->field($fact_model, 'certainty_factor')->textInput(['maxlength' => true]) ?>

        <?= $form->field($fact_model, 'description')->textarea(['maxlength' => true, 'rows'=>6]) ?>

        <?= Button::widget([
            'label' => Yii::t('app', 'BUTTON_ADD'),
            'options' => [
                'id' => 'add-initial-fact-button',
                'class' => 'btn-success',
                'style' => 'margin:5px'
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

<!-- Модальное окно изменения начального факта -->
<?php Modal::begin([
    'id' => 'editInitialFactModalForm',
    'header' => '<h3>' . Yii::t('app', 'RVML_EDITOR_PAGE_EDIT_INITIAL_FACT') . '</h3>',
]); ?>

    <!-- Скрипт модального окна -->
    <script type="text/javascript">
        // Выполнение скрипта при загрузке страницы
        $(document).ready(function() {
            // Обработка нажатия кнопки сохранения
            $("#edit-initial-fact-button").click(function(e) {
                var form = $("#edit-initial-fact-form");
                // Ajax-запрос
                $.ajax({
                    url: "<?= Yii::$app->request->baseUrl . '/' . Lang::getCurrent()->url .
                        '/rvml-editor/edit-initial-fact' ?>",
                    type: "post",
                    data: form.serialize() + "&fact_id=" + fact_id,
                    dataType: "json",
                    success: function(data) {
                        // Если валидация прошла успешно (нет ошибок ввода)
                        if (data['success']) {
                            // Скрывание модального окна
                            $("#editInitialFactModalForm").modal('hide');
                            // Получение дерева элементов БЗ
                            var tree = $("#rvml-tree-view").treeview(true);
                            // Цикл по всем узлам дерева элементов БЗ
                            $.each(tree.getNodes(), function(id, node) {
                                // Нахождение обновляемого узла начального факта
                                if (node.id == data['id'] && node.level == 2) {
                                    // Формирование массива слотов факта
                                    var fact_slots = [];
                                    $.each(data['fact_slots'], function(id, f_slot) {
                                        var fact_slot = {
                                            id: f_slot['id'],
                                            text: f_slot['name'],
                                            dataType: f_slot['data_type'],
                                            value: f_slot['value'],
                                            description: f_slot['description'],
                                            selectedIcon: "glyphicon glyphicon-arrow-right",
                                            icon: "glyphicon glyphicon-tag"
                                        };
                                        fact_slots.push(fact_slot);
                                    });
                                    // Формирование нового узла начального факта
                                    var new_node = {
                                        id: data['id'],
                                        factTemplateId: data['fact_template'],
                                        text: data['name'],
                                        certaintyFactor: data['certainty_factor'],
                                        description: data['description'],
                                        icon: "glyphicon glyphicon-stop",
                                        selectedIcon: "glyphicon glyphicon-arrow-right",
                                        nodes: fact_slots
                                    };
                                    // Обновление узла факта
                                    tree.updateNode(node, new_node, {silent: true});
                                    // Выбор обновленного узла факта
                                    tree.selectNode(new_node, {silent: false});
                                }
                            });
                        } else {
                            // Отображение ошибок ввода
                            viewErrors("#edit-initial-fact-form", data);
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
        'id' => 'edit-initial-fact-form',
        'enableAjaxValidation' => true,
        'enableClientValidation' => true,
    ]); ?>

        <?= $form->errorSummary($fact_model); ?>

        <?= $form->field($fact_model, 'fact_template')->hiddenInput() ?>

        <?= $form->field($fact_model, 'fact_template_name')->dropDownList(
            FactTemplate::getFactTemplatesArray($model->id),
            ['disabled' => true]
        ) ?>

        <?= $form->field($fact_model, 'name')->textInput(['maxlength' => true]) ?>

        <?= $form->field($fact_model, 'certainty_factor')->textInput(['maxlength' => true]) ?>

        <?= $form->field($fact_model, 'description')->textarea(['maxlength' => true, 'rows'=>6]) ?>

        <?= Button::widget([
            'label' => Yii::t('app', 'BUTTON_SAVE'),
            'options' => [
                'id' => 'edit-initial-fact-button',
                'class' => 'btn-success',
                'style' => 'margin:5px'
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

<!-- Модальное окно удаления начального факта -->
<?php Modal::begin([
    'id' => 'deleteInitialFactModalForm',
    'header' => '<h3>' . Yii::t('app', 'RVML_EDITOR_PAGE_DELETE_INITIAL_FACT') . '</h3>',
]); ?>

    <!-- Скрипт модального окна -->
    <script type="text/javascript">
        $(document).ready(function() {
            // Обработка нажатия кнопки удаления
            $("#delete-initial-fact-button").click(function(e) {
                e.preventDefault();
                // Ajax-запрос
                $.ajax({
                    url: "<?= Yii::$app->request->baseUrl . '/' . Lang::getCurrent()->url .
                        '/rvml-editor/delete-initial-fact' ?>",
                    type: "post",
                    data: "YII_CSRF_TOKEN=<?= Yii::$app->request->csrfToken ?>&fact_id=" + fact_id,
                    dataType: "json",
                    success: function(data) {
                        // Скрывание модального окна
                        $("#deleteInitialFactModalForm").modal('hide');
                        // Кнопки на панели инструментов дерева элементов БЗ
                        var add_element_button = document.getElementById("add-element-button");
                        var edit_element_button = document.getElementById("edit-element-button");
                        var delete_element_button = document.getElementById("delete-element-button");
                        // Деактивация кнопок на панели дерева элементов БЗ
                        add_element_button.classList.add("disabled");
                        add_element_button.setAttribute("data-target", "");
                        add_element_button.setAttribute("data-original-title", "");
                        edit_element_button.classList.add("disabled");
                        edit_element_button.setAttribute("data-target", "");
                        edit_element_button.setAttribute("data-original-title", "");
                        delete_element_button.classList.add("disabled");
                        delete_element_button.setAttribute("data-target", "");
                        delete_element_button.setAttribute("data-original-title", "");
                        // Получение дерева элементов БЗ
                        var tree = $("#rvml-tree-view").treeview(true);
                        // Поиск удаляемого узла начального факта
                        $.each(tree.getNodes(), function(id, node) {
                            if (node.id == data['fact_id'] && node.level == 2) {
                                // Обновление общего кол-ва начальных фактов (уменьшение tag на 1)
                                tree.getParents(node)[0].tags[0] -= 1;
                                // Удаление узла начального факта
                                tree.removeNode(node, {silent: true});
                            }
                        });
                        // Рабочая область (слой) редактора RVML
                        var production_model = document.getElementById('production-model');
                        // Очистка рабочей области (удаление всех элементов)
                        while(production_model.firstChild)
                            production_model.removeChild(production_model.firstChild);
                        // Формирование текста с сообщением
                        document.getElementById("message-text").lastChild.nodeValue =
                            "<?= Yii::t('app', 'RVML_EDITOR_PAGE_MESSAGE_DELETE_INITIAL_FACT') ?>";
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
            <?php echo Yii::t('app', 'RVML_EDITOR_PAGE_MODAL_FORM_DELETE_INITIAL_FACT_TEXT'); ?>
        </p>
    </div>

    <?php $form = ActiveForm::begin([
        'id' => 'delete-initial-fact-form',
    ]); ?>

        <?= Button::widget([
            'label' => Yii::t('app', 'BUTTON_DELETE'),
            'options' => [
                'id' => 'delete-initial-fact-button',
                'class' => 'btn-success',
                'style' => 'margin:5px'
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

<!-- Модальное окно изменения значения слота начального факта -->
<?php Modal::begin([
    'id' => 'editInitialFactSlotModalForm',
    'header' => '<h3>' . Yii::t('app', 'RVML_EDITOR_PAGE_EDIT_INITIAL_FACT_SLOT_VALUE') . '</h3>',
]); ?>

    <!-- Скрипт модального окна -->
    <script type="text/javascript">
        // Выполнение скрипта при загрузке страницы
        $(document).ready(function() {
            // Обработка нажатия кнопки сохранения
            $("#edit-initial-fact-slot-button").click(function(e) {
                var form = $("#edit-initial-fact-slot-form");
                // Ajax-запрос
                $.ajax({
                    url: "<?= Yii::$app->request->baseUrl . '/' . Lang::getCurrent()->url .
                        '/rvml-editor/edit-fact-slot' ?>",
                    type: "post",
                    data: form.serialize() + "&fact_slot_id=" + fact_slot_id,
                    dataType: "json",
                    success: function(data) {
                        // Если валидация прошла успешно (нет ошибок ввода)
                        if (data['success']) {
                            // Скрывание модального окна
                            $("#editInitialFactSlotModalForm").modal("hide");
                            // Получение дерева элементов БЗ
                            var tree = $("#rvml-tree-view").treeview(true);
                            // Цикл по всем узлам дерева элементов БЗ
                            $.each(tree.getNodes(), function(id, node) {
                                // Нахождение обновляемого узла слота начального факта
                                if (node.id == data['id']) {
                                    // Формирование нового узла слота начального факта
                                    var new_node = {
                                        id: data['id'],
                                        text: data['name'],
                                        dataType: data['data_type'],
                                        value: data['value'],
                                        description: data['description'],
                                        icon: "glyphicon glyphicon-tag",
                                        selectedIcon: "glyphicon glyphicon-arrow-right",
                                        state: [{ selected: true }]
                                    };
                                    // Обновление узла слота начального факта
                                    tree.updateNode(node, new_node, { silent: true });
                                    // Выбор обновленного узла слота начального факта
                                    tree.selectNode(new_node, { silent: false });
                                }
                            });
                        } else {
                            // Отображение ошибок ввода
                            viewErrors("#edit-initial-fact-slot-form", data);
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
        'id' => 'edit-initial-fact-slot-form',
        'enableAjaxValidation' => true,
        'enableClientValidation' => true,
    ]); ?>

        <?= $form->errorSummary($fact_slot_model); ?>

        <?= $form->field($fact_slot_model, 'name')->textInput(['maxlength' => true, 'disabled' => true]) ?>

        <?= $form->field($fact_slot_model, 'data_type')->dropDownList(
            DataType::getDataTypesArray($model->id),
            ['disabled' => true]
        ) ?>

        <?= $form->field($fact_slot_model, 'value')->textInput(['maxlength' => true]) ?>

        <?= $form->field($fact_slot_model, 'description')->textarea(['maxlength' => true, 'rows'=>6]) ?>

        <?= Button::widget([
            'label' => Yii::t('app', 'BUTTON_SAVE'),
            'options' => [
                'id' => 'edit-initial-fact-slot-button',
                'class' => 'btn-success',
                'style' => 'margin:5px'
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

<!-- Модальное окно добавления нового шаблона правила -->
<?php Modal::begin([
    'id' => 'addRuleTemplateModalForm',
    'header' => '<h3>' . Yii::t('app', 'RVML_EDITOR_PAGE_ADD_NEW_RULE_TEMPLATE') . '</h3>',
]); ?>

    <!-- Скрипт модального окна -->
    <script type="text/javascript">
        // Выполнение скрипта при загрузке страницы
        $(document).ready(function() {
            // Изменение номера у заголовков элементов шаблонов правил
            var add_dynamic_condition_form_wrapper = jQuery(".add_dynamic_rule_template_condition_form_wrapper");
            add_dynamic_condition_form_wrapper.on("afterInsert", function(e, item) {
                jQuery(".add_dynamic_rule_template_condition_form_wrapper .panel-title-condition").each(function(index) {
                    jQuery(this).html("<?= Yii::t('app', 'RVML_EDITOR_PAGE_CONDITION'); ?>: " + (index + 1));
                    $(".rule-template-operator").hide();
                });
            });
            add_dynamic_condition_form_wrapper.on("afterDelete", function(e) {
                jQuery(".add_dynamic_rule_template_condition_form_wrapper .panel-title-condition").each(function(index) {
                    jQuery(this).html("<?= Yii::t('app', 'RVML_EDITOR_PAGE_CONDITION'); ?>: " + (index + 1))
                });
            });
            var add_dynamic_action_form_wrapper = jQuery(".add_dynamic_rule_template_action_form_wrapper");
            add_dynamic_action_form_wrapper.on("afterInsert", function(e, item) {
                jQuery(".add_dynamic_rule_template_action_form_wrapper .panel-title-action").each(function(index) {
                    jQuery(this).html("<?= Yii::t('app', 'RVML_EDITOR_PAGE_ACTION'); ?>: " + (index + 1))
                });
            });
            add_dynamic_action_form_wrapper.on("afterDelete", function(e) {
                jQuery(".add_dynamic_rule_template_action_form_wrapper .panel-title-action").each(function(index) {
                    jQuery(this).html("<?= Yii::t('app', 'RVML_EDITOR_PAGE_ACTION'); ?>: " + (index + 1))
                });
            });
            // Скрытие слоя скрытого поля оператора условия для шаблона правила
            $(".rule-template-operator").hide();
            // Обработка нажатия кнопки сохранения
            $("#add-rule-template-button").click(function(e) {
                var form = $("#add-rule-template-form");
                // Ajax-запрос
                $.ajax({
                    url: "<?= Yii::$app->request->baseUrl . '/' . Lang::getCurrent()->url .
                        '/rvml-editor/add-rule-template/' . $model->id ?>",
                    type: "post",
                    data: form.serialize(),
                    dataType: "json",
                    success: function(data) {
                        // Если валидация прошла успешно (нет ошибок ввода)
                        if (data['success']) {
                            // Скрывание модального окна
                            $("#addRuleTemplateModalForm").modal("hide");
                            // Получение дерева элементов БЗ
                            var tree = $("#rvml-tree-view").treeview(true);
                            // Формирование массива условий для шаблона правила
                            var rule_template_conditions = [];
                            // Цикл по всем условиям шаблона правила
                            $.each(data['rule_template_conditions'], function(id, rt_condition) {
                                var flag = false;
                                // Цикл по шаблонам фактов
                                $.each(data['condition_fact_templates'], function(id, fact_template) {
                                    if (rt_condition['fact_template'] == fact_template['id'] && flag == false) {
                                        flag = true;
                                        // Формирование массива слотов шаблона факта
                                        var fact_template_slots = [];
                                        // Цикл по слотам шаблонов фактов
                                        $.each(data['condition_fact_template_slots'], function(id, ft_slots) {
                                            if (id == fact_template['id']) {
                                                // Добавление узлов для слотов шаблона факта в массив
                                                $.each(ft_slots, function(id, ft_slot) {
                                                    var fact_template_slot = {
                                                        id: ft_slot['id'],
                                                        text: ft_slot['name'],
                                                        dataType: ft_slot['data_type'],
                                                        defaultValue: ft_slot['default_value'],
                                                        description: ft_slot['description'],
                                                        selectedIcon: "glyphicon glyphicon-arrow-right",
                                                        icon: "glyphicon glyphicon-tag"
                                                    };
                                                    fact_template_slots.push(fact_template_slot);
                                                });
                                            }
                                        });
                                        // Формирование узла шаблона факта (условия шаблона правила)
                                        var rule_template_condition = {
                                            id: fact_template['id'],
                                            ruleTemplateConditionId: rt_condition['id'],
                                            operator: rt_condition['operator'],
                                            text: fact_template['name'],
                                            description: fact_template['description'],
                                            icon: "glyphicon glyphicon-unchecked",
                                            selectedIcon: "glyphicon glyphicon-arrow-right",
                                            nodes: fact_template_slots
                                        };
                                        rule_template_conditions.push(rule_template_condition);
                                    }
                                });
                            });
                            // Формирование массива действий для шаблона правила
                            var rule_template_actions = [];
                            // Цикл по всем действиям шаблона правила
                            $.each(data['rule_template_actions'], function(id, rt_action) {
                                var flag = false;
                                // Цикл по шаблонам фактов
                                $.each(data['action_fact_templates'], function(id, fact_template) {
                                    if (rt_action['fact_template'] == fact_template['id'] && flag == false) {
                                        flag = true;
                                        // Формирование массива слотов шаблона факта
                                        var fact_template_slots = [];
                                        // Цикл по слотам шаблонов фактов
                                        $.each(data['action_fact_template_slots'], function(id, ft_slots) {
                                            if (id == fact_template['id']) {
                                                // Добавление узлов для слотов шаблона факта в массив
                                                $.each(ft_slots, function(id, ft_slot) {
                                                    var fact_template_slot = {
                                                        id: ft_slot['id'],
                                                        text: ft_slot['name'],
                                                        dataType: ft_slot['data_type'],
                                                        defaultValue: ft_slot['default_value'],
                                                        description: ft_slot['description'],
                                                        selectedIcon: "glyphicon glyphicon-arrow-right",
                                                        icon: "glyphicon glyphicon-tag"
                                                    };
                                                    fact_template_slots.push(fact_template_slot);
                                                });
                                            }
                                        });
                                        // Формирование узла шаблона факта (действия шаблона правила)
                                        var rule_template_action = {
                                            id: fact_template['id'],
                                            ruleTemplateActionId: rt_action['id'],
                                            function: rt_action['function'],
                                            text: fact_template['name'],
                                            description: fact_template['description'],
                                            icon: "glyphicon glyphicon-unchecked",
                                            selectedIcon: "glyphicon glyphicon-arrow-right",
                                            nodes: fact_template_slots
                                        };
                                        rule_template_actions.push(rule_template_action);
                                    }
                                });
                            });
                            // Создание нового узла шаблона правила
                            var new_node = [{
                                id: data['id'],
                                salience: data['salience'],
                                text: data['name'],
                                description: data['description'],
                                icon: "glyphicon glyphicon-registration-mark",
                                selectedIcon: "glyphicon glyphicon-arrow-right",
                                state: [{ expanded: true, selected: true }],
                                nodes: [
                                    {
                                        class: "rule-template-elements",
                                        text: "<?= Yii::t('app', 'RVML_EDITOR_PAGE_CONDITIONS') ?>",
                                        color: "#428bca",
                                        selectable: false,
                                        icon: "glyphicon glyphicon-registration-mark",
                                        tags: [ rule_template_conditions.length ],
                                        state: [{ expanded: true }],
                                        nodes: rule_template_conditions
                                    },
                                    {
                                        class: "rule-template-elements",
                                        text: "<?= Yii::t('app', 'RVML_EDITOR_PAGE_ACTIONS') ?>",
                                        color: "#428bca",
                                        selectable: false,
                                        icon: "glyphicon glyphicon-registration-mark",
                                        tags: [ rule_template_actions.length ],
                                        state: [{ expanded: true }],
                                        nodes: rule_template_actions
                                    }
                                ]
                            }];
                            // Поиск родительского узла для шаблона правила
                            $.each(tree.getNodes(), function(id, node) {
                                if (node.id == 'rule-templates-node') {
                                    // Обновление общего кол-ва шаблонов правил (увеличение tag на 1)
                                    node.tags[0] += 1;
                                    // Добавление нового узла шаблона правила в родительский узел шаблонов правил
                                    tree.addNode(new_node, node, false, { silent: true });
                                    // Выбор добавленного узла шаблона правила
                                    tree.selectNode(new_node, { silent: false });
                                }
                            });
                            // Изменение цвета текста у узлов групп условий и действий
                            var elements = document.getElementsByClassName("rule-template-elements");
                            for (var i = 0; i < elements.length; i++)
                                elements[i].style.color = "#428bca";
                        } else {
                            // Отображение ошибок ввода
                            viewErrors("#add-rule-template-form", data);
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
        'id' => 'add-rule-template-form',
        'enableAjaxValidation' => true,
        'enableClientValidation' => true,
    ]); ?>

        <?= $form->errorSummary($rule_template_model); ?>

        <?= $form->field($rule_template_model, 'name')->textInput(['maxlength' => true]) ?>

        <?= $form->field($rule_template_model, 'salience')->textInput(['maxlength' => true]) ?>

        <?= $form->field($rule_template_model, 'description')->textarea(['maxlength' => true, 'rows'=>6]) ?>

        <?php DynamicFormWidget::begin([
            'widgetContainer' => 'add_dynamic_rule_template_condition_form_wrapper', // only alphanumeric characters plus "_" [A-Za-z0-9_]
            'widgetBody' => '.container-items', // css class selector
            'widgetItem' => '.item', // css class
            'limit' => 99, // the maximum times, an element can be cloned (default 999)
            'min' => 1, // 0 or 1 (default 1)
            'insertButton' => '.add-item', // css class
            'deleteButton' => '.remove-item', // css class
            'model' => $rule_template_condition_models[0],
            'formId' => 'add-rule-template-form',
            'formFields' => [
                'operator',
                'fact_template'
            ],
        ]); ?>
            <div class="panel panel-default">
                <div class="panel-heading">
                    <i class="fa fa-envelope"></i><?= Yii::t('app', 'RVML_EDITOR_PAGE_CONDITIONS'); ?>
                    <button type="button" class="pull-right add-item btn btn-success btn-xs">
                        <i class="glyphicon glyphicon-plus"></i> <?= Yii::t('app', 'RVML_EDITOR_PAGE_ADD_CONDITION'); ?>
                    </button>
                    <div class="clearfix"></div>
                </div>
                <div class="panel-body container-items"><!-- widgetContainer -->
                    <?php foreach ($rule_template_condition_models as $index => $rule_template_condition_model): ?>
                        <div class="item panel panel-default"><!-- widgetBody -->
                            <div class="panel-heading">
                                <span class="panel-title-condition"><?= Yii::t('app', 'RVML_EDITOR_PAGE_CONDITION'); ?>:
                                    <?= ($index + 1) ?></span>
                                <button type="button" class="pull-right remove-item btn btn-danger btn-xs">
                                    <i class="glyphicon glyphicon-minus"></i>
                                </button>
                                <div class="clearfix"></div>
                            </div>
                            <div class="panel-body">
                                <?php if (!$rule_template_condition_model->isNewRecord) {
                                    echo Html::activeHiddenInput($rule_template_condition_model, "[{$index}]id");
                                } ?>
                                <div class="rule-template-operator">
                                    <?= $form->field($rule_template_condition_model, "[{$index}]operator")
                                        ->dropDownList(RuleTemplateCondition::getOperatorsArray(),
                                            ['style' => 'display: none']) ?>
                                </div>
                                <?= $form->field($rule_template_condition_model, "[{$index}]fact_template")
                                    ->dropDownList(FactTemplate::getFactTemplatesArray($model->id)) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php DynamicFormWidget::end(); ?>

        <?php DynamicFormWidget::begin([
            'widgetContainer' => 'add_dynamic_rule_template_action_form_wrapper', // only alphanumeric characters plus "_" [A-Za-z0-9_]
            'widgetBody' => '.container-items', // css class selector
            'widgetItem' => '.item', // css class
            'limit' => 99, // the maximum times, an element can be cloned (default 999)
            'min' => 1, // 0 or 1 (default 1)
            'insertButton' => '.add-item', // css class
            'deleteButton' => '.remove-item', // css class
            'model' => $rule_template_action_models[0],
            'formId' => 'add-rule-template-form',
            'formFields' => [
                'function',
                'fact_template'
            ],
        ]); ?>
            <div class="panel panel-default">
                <div class="panel-heading">
                    <i class="fa fa-envelope"></i><?= Yii::t('app', 'RVML_EDITOR_PAGE_ACTIONS'); ?>
                    <button type="button" class="pull-right add-item btn btn-success btn-xs">
                        <i class="glyphicon glyphicon-plus"></i> <?= Yii::t('app', 'RVML_EDITOR_PAGE_ADD_ACTION'); ?>
                    </button>
                    <div class="clearfix"></div>
                </div>
                <div class="panel-body container-items"><!-- widgetContainer -->
                    <?php foreach ($rule_template_action_models as $index => $rule_template_action_model): ?>
                        <div class="item panel panel-default"><!-- widgetBody -->
                            <div class="panel-heading">
                                <span class="panel-title-action"><?= Yii::t('app', 'RVML_EDITOR_PAGE_ACTION'); ?>:
                                    <?= ($index + 1) ?></span>
                                <button type="button" class="pull-right remove-item btn btn-danger btn-xs">
                                    <i class="glyphicon glyphicon-minus"></i>
                                </button>
                                <div class="clearfix"></div>
                            </div>
                            <div class="panel-body">
                                <?php if (!$rule_template_action_model->isNewRecord) {
                                    echo Html::activeHiddenInput($rule_template_action_model, "[{$index}]id");
                                } ?>
                                <div class="row">
                                    <div class="col-sm-4">
                                        <?= $form->field($rule_template_action_model, "[{$index}]function")
                                            ->dropDownList(RuleTemplateAction::getFunctionsArray()) ?>
                                    </div>
                                    <div class="col-sm-8">
                                        <?= $form->field($rule_template_action_model, "[{$index}]fact_template")
                                            ->dropDownList(FactTemplate::getFactTemplatesArray($model->id)) ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php DynamicFormWidget::end(); ?>

        <?= Button::widget([
            'label' => Yii::t('app', 'BUTTON_ADD'),
            'options' => [
                'id' => 'add-rule-template-button',
                'class' => 'btn-success',
                'style' => 'margin:5px'
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

<!-- Модальное окно изменения шаблона правила -->
<?php Modal::begin([
    'id' => 'editRuleTemplateModalForm',
    'header' => '<h3>' . Yii::t('app', 'RVML_EDITOR_PAGE_EDIT_RULE_TEMPLATE') . '</h3>',
]); ?>

    <!-- Скрипт модального окна -->
    <script type="text/javascript">
        // Выполнение скрипта при загрузке страницы
        $(document).on('ready pjax:success', function() {
            // Изменение номера у заголовков элементов шаблонов правил
            var edit_dynamic_condition_form_wrapper = jQuery(".edit_dynamic_rule_template_condition_form_wrapper");
            edit_dynamic_condition_form_wrapper.on("afterInsert", function(e, item) {
                jQuery(".edit_dynamic_rule_template_condition_form_wrapper .panel-title-condition").each(function(index) {
                    jQuery(this).html("<?= Yii::t('app', 'RVML_EDITOR_PAGE_CONDITION'); ?>: " + (index + 1));
                    $(".rule-template-operator").hide();
                });
            });
            edit_dynamic_condition_form_wrapper.on("afterDelete", function(e) {
                jQuery(".edit_dynamic_rule_template_condition_form_wrapper .panel-title-condition").each(function(index) {
                    jQuery(this).html("<?= Yii::t('app', 'RVML_EDITOR_PAGE_CONDITION'); ?>: " + (index + 1))
                });
            });
            var edit_dynamic_action_form_wrapper = jQuery(".edit_dynamic_rule_template_action_form_wrapper");
            edit_dynamic_action_form_wrapper.on("afterInsert", function(e, item) {
                jQuery(".edit_dynamic_rule_template_action_form_wrapper .panel-title-action").each(function(index) {
                    jQuery(this).html("<?= Yii::t('app', 'RVML_EDITOR_PAGE_ACTION'); ?>: " + (index + 1))
                });
            });
            edit_dynamic_action_form_wrapper.on("afterDelete", function(e) {
                jQuery(".edit_dynamic_rule_template_action_form_wrapper .panel-title-action").each(function(index) {
                    jQuery(this).html("<?= Yii::t('app', 'RVML_EDITOR_PAGE_ACTION'); ?>: " + (index + 1))
                });
            });
            // Скрытие слоя скрытого поля оператора условия для шаблона правила
            $(".rule-template-operator").hide();
            // Обработка нажатия кнопки сохранения
            $("#edit-rule-template-button").click(function(e) {
                var form = $("#edit-rule-template-form");
                // Ajax-запрос
                $.ajax({
                    url: "<?= Yii::$app->request->baseUrl . '/' . Lang::getCurrent()->url .
                        '/rvml-editor/edit-rule-template' ?>",
                    type: "post",
                    data: form.serialize() + "&rule_template_id=" + rule_template_id,
                    dataType: "json",
                    success: function(data) {
                        // Если валидация прошла успешно (нет ошибок ввода)
                        if (data['success']) {
                            // Скрывание модального окна
                            $("#editRuleTemplateModalForm").modal('hide');
                            // Получение дерева элементов БЗ
                            var tree = $("#rvml-tree-view").treeview(true);
                            // Формирование массива условий для шаблона правила
                            var rule_template_conditions = [];
                            // Цикл по всем условиям шаблона правила
                            $.each(data['rule_template_conditions'], function(id, rt_condition) {
                                var flag = false;
                                // Цикл по шаблонам фактов
                                $.each(data['condition_fact_templates'], function(id, fact_template) {
                                    if (rt_condition['fact_template'] == fact_template['id'] && flag == false) {
                                        flag = true;
                                        // Формирование массива слотов шаблона факта
                                        var fact_template_slots = [];
                                        // Цикл по слотам шаблонов фактов
                                        $.each(data['condition_fact_template_slots'], function(id, ft_slots) {
                                            if (id == fact_template['id']) {
                                                // Добавление узлов для слотов шаблона факта в массив
                                                $.each(ft_slots, function(id, ft_slot) {
                                                    var fact_template_slot = {
                                                        id: ft_slot['id'],
                                                        text: ft_slot['name'],
                                                        dataType: ft_slot['data_type'],
                                                        defaultValue: ft_slot['default_value'],
                                                        description: ft_slot['description'],
                                                        selectedIcon: "glyphicon glyphicon-arrow-right",
                                                        icon: "glyphicon glyphicon-tag"
                                                    };
                                                    fact_template_slots.push(fact_template_slot);
                                                });
                                            }
                                        });
                                        // Формирование узла шаблона факта (условия шаблона правила)
                                        var rule_template_condition = {
                                            id: fact_template['id'],
                                            ruleTemplateConditionId: rt_condition['id'],
                                            operator: rt_condition['operator'],
                                            text: fact_template['name'],
                                            description: fact_template['description'],
                                            icon: "glyphicon glyphicon-unchecked",
                                            selectedIcon: "glyphicon glyphicon-arrow-right",
                                            nodes: fact_template_slots
                                        };
                                        rule_template_conditions.push(rule_template_condition);
                                    }
                                });
                            });
                            // Формирование массива действий для шаблона правила
                            var rule_template_actions = [];
                            // Цикл по всем действиям шаблона правила
                            $.each(data['rule_template_actions'], function(id, rt_action) {
                                var flag = false;
                                // Цикл по шаблонам фактов
                                $.each(data['action_fact_templates'], function(id, fact_template) {
                                    if (rt_action['fact_template'] == fact_template['id'] && flag == false) {
                                        flag = true;
                                        // Формирование массива слотов шаблона факта
                                        var fact_template_slots = [];
                                        // Цикл по слотам шаблонов фактов
                                        $.each(data['action_fact_template_slots'], function(id, ft_slots) {
                                            if (id == fact_template['id']) {
                                                // Добавление узлов для слотов шаблона факта в массив
                                                $.each(ft_slots, function(id, ft_slot) {
                                                    var fact_template_slot = {
                                                        id: ft_slot['id'],
                                                        text: ft_slot['name'],
                                                        dataType: ft_slot['data_type'],
                                                        defaultValue: ft_slot['default_value'],
                                                        description: ft_slot['description'],
                                                        selectedIcon: "glyphicon glyphicon-arrow-right",
                                                        icon: "glyphicon glyphicon-tag"
                                                    };
                                                    fact_template_slots.push(fact_template_slot);
                                                });
                                            }
                                        });
                                        // Формирование узла шаблона факта (действия шаблона правила)
                                        var rule_template_action = {
                                            id: fact_template['id'],
                                            ruleTemplateActionId: rt_action['id'],
                                            function: rt_action['function'],
                                            text: fact_template['name'],
                                            description: fact_template['description'],
                                            icon: "glyphicon glyphicon-unchecked",
                                            selectedIcon: "glyphicon glyphicon-arrow-right",
                                            nodes: fact_template_slots
                                        };
                                        rule_template_actions.push(rule_template_action);
                                    }
                                });
                            });
                            // Поиск родительского узла для шаблона правила
                            $.each(tree.getNodes(), function(id, node) {
                                // Нахождение обновляемого узла шаблона правила
                                if (node.id == data['id'] && node.level == 2) {
                                    // Создание нового узла шаблона правила
                                    var new_node = {
                                        id: data['id'],
                                        salience: data['salience'],
                                        text: data['name'],
                                        description: data['description'],
                                        icon: "glyphicon glyphicon-registration-mark",
                                        selectedIcon: "glyphicon glyphicon-arrow-right",
                                        state: [{ expanded: true, selected: true }],
                                        nodes: [
                                            {
                                                class: "rule-template-elements",
                                                text: "<?= Yii::t('app', 'RVML_EDITOR_PAGE_CONDITIONS') ?>",
                                                color: "#428bca",
                                                selectable: false,
                                                icon: "glyphicon glyphicon-registration-mark",
                                                tags: [ rule_template_conditions.length ],
                                                state: [{ expanded: true }],
                                                nodes: rule_template_conditions
                                            },
                                            {
                                                class: "rule-template-elements",
                                                text: "<?= Yii::t('app', 'RVML_EDITOR_PAGE_ACTIONS') ?>",
                                                color: "#428bca",
                                                selectable: false,
                                                icon: "glyphicon glyphicon-registration-mark",
                                                tags: [ rule_template_actions.length ],
                                                state: [{ expanded: true }],
                                                nodes: rule_template_actions
                                            }
                                        ]
                                    };
                                    // Обновление узла шаблона правила
                                    tree.updateNode(node, new_node, { silent: true });
                                    // Выбор добавленного узла шаблона правила
                                    tree.selectNode(new_node, { silent: false });
                                }
                            });
                        } else {
                            // Отображение ошибок ввода
                            viewErrors("#edit-rule-template-form", data);
                        }
                    },
                    error: function() {
                        alert('Error!');
                    }
                });
            });
        });
    </script>

    <?php Pjax::begin(); ?>

    <?php $form = ActiveForm::begin([
        'id' => 'edit-rule-template-form',
        'enableAjaxValidation' => true,
        'enableClientValidation' => true
    ]); ?>

        <?= Alert::widget([
            'options' => ['class' => 'edit-rule-template-alert alert-warning'],
            'closeButton' => false
        ]); ?>

        <?= $form->errorSummary($rule_template_model); ?>

        <?= $form->field($rule_template_model, 'name')->textInput(['maxlength' => true]) ?>

        <?= $form->field($rule_template_model, 'salience')->textInput(['maxlength' => true]) ?>

        <?= $form->field($rule_template_model, 'description')->textarea(['maxlength' => true, 'rows'=>6]) ?>

        <?php DynamicFormWidget::begin([
            'widgetContainer' => 'edit_dynamic_rule_template_condition_form_wrapper', // only alphanumeric characters plus "_" [A-Za-z0-9_]
            'widgetBody' => '.container-items', // css class selector
            'widgetItem' => '.item', // css class
            'limit' => 99, // the maximum times, an element can be cloned (default 999)
            'min' => 1, // 0 or 1 (default 1)
            'insertButton' => '.add-item', // css class
            'deleteButton' => '.remove-item', // css class
            'model' => $rule_template_condition_models[0],
            'formId' => 'edit-rule-template-form',
            'formFields' => [
                'operator',
                'fact_template'
            ],
        ]); ?>
            <div class="panel panel-default">
                <div class="panel-heading">
                    <i class="fa fa-envelope"></i><?= Yii::t('app', 'RVML_EDITOR_PAGE_CONDITIONS'); ?>
                    <button type="button" class="pull-right add-item btn btn-success btn-xs">
                        <i class="glyphicon glyphicon-plus"></i> <?= Yii::t('app', 'RVML_EDITOR_PAGE_ADD_CONDITION'); ?>
                    </button>
                    <div class="clearfix"></div>
                </div>
                <div class="panel-body container-items"><!-- widgetContainer -->
                    <?php foreach ($rule_template_condition_models as $index => $rule_template_condition_model): ?>
                        <div class="item panel panel-default"><!-- widgetBody -->
                            <div class="panel-heading">
                                <span class="panel-title-condition"><?= Yii::t('app', 'RVML_EDITOR_PAGE_CONDITION'); ?>:
                                    <?= ($index + 1) ?></span>
                                <button type="button" class="pull-right remove-item btn btn-danger btn-xs">
                                    <i class="glyphicon glyphicon-minus"></i>
                                </button>
                                <div class="clearfix"></div>
                            </div>
                            <div class="panel-body">
                                <?php if (!$rule_template_condition_model->isNewRecord) {
                                    echo Html::activeHiddenInput($rule_template_condition_model, "[{$index}]id");
                                } ?>
                                <div class="rule-template-operator">
                                    <?= $form->field($rule_template_condition_model, "[{$index}]operator")
                                        ->dropDownList(RuleTemplateCondition::getOperatorsArray(),
                                            ['style' => 'display: none']) ?>
                                </div>
                                <?= $form->field($rule_template_condition_model, "[{$index}]fact_template")
                                    ->dropDownList(FactTemplate::getFactTemplatesArray($model->id)) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php DynamicFormWidget::end(); ?>

        <?php DynamicFormWidget::begin([
            'widgetContainer' => 'edit_dynamic_rule_template_action_form_wrapper', // only alphanumeric characters plus "_" [A-Za-z0-9_]
            'widgetBody' => '.container-items', // css class selector
            'widgetItem' => '.item', // css class
            'limit' => 99, // the maximum times, an element can be cloned (default 999)
            'min' => 1, // 0 or 1 (default 1)
            'insertButton' => '.add-item', // css class
            'deleteButton' => '.remove-item', // css class
            'model' => $rule_template_action_models[0],
            'formId' => 'edit-rule-template-form',
            'formFields' => [
                'function',
                'fact_template'
            ],
        ]); ?>
            <div class="panel panel-default">
                <div class="panel-heading">
                    <i class="fa fa-envelope"></i><?= Yii::t('app', 'RVML_EDITOR_PAGE_ACTIONS'); ?>
                    <button type="button" class="pull-right add-item btn btn-success btn-xs">
                        <i class="glyphicon glyphicon-plus"></i> <?= Yii::t('app', 'RVML_EDITOR_PAGE_ADD_ACTION'); ?>
                    </button>
                    <div class="clearfix"></div>
                </div>
                <div class="panel-body container-items"><!-- widgetContainer -->
                    <?php foreach ($rule_template_action_models as $index => $rule_template_action_model): ?>
                        <div class="item panel panel-default"><!-- widgetBody -->
                            <div class="panel-heading">
                                <span class="panel-title-action"><?= Yii::t('app', 'RVML_EDITOR_PAGE_ACTION'); ?>:
                                    <?= ($index + 1) ?></span>
                                <button type="button" class="pull-right remove-item btn btn-danger btn-xs">
                                    <i class="glyphicon glyphicon-minus"></i>
                                </button>
                                <div class="clearfix"></div>
                            </div>
                            <div class="panel-body">
                                <?php if (!$rule_template_action_model->isNewRecord) {
                                    echo Html::activeHiddenInput($rule_template_action_model, "[{$index}]id");
                                } ?>
                                <div class="row">
                                    <div class="col-sm-4">
                                        <?= $form->field($rule_template_action_model, "[{$index}]function")
                                            ->dropDownList(RuleTemplateAction::getFunctionsArray()) ?>
                                    </div>
                                    <div class="col-sm-8">
                                        <?= $form->field($rule_template_action_model, "[{$index}]fact_template")
                                            ->dropDownList(FactTemplate::getFactTemplatesArray($model->id)) ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php DynamicFormWidget::end(); ?>

        <?= Button::widget([
            'label' => Yii::t('app', 'BUTTON_SAVE'),
            'options' => [
                'id' => 'edit-rule-template-button',
                'class' => 'btn-success',
                'style' => 'margin:5'
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

    <?= Html::beginForm(['/rvml-editor/' . $model->id], 'post',
        ['id' => 'pjax-rule-template-form', 'data-pjax' => '', 'style' => 'display:none']); ?>
        <?= Html::hiddenInput('rule-template-id', Yii::$app->request->post('rule-template-id'),
            ['id' => 'pjax-rule-template-input']) ?>
        <?= Html::submitButton('Вычислить', ['id' => 'pjax-rule-template-button', 'data-pjax' => '']) ?>
    <?= Html::endForm() ?>

    <?php Pjax::end(); ?>

<?php Modal::end(); ?>

<!-- Модальное окно удаления шаблона правила -->
<?php Modal::begin([
    'id' => 'deleteRuleTemplateModalForm',
    'header' => '<h3>' . Yii::t('app', 'RVML_EDITOR_PAGE_DELETE_RULE_TEMPLATE') . '</h3>',
]); ?>

    <!-- Скрипт модального окна -->
    <script type="text/javascript">
        $(document).ready(function() {
            // Обработка нажатия кнопки удаления
            $("#delete-rule-template-button").click(function(e) {
                e.preventDefault();
                // Ajax-запрос
                $.ajax({
                    url: "<?= Yii::$app->request->baseUrl . '/' . Lang::getCurrent()->url .
                        '/rvml-editor/delete-rule-template' ?>",
                    type: "post",
                    data: "YII_CSRF_TOKEN=<?= Yii::$app->request->csrfToken ?>&rule_template_id=" + rule_template_id,
                    dataType: "json",
                    success: function(data) {
                        // Скрывание модального окна
                        $("#deleteRuleTemplateModalForm").modal('hide');
                        // Кнопки на панели инструментов дерева элементов БЗ
                        var add_element_button = document.getElementById("add-element-button");
                        var edit_element_button = document.getElementById("edit-element-button");
                        var delete_element_button = document.getElementById("delete-element-button");
                        // Деактивация кнопок на панели дерева элементов БЗ
                        add_element_button.classList.add("disabled");
                        add_element_button.setAttribute("data-target", "");
                        add_element_button.setAttribute("data-original-title", "");
                        edit_element_button.classList.add("disabled");
                        edit_element_button.setAttribute("data-target", "");
                        edit_element_button.setAttribute("data-original-title", "");
                        delete_element_button.classList.add("disabled");
                        delete_element_button.setAttribute("data-target", "");
                        delete_element_button.setAttribute("data-original-title", "");
                        // Получение дерева элементов БЗ
                        var tree = $("#rvml-tree-view").treeview(true);
                        // Цикл по удаляемым правилам
                        $.each(data['rules'], function(id, rule) {
                            var removed_node;
                            $.each(tree.getNodes(), function(id, node) {
                                if (node.id == rule['id'])
                                    removed_node = node;
                                // Обновление общего кол-ва правил (уменьшение tag на 1)
                                if (node.id == 'rules-node')
                                    node.tags[0] -= 1;
                            });
                            // Удаление узла правила
                            tree.removeNode(removed_node, { silent: true });
                        });
                        // Поиск удаляемого узла шаблона правила
                        $.each(tree.getNodes(), function(id, node) {
                            if (node.id == data['rule_template_id'] && node.level == 2) {
                                // Обновление общего кол-ва шаблонов правил (уменьшение tag на 1)
                                tree.getParents(node)[0].tags[0] -= 1;
                                // Удаление узла шаблона правила
                                tree.removeNode(node, {silent: true});
                            }
                        });
                        // Рабочая область (слой) редактора RVML
                        var production_model = document.getElementById('production-model');
                        // Очистка рабочей области (удаление всех элементов)
                        while(production_model.firstChild)
                            production_model.removeChild(production_model.firstChild);
                        // Формирование текста с сообщением
                        document.getElementById("message-text").lastChild.nodeValue =
                            "<?= Yii::t('app', 'RVML_EDITOR_PAGE_MESSAGE_DELETE_RULE_TEMPLATE') ?>";
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
            <?php echo Yii::t('app', 'RVML_EDITOR_PAGE_MODAL_FORM_DELETE_RULE_TEMPLATE_TEXT'); ?>
        </p>
    </div>

    <?php $form = ActiveForm::begin([
        'id' => 'delete-rule-template-form',
    ]); ?>

        <?= Alert::widget([
            'options' => ['class' => 'delete-rule-template-alert alert-warning'],
            'closeButton' => false
        ]); ?>

        <?= Button::widget([
            'label' => Yii::t('app', 'BUTTON_DELETE'),
            'options' => [
                'id' => 'delete-rule-template-button',
                'class' => 'btn-success',
                'style' => 'margin:5px'
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

<!-- Модальное окно добавления нового правила -->
<?php Modal::begin([
    'id' => 'addRuleModalForm',
    'header' => '<h3>' . Yii::t('app', 'RVML_EDITOR_PAGE_ADD_NEW_RULE') . '</h3>',
]); ?>

    <!-- Скрипт модального окна -->
    <script type="text/javascript">
        // Выполнение скрипта при загрузке страницы
        $(document).ready(function() {
            // Изменение номера у заголовков элементов шаблонов правил
            var add_dynamic_condition_form_wrapper = jQuery(".add_dynamic_rule_condition_form_wrapper");
            add_dynamic_condition_form_wrapper.on("afterInsert", function(e, item) {
                jQuery(".add_dynamic_rule_condition_form_wrapper .panel-title-condition").each(function(index) {
                    jQuery(this).html("<?= Yii::t('app', 'RVML_EDITOR_PAGE_CONDITION'); ?>: " + (index + 1));
                    $(".rule-template-operator").hide();
                });
            });
            add_dynamic_condition_form_wrapper.on("afterDelete", function(e) {
                jQuery(".add_dynamic_rule_condition_form_wrapper .panel-title-condition").each(function(index) {
                    jQuery(this).html("<?= Yii::t('app', 'RVML_EDITOR_PAGE_CONDITION'); ?>: " + (index + 1))
                });
            });
            var add_dynamic_action_form_wrapper = jQuery(".add_dynamic_rule_action_form_wrapper");
            add_dynamic_action_form_wrapper.on("afterInsert", function(e, item) {
                jQuery(".add_dynamic_rule_action_form_wrapper .panel-title-action").each(function(index) {
                    jQuery(this).html("<?= Yii::t('app', 'RVML_EDITOR_PAGE_ACTION'); ?>: " + (index + 1))
                });
            });
            add_dynamic_action_form_wrapper.on("afterDelete", function(e) {
                jQuery(".add_dynamic_rule_action_form_wrapper .panel-title-action").each(function(index) {
                    jQuery(this).html("<?= Yii::t('app', 'RVML_EDITOR_PAGE_ACTION'); ?>: " + (index + 1))
                });
            });
            // Поле наименования правила
            var rule_name = document.getElementById("rule-name");
            // Список шаблонов правил
            var rule_template = document.getElementById("rule-rule_template");
            // Определение наименования правила в соответствии с выбранным шаблоном правила
            for (var i = 0; i < rule_template.options.length; i++)
                if (rule_template.options[i].selected)
                    rule_name.value = rule_template.options[i].text;
            // Действие при выборе шаблона правила
            $("#rule-rule_template").change(function() {
                for (var i = 0; i < rule_template.options.length; i++)
                    if (rule_template.options[i].selected) {
                        // Подставление наименования шаблона правила в наименование конкретного правила
                        rule_name.value = rule_template.options[i].text;
                        // Ajax-запрос
                        $.ajax({
                            url: "<?= Yii::$app->request->baseUrl . '/' . Lang::getCurrent()->url .
                                '/rvml-editor/get-rule-template-parameters/' ?>" + rule_template.options[i].value,
                            type: "get",
                            dataType: "json",
                            success: function(data) {
                                // Удаление всех условий правила
                                $(".add_dynamic_rule_condition_form_wrapper .remove-item").click();
                                // Удаление всех действий правила
                                $(".add_dynamic_rule_action_form_wrapper .remove-item").click();
                                // Определение кол-ва условий правила
                                var i = 1;
                                while (i < data['rule_template_conditions'].length) {
                                    $("#add-rule-condition").click();
                                    i++;
                                }
                                // Определение кол-ва действий правила
                                var j = 1;
                                while (j < data['rule_template_actions'].length) {
                                    $("#add-rule-action").click();
                                    j++;
                                }
                                // Определение соответствующих значений наименования условий правила
                                jQuery(".add_dynamic_rule_condition_form_wrapper .rule-template-condition-name select")
                                    .each(function(id) {
                                        for (var i = 0; i < this.options.length; i++)
                                            if (this.options[i].value == data['rule_template_conditions'][id]['fact_template'])
                                                this.options[i].selected = true;
                                    });
                                // Определение соответствующих значений наименования действий правила
                                jQuery(".add_dynamic_rule_action_form_wrapper .rule-template-action-name select")
                                    .each(function(id) {
                                        for (var i = 0; i < this.options.length; i++)
                                            if (this.options[i].value == data['rule_template_actions'][id]['fact_template'])
                                                this.options[i].selected = true;
                                    });
                                // Определение соответствующих значений функций действий правила
                                jQuery(".add_dynamic_rule_action_form_wrapper .rule-template-action-function select")
                                    .each(function(id) {
                                        for (var i = 0; i < this.options.length; i++)
                                            if (this.options[i].value == data['rule_template_actions'][id]['function'])
                                                this.options[i].selected = true;
                                    });
                            },
                            error: function() {
                                alert('Error!');
                            }
                        });
                    }
            });
            // Скрытие слоя скрытого поля оператора условия для правила
            $(".rule-operator").hide();
            // Обработка нажатия кнопки сохранения
            $("#add-rule-button").click(function(e) {
                var form = $("#add-rule-form");
                // Ajax-запрос
                $.ajax({
                    url: "<?= Yii::$app->request->baseUrl . '/' . Lang::getCurrent()->url .
                        '/rvml-editor/add-rule/' . $model->id ?>",
                    type: "post",
                    data: form.serialize(),
                    dataType: "json",
                    success: function(data) {
                        // Если валидация прошла успешно (нет ошибок ввода)
                        if (data['success']) {
                            // Скрывание модального окна
                            $("#addRuleModalForm").modal("hide");
                            // Получение дерева элементов БЗ
                            var tree = $("#rvml-tree-view").treeview(true);
                            // Формирование массива условий правила
                            var rule_conditions = [];
                            // Цикл по всем условиям правила
                            $.each(data['rule_conditions'], function(id, condition) {
                                var flag = false;
                                // Цикл по фактам
                                $.each(data['condition_facts'], function(id, fact) {
                                    if (condition['fact'] == fact['id'] && flag == false) {
                                        flag = true;
                                        // Формирование массива слотов факта
                                        var fact_slots = [];
                                        // Цикл по слотам фактов
                                        $.each(data['condition_fact_slots'], function(id, f_slots) {
                                            if (id == fact['id']) {
                                                // Добавление узлов для слотов факта в массив
                                                $.each(f_slots, function(id, f_slot) {
                                                    var fact_slot = {
                                                        id: f_slot['id'],
                                                        text: f_slot['name'],
                                                        dataType: f_slot['data_type'],
                                                        value: f_slot['value'],
                                                        description: f_slot['description'],
                                                        selectedIcon: "glyphicon glyphicon-arrow-right",
                                                        icon: "glyphicon glyphicon-tag"
                                                    };
                                                    fact_slots.push(fact_slot);
                                                });
                                            }
                                        });
                                        // Формирование узла факта (условия правила)
                                        var rule_condition = {
                                            id: fact['id'],
                                            factTemplateId: fact['fact_template'],
                                            ruleConditionId: condition['id'],
                                            operator: condition['operator'],
                                            text: fact['name'],
                                            certaintyFactor: fact['certainty_factor'],
                                            description: fact['description'],
                                            icon: "glyphicon glyphicon-stop",
                                            selectedIcon: "glyphicon glyphicon-arrow-right",
                                            nodes: fact_slots
                                        };
                                        rule_conditions.push(rule_condition);
                                    }
                                });
                            });
                            // Формирование массива действий для правила
                            var rule_actions = [];
                            // Цикл по всем действиям правила
                            $.each(data['rule_actions'], function(id, action) {
                                var flag = false;
                                // Цикл по фактам
                                $.each(data['action_facts'], function(id, fact) {
                                    if (action['fact'] == fact['id'] && flag == false) {
                                        flag = true;
                                        // Формирование массива слотов факта
                                        var fact_slots = [];
                                        // Цикл по слотам фактов
                                        $.each(data['action_fact_slots'], function(id, f_slots) {
                                            if (id == fact['id']) {
                                                // Добавление узлов для слотов факта в массив
                                                $.each(f_slots, function(id, f_slot) {
                                                    var fact_slot = {
                                                        id: f_slot['id'],
                                                        text: f_slot['name'],
                                                        dataType: f_slot['data_type'],
                                                        value: f_slot['value'],
                                                        description: f_slot['description'],
                                                        selectedIcon: "glyphicon glyphicon-arrow-right",
                                                        icon: "glyphicon glyphicon-tag"
                                                    };
                                                    fact_slots.push(fact_slot);
                                                });
                                            }
                                        });
                                        // Формирование узла факта (действия правила)
                                        var rule_action = {
                                            id: fact['id'],
                                            factTemplateId: fact['fact_template'],
                                            ruleActionId: action['id'],
                                            function: action['function'],
                                            text: fact['name'],
                                            certaintyFactor: fact['certainty_factor'],
                                            description: fact['description'],
                                            icon: "glyphicon glyphicon-stop",
                                            selectedIcon: "glyphicon glyphicon-arrow-right",
                                            nodes: fact_slots
                                        };
                                        rule_actions.push(rule_action);
                                    }
                                });
                            });
                            // Создание нового узла шаблона правила
                            var new_node = [{
                                id: data['id'],
                                ruleTemplateId: data['rule_template'],
                                certaintyFactor: data['certainty_factor'],
                                salience: data['salience'],
                                text: data['name'],
                                description: data['description'],
                                icon: "glyphicon glyphicon-record",
                                selectedIcon: "glyphicon glyphicon-arrow-right",
                                state: [{ expanded: true, selected: true }],
                                nodes: [
                                    {
                                        class: "rule-elements",
                                        text: "<?= Yii::t('app', 'RVML_EDITOR_PAGE_CONDITIONS') ?>",
                                        color: "#428bca",
                                        selectable: false,
                                        icon: "glyphicon glyphicon-record",
                                        tags: [ rule_conditions.length ],
                                        state: [{ expanded: true }],
                                        nodes: rule_conditions
                                    },
                                    {
                                        class: "rule-elements",
                                        text: "<?= Yii::t('app', 'RVML_EDITOR_PAGE_ACTIONS') ?>",
                                        color: "#428bca",
                                        selectable: false,
                                        icon: "glyphicon glyphicon-record",
                                        tags: [ rule_actions.length ],
                                        state: [{ expanded: true }],
                                        nodes: rule_actions
                                    }
                                ]
                            }];
                            // Поиск родительского узла для правила
                            $.each(tree.getNodes(), function(id, node) {
                                if (node.id == 'rules-node') {
                                    // Обновление общего кол-ва правил (увеличение tag на 1)
                                    node.tags[0] += 1;
                                    // Добавление нового узла правила в родительский узел правил
                                    tree.addNode(new_node, node, false, { silent: true });
                                    // Выбор добавленного узла правила
                                    tree.selectNode(new_node, { silent: false });
                                }
                            });
                            // Изменение цвета текста у узлов групп условий и действий
                            var elements = document.getElementsByClassName("rule-elements");
                            for (var i = 0; i < elements.length; i++)
                                elements[i].style.color = "#428bca";
                        } else {
                            // Отображение ошибок ввода
                            viewErrors("#add-rule-form", data);
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
        'id' => 'add-rule-form',
        'enableAjaxValidation' => true,
        'enableClientValidation' => true,
    ]); ?>

        <?= $form->errorSummary($rule_model); ?>

        <?= $form->field($rule_model, 'rule_template')->dropDownList(RuleTemplate::getRuleTemplatesArray($model->id)) ?>

        <?= $form->field($rule_model, 'name')->textInput(['maxlength' => true]) ?>

        <?= $form->field($rule_model, 'certainty_factor')->textInput(['maxlength' => true]) ?>

        <?= $form->field($rule_model, 'salience')->textInput(['maxlength' => true]) ?>

        <?= $form->field($rule_model, 'description')->textarea(['maxlength' => true, 'rows'=>6]) ?>

        <?php DynamicFormWidget::begin([
            'widgetContainer' => 'add_dynamic_rule_condition_form_wrapper', // only alphanumeric characters plus "_" [A-Za-z0-9_]
            'widgetBody' => '.container-items', // css class selector
            'widgetItem' => '.item', // css class
            'limit' => 99, // the maximum times, an element can be cloned (default 999)
            'min' => 1, // 0 or 1 (default 1)
            'insertButton' => '.add-item', // css class
            'deleteButton' => '.remove-item', // css class
            'model' => $rule_template_condition_models[0],
            'formId' => 'add-rule-form',
            'formFields' => [
                'operator',
                'fact_template'
            ],
        ]); ?>
            <div class="panel panel-default">
                <div class="panel-heading">
                    <i class="fa fa-envelope"></i><?= Yii::t('app', 'RVML_EDITOR_PAGE_CONDITIONS'); ?>
                    <button type="button" class="pull-right add-item btn btn-success btn-xs" id="add-rule-condition">
                        <i class="glyphicon glyphicon-plus"></i> <?= Yii::t('app', 'RVML_EDITOR_PAGE_ADD_CONDITION'); ?>
                    </button>
                    <div class="clearfix"></div>
                </div>
                <div class="panel-body container-items"><!-- widgetContainer -->
                    <?php foreach ($rule_template_condition_models as $index => $rule_template_condition_model): ?>
                        <div class="item panel panel-default"><!-- widgetBody -->
                            <div class="panel-heading">
                                <span class="panel-title-condition"><?= Yii::t('app', 'RVML_EDITOR_PAGE_CONDITION'); ?>:
                                    <?= ($index + 1) ?></span>
                                <button type="button" class="pull-right remove-item btn btn-danger btn-xs">
                                    <i class="glyphicon glyphicon-minus"></i>
                                </button>
                                <div class="clearfix"></div>
                            </div>
                            <div class="panel-body">
                                <?php if (!$rule_template_condition_model->isNewRecord) {
                                    echo Html::activeHiddenInput($rule_template_condition_model, "[{$index}]id");
                                } ?>
                                <div class="rule-template-operator">
                                    <?= $form->field($rule_template_condition_model, "[{$index}]operator")
                                        ->dropDownList(RuleTemplateCondition::getOperatorsArray(),
                                            ['style' => 'display: none']) ?>
                                </div>
                                <div class="rule-template-condition-name">
                                    <?= $form->field($rule_template_condition_model, "[{$index}]fact_template")
                                        ->dropDownList(FactTemplate::getFactTemplatesArray($model->id))
                                        ->label(Yii::t('app', 'RULE_CONDITION_MODEL_FACT')) ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php DynamicFormWidget::end(); ?>

        <?php DynamicFormWidget::begin([
            'widgetContainer' => 'add_dynamic_rule_action_form_wrapper', // only alphanumeric characters plus "_" [A-Za-z0-9_]
            'widgetBody' => '.container-items', // css class selector
            'widgetItem' => '.item', // css class
            'limit' => 99, // the maximum times, an element can be cloned (default 999)
            'min' => 1, // 0 or 1 (default 1)
            'insertButton' => '.add-item', // css class
            'deleteButton' => '.remove-item', // css class
            'model' => $rule_template_action_models[0],
            'formId' => 'add-rule-form',
            'formFields' => [
                'function',
                'fact_template'
            ],
        ]); ?>
            <div class="panel panel-default">
                <div class="panel-heading">
                    <i class="fa fa-envelope"></i><?= Yii::t('app', 'RVML_EDITOR_PAGE_ACTIONS'); ?>
                    <button type="button" class="pull-right add-item btn btn-success btn-xs" id="add-rule-action">
                        <i class="glyphicon glyphicon-plus"></i> <?= Yii::t('app', 'RVML_EDITOR_PAGE_ADD_ACTION'); ?>
                    </button>
                    <div class="clearfix"></div>
                </div>
                <div class="panel-body container-items"><!-- widgetContainer -->
                    <?php foreach ($rule_template_action_models as $index => $rule_template_action_model): ?>
                        <div class="item panel panel-default"><!-- widgetBody -->
                            <div class="panel-heading">
                                <span class="panel-title-action"><?= Yii::t('app', 'RVML_EDITOR_PAGE_ACTION'); ?>:
                                    <?= ($index + 1) ?></span>
                                <button type="button" class="pull-right remove-item btn btn-danger btn-xs">
                                    <i class="glyphicon glyphicon-minus"></i>
                                </button>
                                <div class="clearfix"></div>
                            </div>
                            <div class="panel-body">
                                <?php if (!$rule_template_action_model->isNewRecord) {
                                    echo Html::activeHiddenInput($rule_template_action_model, "[{$index}]id");
                                } ?>
                                <div class="row">
                                    <div class="col-sm-4">
                                        <div class="rule-template-action-function">
                                            <?= $form->field($rule_template_action_model, "[{$index}]function")
                                                ->dropDownList(RuleTemplateAction::getFunctionsArray()) ?>
                                        </div>
                                    </div>
                                    <div class="col-sm-8">
                                        <div class="rule-template-action-name">
                                            <?= $form->field($rule_template_action_model, "[{$index}]fact_template")
                                                ->dropDownList(FactTemplate::getFactTemplatesArray($model->id))
                                                ->label(Yii::t('app', 'RULE_ACTION_MODEL_FACT'))?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php DynamicFormWidget::end(); ?>

        <?= Button::widget([
            'label' => Yii::t('app', 'BUTTON_ADD'),
            'options' => [
                'id' => 'add-rule-button',
                'class' => 'btn-success',
                'style' => 'margin:5px'
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

<!-- Модальное окно изменения условия правила -->
<?php Modal::begin([
    'id' => 'editRuleConditionModalForm',
    'header' => '<h3>' . Yii::t('app', 'RVML_EDITOR_PAGE_EDIT_RULE_CONDITION') . '</h3>',
]); ?>

    <!-- Скрипт модального окна -->
    <script type="text/javascript">
        // Выполнение скрипта при загрузке страницы
        $(document).ready(function() {
            // Обработка нажатия кнопки сохранения
            $("#edit-rule-condition-button").click(function(e) {
                var form = $("#edit-rule-condition-form");
                // Ajax-запрос
                $.ajax({
                    url: "<?= Yii::$app->request->baseUrl . '/' . Lang::getCurrent()->url .
                        '/rvml-editor/edit-rule-condition' ?>",
                    type: "post",
                    data: form.serialize() + "&fact_id=" + fact_id + "&rule_condition_id=" + rule_condition_id,
                    dataType: "json",
                    success: function(data) {
                        // Если валидация прошла успешно (нет ошибок ввода)
                        if (data['success']) {
                            // Скрывание модального окна
                            $("#editRuleConditionModalForm").modal('hide');
                            // Получение дерева элементов БЗ
                            var tree = $("#rvml-tree-view").treeview(true);
                            // Цикл по всем узлам дерева элементов БЗ
                            $.each(tree.getNodes(), function(id, node) {
                                // Нахождение обновляемого узла факта (условия правила)
                                if (node.id == data['id'] && node.level == 4 && node.ruleConditionId) {
                                    // Формирование массива слотов факта
                                    var fact_slots = [];
                                    $.each(data['fact_slots'], function(id, f_slot) {
                                        var fact_slot = {
                                            id: f_slot['id'],
                                            text: f_slot['name'],
                                            dataType: f_slot['data_type'],
                                            value: f_slot['value'],
                                            description: f_slot['description'],
                                            selectedIcon: "glyphicon glyphicon-arrow-right",
                                            icon: "glyphicon glyphicon-tag"
                                        };
                                        fact_slots.push(fact_slot);
                                    });
                                    // Формирование нового узла факта (условия правила)
                                    var new_node = {
                                        id: data['id'],
                                        factTemplateId: data['fact_template'],
                                        ruleConditionId: data['rule_condition_id'],
                                        operator: data['rule_condition_operator'],
                                        text: data['name'],
                                        certaintyFactor: data['certainty_factor'],
                                        description: data['description'],
                                        icon: "glyphicon glyphicon-stop",
                                        selectedIcon: "glyphicon glyphicon-arrow-right",
                                        nodes: fact_slots
                                    };
                                    // Обновление узла факта (условия правила)
                                    tree.updateNode(node, new_node, {silent: true});
                                    // Выбор обновленного узла факта (условия правила)
                                    tree.selectNode(new_node, {silent: false});
                                }
                            });
                        } else {
                            // Отображение ошибок ввода
                            viewErrors("#edit-rule-condition-form", data);
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
        'id' => 'edit-rule-condition-form',
        'enableAjaxValidation' => true,
        'enableClientValidation' => true,
    ]); ?>

        <?= $form->errorSummary($fact_model); ?>

        <?= $form->field($fact_model, 'fact_template')->hiddenInput() ?>

        <?= $form->field($fact_model, 'fact_template_name')->dropDownList(
            FactTemplate::getFactTemplatesArray($model->id),
            ['disabled' => true]
        ) ?>

        <?= $form->field($fact_model, 'name')->textInput(['maxlength' => true]) ?>

        <?= $form->field($fact_model, 'certainty_factor')->textInput(['maxlength' => true]) ?>

        <?= $form->field($fact_model, 'description')->textarea(['maxlength' => true, 'rows'=>6]) ?>

        <?= Button::widget([
            'label' => Yii::t('app', 'BUTTON_SAVE'),
            'options' => [
                'id' => 'edit-rule-condition-button',
                'class' => 'btn-success',
                'style' => 'margin:5px'
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

<!-- Модальное окно изменения действия правила -->
<?php Modal::begin([
    'id' => 'editRuleActionModalForm',
    'header' => '<h3>' . Yii::t('app', 'RVML_EDITOR_PAGE_EDIT_RULE_ACTION') . '</h3>',
]); ?>

    <!-- Скрипт модального окна -->
    <script type="text/javascript">
        // Выполнение скрипта при загрузке страницы
        $(document).ready(function() {
            // Обработка нажатия кнопки сохранения
            $("#edit-rule-action-button").click(function(e) {
                var form = $("#edit-rule-action-form");
                // Ajax-запрос
                $.ajax({
                    url: "<?= Yii::$app->request->baseUrl . '/' . Lang::getCurrent()->url .
                        '/rvml-editor/edit-rule-action' ?>",
                    type: "post",
                    data: form.serialize() + "&fact_id=" + fact_id + "&rule_action_id=" + rule_action_id,
                    dataType: "json",
                    success: function(data) {
                        // Если валидация прошла успешно (нет ошибок ввода)
                        if (data['success']) {
                            // Скрывание модального окна
                            $("#editRuleActionModalForm").modal('hide');
                            // Получение дерева элементов БЗ
                            var tree = $("#rvml-tree-view").treeview(true);
                            // Цикл по всем узлам дерева элементов БЗ
                            $.each(tree.getNodes(), function(id, node) {
                                // Нахождение обновляемого узла факта (действия правила)
                                if (node.id == data['id'] && node.level == 4 && node.ruleActionId) {
                                    // Формирование массива слотов факта
                                    var fact_slots = [];
                                    $.each(data['fact_slots'], function(id, f_slot) {
                                        var fact_slot = {
                                            id: f_slot['id'],
                                            text: f_slot['name'],
                                            dataType: f_slot['data_type'],
                                            value: f_slot['value'],
                                            description: f_slot['description'],
                                            selectedIcon: "glyphicon glyphicon-arrow-right",
                                            icon: "glyphicon glyphicon-tag"
                                        };
                                        fact_slots.push(fact_slot);
                                    });
                                    // Формирование нового узла факта (действия правила)
                                    var new_node = {
                                        id: data['id'],
                                        factTemplateId: data['fact_template'],
                                        ruleActionId: data['rule_action_id'],
                                        function: data['rule_action_function'],
                                        text: data['name'],
                                        certaintyFactor: data['certainty_factor'],
                                        description: data['description'],
                                        icon: "glyphicon glyphicon-stop",
                                        selectedIcon: "glyphicon glyphicon-arrow-right",
                                        nodes: fact_slots
                                    };
                                    // Обновление узла факта (действия правила)
                                    tree.updateNode(node, new_node, {silent: true});
                                    // Выбор обновленного узла факта (действия правила)
                                    tree.selectNode(new_node, {silent: false});
                                }
                            });
                        } else {
                            // Отображение ошибок ввода
                            viewErrors("#edit-rule-action-form", data);
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
        'id' => 'edit-rule-action-form',
        'enableAjaxValidation' => true,
        'enableClientValidation' => true,
    ]); ?>

        <?= $form->errorSummary($fact_model); ?>

        <?= $form->field($fact_model, 'fact_template')->hiddenInput() ?>

        <?= $form->field($fact_model, 'fact_template_name')->dropDownList(
            FactTemplate::getFactTemplatesArray($model->id),
            ['disabled' => true]
        ) ?>

        <?= $form->field($fact_model, 'name')->textInput(['maxlength' => true]) ?>

        <?= $form->field($fact_model, 'certainty_factor')->textInput(['maxlength' => true]) ?>

        <?= $form->field($fact_model, 'description')->textarea(['maxlength' => true, 'rows'=>6]) ?>

        <?= Button::widget([
            'label' => Yii::t('app', 'BUTTON_SAVE'),
            'options' => [
                'id' => 'edit-rule-action-button',
                'class' => 'btn-success',
                'style' => 'margin:5px'
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

<!-- Модальное окно изменения значения слота условия правила -->
<?php Modal::begin([
    'id' => 'editRuleConditionSlotModalForm',
    'header' => '<h3>' . Yii::t('app', 'RVML_EDITOR_PAGE_EDIT_RULE_CONDITION_SLOT_VALUE') . '</h3>',
]); ?>

    <!-- Скрипт модального окна -->
    <script type="text/javascript">
        // Выполнение скрипта при загрузке страницы
        $(document).ready(function() {
            // Обработка нажатия кнопки сохранения
            $("#edit-rule-condition-slot-button").click(function(e) {
                var form = $("#edit-rule-condition-slot-form");
                // Ajax-запрос
                $.ajax({
                    url: "<?= Yii::$app->request->baseUrl . '/' . Lang::getCurrent()->url .
                        '/rvml-editor/edit-fact-slot' ?>",
                    type: "post",
                    data: form.serialize() + "&fact_slot_id=" + rule_condition_slot_id,
                    dataType: "json",
                    success: function(data) {
                        // Если валидация прошла успешно (нет ошибок ввода)
                        if (data['success']) {
                            // Скрывание модального окна
                            $("#editRuleConditionSlotModalForm").modal("hide");
                            // Получение дерева элементов БЗ
                            var tree = $("#rvml-tree-view").treeview(true);
                            // Цикл по всем узлам дерева элементов БЗ
                            $.each(tree.getNodes(), function(id, node) {
                                // Нахождение обновляемого узла слота условия правила
                                if (node.id == data['id']) {
                                    // Формирование нового узла слота условия правила
                                    var new_node = {
                                        id: data['id'],
                                        text: data['name'],
                                        dataType: data['data_type'],
                                        value: data['value'],
                                        description: data['description'],
                                        icon: "glyphicon glyphicon-tag",
                                        selectedIcon: "glyphicon glyphicon-arrow-right",
                                        state: [{ selected: true }]
                                    };
                                    // Обновление узла слота условия правила
                                    tree.updateNode(node, new_node, { silent: true });
                                    // Выбор обновленного узла слота условия правила
                                    tree.selectNode(new_node, { silent: false });
                                }
                            });
                        } else {
                            // Отображение ошибок ввода
                            viewErrors("#edit-rule-condition-slot-form", data);
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
        'id' => 'edit-rule-condition-slot-form',
        'enableAjaxValidation' => true,
        'enableClientValidation' => true,
    ]); ?>

        <?= $form->errorSummary($fact_slot_model); ?>

        <?= $form->field($fact_slot_model, 'name')->textInput(['maxlength' => true, 'disabled' => true]) ?>

        <?= $form->field($fact_slot_model, 'data_type')->dropDownList(
            DataType::getDataTypesArray($model->id),
            ['disabled' => true]
        ) ?>

        <?= $form->field($fact_slot_model, 'value')->textInput(['maxlength' => true]) ?>

        <?= $form->field($fact_slot_model, 'description')->textarea(['maxlength' => true, 'rows'=>6]) ?>

        <?= Button::widget([
            'label' => Yii::t('app', 'BUTTON_SAVE'),
            'options' => [
                'id' => 'edit-rule-condition-slot-button',
                'class' => 'btn-success',
                'style' => 'margin:5px'
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

<!-- Модальное окно изменения значения слота действия правила -->
<?php Modal::begin([
    'id' => 'editRuleActionSlotModalForm',
    'header' => '<h3>' . Yii::t('app', 'RVML_EDITOR_PAGE_EDIT_RULE_ACTION_SLOT_VALUE') . '</h3>',
]); ?>

    <!-- Скрипт модального окна -->
    <script type="text/javascript">
        // Выполнение скрипта при загрузке страницы
        $(document).ready(function() {
            // Обработка нажатия кнопки сохранения
            $("#edit-rule-action-slot-button").click(function(e) {
                var form = $("#edit-rule-action-slot-form");
                // Ajax-запрос
                $.ajax({
                    url: "<?= Yii::$app->request->baseUrl . '/' . Lang::getCurrent()->url .
                        '/rvml-editor/edit-fact-slot' ?>",
                    type: "post",
                    data: form.serialize() + "&fact_slot_id=" + rule_action_slot_id,
                    dataType: "json",
                    success: function(data) {
                        // Если валидация прошла успешно (нет ошибок ввода)
                        if (data['success']) {
                            // Скрывание модального окна
                            $("#editRuleActionSlotModalForm").modal("hide");
                            // Получение дерева элементов БЗ
                            var tree = $("#rvml-tree-view").treeview(true);
                            // Цикл по всем узлам дерева элементов БЗ
                            $.each(tree.getNodes(), function(id, node) {
                                // Нахождение обновляемого узла слота действия правила
                                if (node.id == data['id']) {
                                    // Формирование нового узла слота действия правила
                                    var new_node = {
                                        id: data['id'],
                                        text: data['name'],
                                        dataType: data['data_type'],
                                        value: data['value'],
                                        description: data['description'],
                                        icon: "glyphicon glyphicon-tag",
                                        selectedIcon: "glyphicon glyphicon-arrow-right",
                                        state: [{ selected: true }]
                                    };
                                    // Обновление узла слота действия правила
                                    tree.updateNode(node, new_node, { silent: true });
                                    // Выбор обновленного узла слота действия правила
                                    tree.selectNode(new_node, { silent: false });
                                }
                            });
                        } else {
                            // Отображение ошибок ввода
                            viewErrors("#edit-rule-action-slot-form", data);
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
        'id' => 'edit-rule-action-slot-form',
        'enableAjaxValidation' => true,
        'enableClientValidation' => true,
    ]); ?>

        <?= $form->errorSummary($fact_slot_model); ?>

        <?= $form->field($fact_slot_model, 'name')->textInput(['maxlength' => true, 'disabled' => true]) ?>

        <?= $form->field($fact_slot_model, 'data_type')->dropDownList(
            DataType::getDataTypesArray($model->id),
            ['disabled' => true]
        ) ?>

        <?= $form->field($fact_slot_model, 'value')->textInput(['maxlength' => true]) ?>

        <?= $form->field($fact_slot_model, 'description')->textarea(['maxlength' => true, 'rows'=>6]) ?>

        <?= Button::widget([
            'label' => Yii::t('app', 'BUTTON_SAVE'),
            'options' => [
                'id' => 'edit-rule-action-slot-button',
                'class' => 'btn-success',
                'style' => 'margin:5px'
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

<!-- Модальное окно изменения правила -->
<?php Modal::begin([
    'id' => 'editRuleModalForm',
    'header' => '<h3>' . Yii::t('app', 'RVML_EDITOR_PAGE_EDIT_RULE') . '</h3>',
]); ?>

    <!-- Скрипт модального окна -->
    <script type="text/javascript">
        // Выполнение скрипта при загрузке страницы
        $(document).on('ready pjax:success', function() {
            // Изменение номера у заголовков элементов шаблонов правил
            var edit_dynamic_condition_form_wrapper = jQuery(".edit_dynamic_new_rule_condition_form_wrapper");
            edit_dynamic_condition_form_wrapper.on("afterInsert", function(e, item) {
                jQuery(".edit_dynamic_new_rule_condition_form_wrapper .panel-title-condition").each(function(index) {
                    jQuery(this).html("<?= Yii::t('app', 'RVML_EDITOR_PAGE_NEW_CONDITION'); ?>: " + (index + 1));
                    $(".rule-operator").hide();
                });
            });
            edit_dynamic_condition_form_wrapper.on("afterDelete", function(e) {
                jQuery(".edit_dynamic_new_rule_condition_form_wrapper .panel-title-condition").each(function(index) {
                    jQuery(this).html("<?= Yii::t('app', 'RVML_EDITOR_PAGE_NEW_CONDITION'); ?>: " + (index + 1))
                });
            });
            var edit_dynamic_action_form_wrapper = jQuery(".edit_dynamic_new_rule_action_form_wrapper");
            edit_dynamic_action_form_wrapper.on("afterInsert", function(e, item) {
                jQuery(".edit_dynamic_new_rule_action_form_wrapper .panel-title-action").each(function(index) {
                    jQuery(this).html("<?= Yii::t('app', 'RVML_EDITOR_PAGE_NEW_ACTION'); ?>: " + (index + 1))
                });
            });
            edit_dynamic_action_form_wrapper.on("afterDelete", function(e) {
                jQuery(".edit_dynamic_new_rule_action_form_wrapper .panel-title-action").each(function(index) {
                    jQuery(this).html("<?= Yii::t('app', 'RVML_EDITOR_PAGE_NEW_ACTION'); ?>: " + (index + 1))
                });
            });
            // Скрытие слоя скрытого поля оператора условия правила
            $(".rule-operator").hide();
            // Скрытие слоя скрытого поля факта (условия и действия правила)
            $(".rule-fact").hide();
            // Обработка нажатия кнопки сохранения
            $("#edit-rule-button").click(function(e) {
                var form = $("#edit-rule-form");
                // Ajax-запрос
                $.ajax({
                    url: "<?= Yii::$app->request->baseUrl . '/' . Lang::getCurrent()->url . '/rvml-editor/edit-rule' ?>",
                    type: "post",
                    data: form.serialize() + "&rule_id=" + rule_id,
                    dataType: "json",
                    success: function(data) {
                        // Если валидация прошла успешно (нет ошибок ввода)
                        if (data['success']) {
                            // Скрывание модального окна
                            $("#editRuleModalForm").modal('hide');
                            // Получение дерева элементов БЗ
                            var tree = $("#rvml-tree-view").treeview(true);
                            // Формирование массива условий правила
                            var rule_conditions = [];
                            // Цикл по всем условиям правила
                            $.each(data['rule_conditions'], function(id, condition) {
                                var flag = false;
                                // Цикл по фактам
                                $.each(data['condition_facts'], function(id, fact) {
                                    if (condition['fact'] == fact['id'] && flag == false) {
                                        flag = true;
                                        // Формирование массива слотов факта
                                        var fact_slots = [];
                                        // Цикл по слотам фактов
                                        $.each(data['condition_fact_slots'], function(id, f_slots) {
                                            if (id == fact['id']) {
                                                // Добавление узлов для слотов факта в массив
                                                $.each(f_slots, function(id, f_slot) {
                                                    var fact_slot = {
                                                        id: f_slot['id'],
                                                        text: f_slot['name'],
                                                        dataType: f_slot['data_type'],
                                                        value: f_slot['value'],
                                                        description: f_slot['description'],
                                                        selectedIcon: "glyphicon glyphicon-arrow-right",
                                                        icon: "glyphicon glyphicon-tag"
                                                    };
                                                    fact_slots.push(fact_slot);
                                                });
                                            }
                                        });
                                        // Формирование узла факта (условия правила)
                                        var rule_condition = {
                                            id: fact['id'],
                                            factTemplateId: fact['fact_template'],
                                            ruleConditionId: condition['id'],
                                            operator: condition['operator'],
                                            text: fact['name'],
                                            certaintyFactor: fact['certainty_factor'],
                                            description: fact['description'],
                                            icon: "glyphicon glyphicon-stop",
                                            selectedIcon: "glyphicon glyphicon-arrow-right",
                                            nodes: fact_slots
                                        };
                                        rule_conditions.push(rule_condition);
                                    }
                                });
                            });
                            // Формирование массива действий для правила
                            var rule_actions = [];
                            // Цикл по всем действиям правила
                            $.each(data['rule_actions'], function(id, action) {
                                var flag = false;
                                // Цикл по фактам
                                $.each(data['action_facts'], function(id, fact) {
                                    if (action['fact'] == fact['id'] && flag == false) {
                                        flag = true;
                                        // Формирование массива слотов факта
                                        var fact_slots = [];
                                        // Цикл по слотам фактов
                                        $.each(data['action_fact_slots'], function(id, f_slots) {
                                            if (id == fact['id']) {
                                                // Добавление узлов для слотов факта в массив
                                                $.each(f_slots, function(id, f_slot) {
                                                    var fact_slot = {
                                                        id: f_slot['id'],
                                                        text: f_slot['name'],
                                                        dataType: f_slot['data_type'],
                                                        value: f_slot['value'],
                                                        description: f_slot['description'],
                                                        selectedIcon: "glyphicon glyphicon-arrow-right",
                                                        icon: "glyphicon glyphicon-tag"
                                                    };
                                                    fact_slots.push(fact_slot);
                                                });
                                            }
                                        });
                                        // Формирование узла факта (действия правила)
                                        var rule_action = {
                                            id: fact['id'],
                                            factTemplateId: fact['fact_template'],
                                            ruleActionId: action['id'],
                                            function: action['function'],
                                            text: fact['name'],
                                            certaintyFactor: fact['certainty_factor'],
                                            description: fact['description'],
                                            icon: "glyphicon glyphicon-stop",
                                            selectedIcon: "glyphicon glyphicon-arrow-right",
                                            nodes: fact_slots
                                        };
                                        rule_actions.push(rule_action);
                                    }
                                });
                            });

                            // Поиск родительского узла для правила
                            $.each(tree.getNodes(), function(id, node) {
                                // Нахождение обновляемого узла правила
                                if (node.id == data['id'] && node.level == 2) {
                                    // Создание нового узла правила
                                    var new_node = {
                                        id: data['id'],
                                        ruleTemplateId: data['rule_template'],
                                        certaintyFactor: data['certainty_factor'],
                                        salience: data['salience'],
                                        text: data['name'],
                                        description: data['description'],
                                        icon: "glyphicon glyphicon-record",
                                        selectedIcon: "glyphicon glyphicon-arrow-right",
                                        state: [{ expanded: true, selected: true }],
                                        nodes: [
                                            {
                                                class: "rule-elements",
                                                text: "<?= Yii::t('app', 'RVML_EDITOR_PAGE_CONDITIONS') ?>",
                                                color: "#428bca",
                                                selectable: false,
                                                icon: "glyphicon glyphicon-record",
                                                tags: [ rule_conditions.length ],
                                                state: [{ expanded: true }],
                                                nodes: rule_conditions
                                            },
                                            {
                                                class: "rule-elements",
                                                text: "<?= Yii::t('app', 'RVML_EDITOR_PAGE_ACTIONS') ?>",
                                                color: "#428bca",
                                                selectable: false,
                                                icon: "glyphicon glyphicon-record",
                                                tags: [ rule_actions.length ],
                                                state: [{ expanded: true }],
                                                nodes: rule_actions
                                            }
                                        ]
                                    };
                                    // Обновление узла правила
                                    tree.updateNode(node, new_node, { silent: true });
                                    // Выбор добавленного узла правила
                                    tree.selectNode(new_node, { silent: false });
                                }
                            });
                        } else {
                            // Отображение ошибок ввода
                            viewErrors("#edit-rule-form", data);
                        }
                    },
                    error: function() {
                        alert('Error!');
                    }
                });
            });
        });
    </script>

    <?php Pjax::begin(); ?>

    <?php $form = ActiveForm::begin([
        'id' => 'edit-rule-form',
        'enableAjaxValidation' => true,
        'enableClientValidation' => true
    ]); ?>

        <?= $form->errorSummary($rule_model); ?>

        <?= $form->field($rule_model, 'rule_template')->dropDownList(
            RuleTemplate::getRuleTemplatesArray($model->id),
            ['disabled' => true]
        ) ?>

        <?= $form->field($rule_model, 'name')->textInput(['maxlength' => true]) ?>

        <?= $form->field($rule_model, 'certainty_factor')->textInput(['maxlength' => true]) ?>

        <?= $form->field($rule_model, 'salience')->textInput(['maxlength' => true]) ?>

        <?= $form->field($rule_model, 'description')->textarea(['maxlength' => true, 'rows'=>6]) ?>

        <?php DynamicFormWidget::begin([
            'widgetContainer' => 'edit_dynamic_rule_condition_form_wrapper', // only alphanumeric characters plus "_" [A-Za-z0-9_]
            'widgetBody' => '.container-items', // css class selector
            'widgetItem' => '.item', // css class
            'limit' => 99, // the maximum times, an element can be cloned (default 999)
            'min' => 1, // 0 or 1 (default 1)
            'insertButton' => '.add-item', // css class
            'deleteButton' => '.remove-item', // css class
            'model' => $rule_condition_models[0],
            'formId' => 'edit-rule-form',
            'formFields' => [
                'operator',
                'fact'
            ],
        ]); ?>
            <div class="panel panel-default">
                <div class="panel-heading">
                    <i class="fa fa-envelope"></i><?= Yii::t('app', 'RVML_EDITOR_PAGE_CONDITIONS'); ?>
                    <div class="clearfix"></div>
                </div>
                <div class="panel-body container-items"><!-- widgetContainer -->
                    <?php foreach ($rule_condition_models as $index => $rule_condition_model): ?>
                        <div class="item panel panel-default"><!-- widgetBody -->
                            <div class="panel-heading">
                                <span class="panel-title-condition"><?= Yii::t('app', 'RVML_EDITOR_PAGE_CONDITION'); ?>:
                                    <?= ($index + 1) ?></span>
                                <button type="button" class="pull-right remove-item btn btn-danger btn-xs">
                                    <i class="glyphicon glyphicon-minus"></i>
                                </button>
                                <div class="clearfix"></div>
                            </div>
                            <div class="panel-body">
                                <?php if (!$rule_condition_model->isNewRecord) {
                                    echo Html::activeHiddenInput($rule_condition_model, "[{$index}]id");
                                } ?>
                                <div class="rule-operator">
                                    <?= $form->field($rule_condition_model, "[{$index}]operator")
                                        ->dropDownList(RuleCondition::getOperatorsArray(),
                                            ['style' => 'display: none']) ?>
                                </div>
                                <div class="rule-fact">
                                    <?= $form->field($rule_condition_model, "[{$index}]fact")
                                        ->textInput(['maxlength' => true, 'style' => 'display: none']) ?>
                                </div>
                                <?= $form->field($rule_condition_model, "[{$index}]fact_name")
                                    ->textInput(['maxlength' => true,
                                        'value' => Fact::getFactName($rule_condition_model->fact)]) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php DynamicFormWidget::end(); ?>

        <?php DynamicFormWidget::begin([
            'widgetContainer' => 'edit_dynamic_new_rule_condition_form_wrapper', // only alphanumeric characters plus "_" [A-Za-z0-9_]
            'widgetBody' => '.container-items', // css class selector
            'widgetItem' => '.item', // css class
            'limit' => 99, // the maximum times, an element can be cloned (default 999)
            'min' => 0, // 0 or 1 (default 1)
            'insertButton' => '.add-item', // css class
            'deleteButton' => '.remove-item', // css class
            'model' => $rule_template_condition_models[0],
            'formId' => 'edit-rule-form',
            'formFields' => [
                'fact_template'
            ],
        ]); ?>
            <div class="panel panel-default">
                <div class="panel-heading">
                    <i class="fa fa-envelope"></i><?= Yii::t('app', 'RVML_EDITOR_PAGE_NEW_CONDITIONS'); ?>
                    <button type="button" class="pull-right add-item btn btn-success btn-xs">
                        <i class="glyphicon glyphicon-plus"></i> <?= Yii::t('app', 'RVML_EDITOR_PAGE_ADD_CONDITION'); ?>
                    </button>
                    <div class="clearfix"></div>
                </div>
                <div class="panel-body container-items"><!-- widgetContainer -->
                    <?php foreach ($rule_template_condition_models as $index => $rule_template_condition_model): ?>
                        <div class="item panel panel-default"><!-- widgetBody -->
                            <div class="panel-heading">
                                <span class="panel-title-condition">
                                    <?= Yii::t('app', 'RVML_EDITOR_PAGE_NEW_CONDITION'); ?>: <?= ($index + 1) ?>
                                </span>
                                <button type="button" class="pull-right remove-item btn btn-danger btn-xs">
                                    <i class="glyphicon glyphicon-minus"></i>
                                </button>
                                <div class="clearfix"></div>
                            </div>
                            <div class="panel-body">
                                <?= $form->field($rule_template_condition_model, "[{$index}]fact_template")
                                    ->dropDownList(FactTemplate::getFactTemplatesArray($model->id))
                                    ->label(Yii::t('app', 'RULE_CONDITION_MODEL_FACT')) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php DynamicFormWidget::end(); ?>

        <?php DynamicFormWidget::begin([
            'widgetContainer' => 'edit_dynamic_rule_action_form_wrapper', // only alphanumeric characters plus "_" [A-Za-z0-9_]
            'widgetBody' => '.container-items', // css class selector
            'widgetItem' => '.item', // css class
            'limit' => 99, // the maximum times, an element can be cloned (default 999)
            'min' => 1, // 0 or 1 (default 1)
            'insertButton' => '.add-item', // css class
            'deleteButton' => '.remove-item', // css class
            'model' => $rule_action_models[0],
            'formId' => 'edit-rule-form',
            'formFields' => [
                'function',
                'fact'
            ],
        ]); ?>
            <div class="panel panel-default">
                <div class="panel-heading">
                    <i class="fa fa-envelope"></i><?= Yii::t('app', 'RVML_EDITOR_PAGE_ACTIONS'); ?>
                    <div class="clearfix"></div>
                </div>
                <div class="panel-body container-items"><!-- widgetContainer -->
                    <?php foreach ($rule_action_models as $index => $rule_action_model): ?>
                        <div class="item panel panel-default"><!-- widgetBody -->
                            <div class="panel-heading">
                                <span class="panel-title-action"><?= Yii::t('app', 'RVML_EDITOR_PAGE_ACTION'); ?>:
                                    <?= ($index + 1) ?></span>
                                <button type="button" class="pull-right remove-item btn btn-danger btn-xs">
                                    <i class="glyphicon glyphicon-minus"></i>
                                </button>
                                <div class="clearfix"></div>
                            </div>
                            <div class="panel-body">
                                <?php if (!$rule_action_model->isNewRecord) {
                                    echo Html::activeHiddenInput($rule_action_model, "[{$index}]id");
                                } ?>
                                <div class="row">
                                    <div class="col-sm-4">
                                        <?= $form->field($rule_action_model, "[{$index}]function")
                                            ->dropDownList(RuleAction::getFunctionsArray()) ?>
                                    </div>
                                    <div class="col-sm-8">
                                        <div class="rule-fact">
                                            <?= $form->field($rule_action_model, "[{$index}]fact")
                                                ->textInput(['maxlength' => true, 'style' => 'display: none']) ?>
                                        </div>
                                        <?= $form->field($rule_action_model, "[{$index}]fact_name")
                                            ->textInput(['maxlength' => true,
                                                'value' => Fact::getFactName($rule_action_model->fact)]) ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php DynamicFormWidget::end(); ?>

        <?php DynamicFormWidget::begin([
            'widgetContainer' => 'edit_dynamic_new_rule_action_form_wrapper', // only alphanumeric characters plus "_" [A-Za-z0-9_]
            'widgetBody' => '.container-items', // css class selector
            'widgetItem' => '.item', // css class
            'limit' => 99, // the maximum times, an element can be cloned (default 999)
            'min' => 0, // 0 or 1 (default 1)
            'insertButton' => '.add-item', // css class
            'deleteButton' => '.remove-item', // css class
            'model' => $rule_template_action_models[0],
            'formId' => 'edit-rule-form',
            'formFields' => [
                'function',
                'fact_template'
            ],
        ]); ?>
            <div class="panel panel-default">
                <div class="panel-heading">
                    <i class="fa fa-envelope"></i><?= Yii::t('app', 'RVML_EDITOR_PAGE_NEW_ACTIONS'); ?>
                    <button type="button" class="pull-right add-item btn btn-success btn-xs" id="add-rule-action">
                        <i class="glyphicon glyphicon-plus"></i> <?= Yii::t('app', 'RVML_EDITOR_PAGE_ADD_ACTION'); ?>
                    </button>
                    <div class="clearfix"></div>
                </div>
                <div class="panel-body container-items"><!-- widgetContainer -->
                    <?php foreach ($rule_template_action_models as $index => $rule_template_action_model): ?>
                        <div class="item panel panel-default"><!-- widgetBody -->
                            <div class="panel-heading">
                                <span class="panel-title-action"><?= Yii::t('app', 'RVML_EDITOR_PAGE_NEW_ACTION'); ?>:
                                    <?= ($index + 1) ?></span>
                                <button type="button" class="pull-right remove-item btn btn-danger btn-xs">
                                    <i class="glyphicon glyphicon-minus"></i>
                                </button>
                                <div class="clearfix"></div>
                            </div>
                            <div class="panel-body">
                                <div class="row">
                                    <div class="col-sm-4">
                                        <?= $form->field($rule_template_action_model, "[{$index}]function")
                                            ->dropDownList(RuleTemplateAction::getFunctionsArray()) ?>
                                    </div>
                                    <div class="col-sm-8">
                                        <?= $form->field($rule_template_action_model, "[{$index}]fact_template")
                                            ->dropDownList(FactTemplate::getFactTemplatesArray($model->id))
                                            ->label(Yii::t('app', 'RULE_ACTION_MODEL_FACT'))?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php DynamicFormWidget::end(); ?>

        <?= Button::widget([
            'label' => Yii::t('app', 'BUTTON_SAVE'),
            'options' => [
                'id' => 'edit-rule-button',
                'class' => 'btn-success',
                'style' => 'margin:5'
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

    <?= Html::beginForm(['/rvml-editor/' . $model->id], 'post',
        ['id' => 'pjax-rule-form', 'data-pjax' => '', 'style' => 'display:none']); ?>
    <?= Html::hiddenInput('rule-id', Yii::$app->request->post('rule-id'), ['id' => 'pjax-rule-input']) ?>
    <?= Html::submitButton('Вычислить', ['id' => 'pjax-rule-button', 'data-pjax' => '']) ?>
    <?= Html::endForm() ?>

    <?php Pjax::end(); ?>

<?php Modal::end(); ?>

<!-- Модальное окно удаления правила -->
<?php Modal::begin([
    'id' => 'deleteRuleModalForm',
    'header' => '<h3>' . Yii::t('app', 'RVML_EDITOR_PAGE_DELETE_RULE') . '</h3>',
]); ?>

    <!-- Скрипт модального окна -->
    <script type="text/javascript">
        $(document).ready(function() {
            // Обработка нажатия кнопки удаления
            $("#delete-rule-button").click(function(e) {
                e.preventDefault();
                // Ajax-запрос
                $.ajax({
                    url: "<?= Yii::$app->request->baseUrl . '/' . Lang::getCurrent()->url .
                        '/rvml-editor/delete-rule' ?>",
                    type: "post",
                    data: "YII_CSRF_TOKEN=<?= Yii::$app->request->csrfToken ?>&rule_id=" + rule_id,
                    dataType: "json",
                    success: function(data) {
                        // Скрывание модального окна
                        $("#deleteRuleModalForm").modal('hide');
                        // Кнопки на панели инструментов дерева элементов БЗ
                        var add_element_button = document.getElementById("add-element-button");
                        var edit_element_button = document.getElementById("edit-element-button");
                        var delete_element_button = document.getElementById("delete-element-button");
                        // Деактивация кнопок на панели дерева элементов БЗ
                        add_element_button.classList.add("disabled");
                        add_element_button.setAttribute("data-target", "");
                        add_element_button.setAttribute("data-original-title", "");
                        edit_element_button.classList.add("disabled");
                        edit_element_button.setAttribute("data-target", "");
                        edit_element_button.setAttribute("data-original-title", "");
                        delete_element_button.classList.add("disabled");
                        delete_element_button.setAttribute("data-target", "");
                        delete_element_button.setAttribute("data-original-title", "");
                        // Получение дерева элементов БЗ
                        var tree = $("#rvml-tree-view").treeview(true);
                        // Поиск удаляемого узла правила
                        $.each(tree.getNodes(), function(id, node) {
                            if (node.id == data['rule_id'] && node.level == 2) {
                                // Обновление общего кол-ва правил (уменьшение tag на 1)
                                tree.getParents(node)[0].tags[0] -= 1;
                                // Удаление узла правила
                                tree.removeNode(node, {silent: true});
                            }
                        });
                        // Рабочая область (слой) редактора RVML
                        var production_model = document.getElementById('production-model');
                        // Очистка рабочей области (удаление всех элементов)
                        while(production_model.firstChild)
                            production_model.removeChild(production_model.firstChild);
                        // Формирование текста с сообщением
                        document.getElementById("message-text").lastChild.nodeValue =
                            "<?= Yii::t('app', 'RVML_EDITOR_PAGE_MESSAGE_DELETE_RULE') ?>";
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
            <?php echo Yii::t('app', 'RVML_EDITOR_PAGE_MODAL_FORM_DELETE_RULE_TEXT'); ?>
        </p>
    </div>

    <?php $form = ActiveForm::begin([
        'id' => 'delete-rule-form',
    ]); ?>

        <?= Button::widget([
            'label' => Yii::t('app', 'BUTTON_DELETE'),
            'options' => [
                'id' => 'delete-rule-button',
                'class' => 'btn-success',
                'style' => 'margin:5px'
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
    'header' => '<h3>' . Yii::t('app', 'RVML_EDITOR_PAGE_RVML_EDITOR') . '</h3>',
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