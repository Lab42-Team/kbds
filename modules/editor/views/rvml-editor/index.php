<?php

/* @var $this yii\web\View */
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
/* @var $data app\modules\editor\controllers\RvmlEditorController */

use yii\web\JsExpression;
use yii\bootstrap\Button;
use execut\widget\TreeView;
use app\modules\knowledge_base\models\RuleAction;
use app\modules\knowledge_base\models\RuleCondition;
use app\modules\knowledge_base\models\RuleTemplateAction;
use app\modules\knowledge_base\models\RuleTemplateCondition;

$this->title = Yii::t('app', 'RVML_EDITOR_PAGE_RVML_EDITOR');

$this->params['menu'] = [
    [
        'label' => '<span class="glyphicon glyphicon-unchecked"></span> ' .
            Yii::t('app', 'RVML_EDITOR_PAGE_FACT_TEMPLATE'),
        'url' => '#',
        'options' => ['data-toggle'=>'modal', 'data-target'=>'#addFactTemplateModalForm']
    ],
    [
        'label' => '<span class="glyphicon glyphicon-stop"></span> ' .
            Yii::t('app', 'RVML_EDITOR_PAGE_INITIAL_FACT'),
        'url' => '#',
        'options' => ['data-toggle'=>'modal', 'data-target'=>'#addInitialFactModalForm']
    ],
    [
        'label' => '<span class="glyphicon glyphicon-registration-mark"></span> ' .
            Yii::t('app', 'RVML_EDITOR_PAGE_RULE_TEMPLATE'),
        'url' => '#',
        'options' => ['data-toggle'=>'modal', 'data-target'=>'#addRuleTemplateModalForm']
    ],
    [
        'label' => '<span class="glyphicon glyphicon-record"></span> ' .
            Yii::t('app', 'RVML_EDITOR_PAGE_RULE'),
        'url' => '#',
        'options' => ['data-toggle'=>'modal', 'data-target'=>'#addRuleModalForm']
    ]
];

$this->params['export-link'] = [
    'label' => '<span class="glyphicon glyphicon-circle-arrow-down"></span> ' .
        Yii::t('app', 'RVML_EDITOR_PAGE_GENERATE_CLIPS_CODE'),
    'url' => ['/rvml-editor/generate-clips-code/' . $model->id]
];
?>

<?= $this->render('_modal_form_rvml_editor', [
    'model' => $model,
    'fact_template_model' => $fact_template_model,
    'fact_template_slot_model' => $fact_template_slot_model,
    'fact_model' => $fact_model,
    'fact_slot_model' => $fact_slot_model,
    'rule_template_model' => $rule_template_model,
    'rule_template_condition_models' => (empty($rule_template_condition_models)) ?
        [new RuleTemplateCondition] : $rule_template_condition_models,
    'rule_template_action_models' => (empty($rule_template_action_models)) ?
        [new RuleTemplateAction] : $rule_template_action_models,
    'rule_model' => $rule_model,
    'rule_condition_models' => (empty($rule_condition_models)) ? [new RuleCondition] : $rule_condition_models,
    'rule_action_models' => (empty($rule_action_models)) ? [new RuleAction] : $rule_action_models
]) ?>

<!-- Подключение стилей для редактора продукционной модели -->
<?php $this->registerCssFile('/css/rvml-editor-styles.css', ['position' => yii\web\View::POS_HEAD]) ?>
<!-- Подключение библиотеки jsPlumb 2.1.2 -->
<?php $this->registerJsFile('/js/jsPlumb-2.1.2.js', ['position' => yii\web\View::POS_HEAD]) ?>
<!-- Подключение скрипта для модальных форм -->
<?php $this->registerJsFile('/js/modal-form.js', ['position' => yii\web\View::POS_HEAD]) ?>
<!-- Подключение скрипта для дерева элементов БЗ -->
<?php $this->registerJsFile('/js/rvml-tree-view.js', ['position' => yii\web\View::POS_HEAD]) ?>

<script type="text/javascript">
    // Графическая сцена jsPlumb
    var instance;
    // id текущего выбранного шаблона факта
    var fact_template_id;
    // id текущего выбранного факта
    var fact_id;
    // id текущего выбранного слота шаблона факта
    var fact_template_slot_id;
    // id текущего выбранного слота начального факта
    var fact_slot_id;
    // id текущего выбранного шаблона правила
    var rule_template_id;
    // id текущего выбранного правила
    var rule_id;
    // id текущего условия правила
    var rule_condition_id;
    // id текущего действия правила
    var rule_action_id;
    // id текущего выбранного слота условия правила
    var rule_condition_slot_id;
    // id текущего выбранного слота действия правила
    var rule_action_slot_id;

    // Отрисовка элементов базы знаний (продукционной модели)
    jsPlumb.ready(function () {
        // Слой на котором отрисовывается база знаний (продукционная модель)
        var production_model = $("#production-model");
        // Наименование связи
        var relation_name = '';

        // Настроика некоторых значении для jsPlumb по умолчанию
        var instance = jsPlumb.getInstance({
            Endpoint: ["Dot", { radius: 1, cssClass: "end-point" }],
            EndpointStyles: [
                { fillStyle: "#800000" },
                { fillStyle: "#800000" }
            ],
            HoverPaintStyle : { strokeStyle: "#428bca", lineWidth: 2 },
            ConnectionsDetachable: false,
            PaintStyle : {
                strokeStyle: "#800000",
                lineWidth: 2,
                outlineColor: "transparent",
                outlineWidth: 4
            },
            Container: "production-model"
        });

        var windows = jsPlumb.getSelector("#production-model .fact-template");

        // Инициализация перетаскивания элементов
        instance.draggable(windows, { containment: "#production-model" });

        // Обработка нажатия левой кнопки мыши на связи между элементами
        instance.bind("click", function(connection) {
            //
            alert('click');
        });

        // Обработка нажатия правой кнопки мыши на связи между элементами
        instance.bind("contextmenu", function(connection, originalEvent) {
            originalEvent.preventDefault();
            //
            alert('contextmenu');
        });

        // bind a connection listener. note that the parameter passed to this function contains more than
        // just the new connection - see the documentation for a full list of what is included in 'info'.
        // this listener sets the connection's internal
        // id as the label overlay's text.
        instance.bind("connection", function(info) {
            // Проверка названия связи
            if (relation_name != '')
                info.connection.getOverlay("label").setLabel(relation_name);
        });

        // Обработка события до установления связи между элементами
        instance.bind("beforeDrop", function(params) {
            return false;
        });

        // suspend drawing and initialise.
        instance.batch(function () {
            instance.makeSource(windows, {
                filter: ".production-join",
                anchor: "Continuous",
                connector: [ "StateMachine", { curviness: 20 } ]
            });

            // initialise all '.concept' elements as connection targets.
            instance.makeTarget(windows, {
                dropOptions: { hoverClass: "dragHover" },
                anchor: "Continuous",
                allowLoopback: false // Нельзя создать кольцевую связь
            });

            // Затираем наименование связи после добавления
            relation_name = '';
        });

        // Сохраняем графическую сцену jsPlumb в глобальную переменную
        window.instance = instance;
    });

    // Выполнение скрипта при загрузке страницы
    $(document).ready(function() {
        // Инициализация подсказок BS3
        $(function() {
            $("[data-toggle-title='tooltip']").tooltip();
        });

        // Обработка закрытия модального окна добавления нового шаблона факта
        $("#addFactTemplateModalForm").on("hidden.bs.modal", function() {
            // Скрытие списка ошибок ввода в модальном окне
            $("#add-fact-template-form .error-summary").hide();
            $("#add-fact-template-form .form-group").each(function() {
                $(this).removeClass("has-error");
                $(this).removeClass("has-success");
            });
            $("#add-fact-template-form .help-block").each(function() {
                $(this).text("");
            });
        });
        // Обработка закрытия модального окна изменения шаблона факта
        $("#editFactTemplateModalForm").on("hidden.bs.modal", function() {
            // Скрытие списка ошибок ввода в модальном окне
            $("#edit-fact-template-form .error-summary").hide();
            $("#edit-fact-template-form .form-group").each(function() {
                $(this).removeClass("has-error");
                $(this).removeClass("has-success");
            });
            $("#edit-fact-template-form .help-block").each(function() {
                $(this).text("");
            });
        });
        // Обработка закрытия модального окна добавления нового слота шаблону факта
        $("#addFactTemplateSlotModalForm").on("hidden.bs.modal", function() {
            // Скрытие списка ошибок ввода в модальном окне
            $("#add-fact-template-slot-form .error-summary").hide();
            $("#add-fact-template-slot-form .form-group").each(function() {
                $(this).removeClass("has-error");
                $(this).removeClass("has-success");
            });
            $("#add-fact-template-slot-form .help-block").each(function() {
                $(this).text("");
            });
        });
        // Обработка закрытия модального окна добавления нового начального факта
        $("#addInitialFactModalForm").on("hidden.bs.modal", function() {
            // Скрытие списка ошибок ввода в модальном окне
            $("#add-initial-fact-form .error-summary").hide();
            $("#add-initial-fact-form .form-group").each(function() {
                $(this).removeClass("has-error");
                $(this).removeClass("has-success");
            });
            $("#add-initial-fact-form .help-block").each(function() {
                $(this).text("");
            });
        });
        // Обработка закрытия модального окна изменения начального факта
        $("#editInitialFactModalForm").on("hidden.bs.modal", function() {
            // Скрытие списка ошибок ввода в модальном окне
            $("#edit-initial-fact-form .error-summary").hide();
            $("#edit-initial-fact-form .form-group").each(function() {
                $(this).removeClass("has-error");
                $(this).removeClass("has-success");
            });
            $("#edit-initial-fact-form .help-block").each(function() {
                $(this).text("");
            });
            // Отображение слоя списка шаблонов фактов
            $(".field-fact-fact_template").show();
        });
        // Обработка закрытия модального окна добавления нового шаблона правила
        $("#addRuleTemplateModalForm").on("hidden.bs.modal", function() {
            // Скрытие списка ошибок ввода в модальном окне
            $("#add-rule-template-form .error-summary").hide();
            $("#add-rule-template-form .form-group").each(function() {
                $(this).removeClass("has-error");
                $(this).removeClass("has-success");
            });
            $("#add-rule-template-form .help-block").each(function() {
                $(this).text("");
            });
        });
        // Обработка закрытия модального окна добавления нового правила
        $("#addRuleModalForm").on("hidden.bs.modal", function() {
            // Скрытие списка ошибок ввода в модальном окне
            $("#add-rule-form .error-summary").hide();
            $("#add-rule-form .form-group").each(function() {
                $(this).removeClass("has-error");
                $(this).removeClass("has-success");
            });
            $("#add-rule-form .help-block").each(function() {
                $(this).text("");
            });
        });

        // Обработка нажатия кнопки изменения элемента БЗ на панели дерева
        $("#edit-element-button").click(function(e) {
            var alert;
            var found_related_rule_templates = false;
            var found_related_initial_facts = false;
            var found_related_rules = false;
            // Получение дерева элементов БЗ
            var tree = $("#rvml-tree-view").treeview(true);
            // Если известен id выбранного шаблона факта
            if (fact_template_id != 0) {
                // Формирование предупредительного сообщения
                alert = $('.edit-fact-template-alert');
                alert.text('');
                // Если выбран шаблон факта
                if (fact_template_id && !fact_template_slot_id)
                    alert.append('<b><?= Yii::t('app', 'WARNING') ?></b> ' +
                        '<?= Yii::t('app', 'RVML_EDITOR_PAGE_FACT_TEMPLATE_MESSAGE_RELATED_CHANGES') ?></br>');
                // Если выбран слот шаблона факта
                if (fact_template_id && fact_template_slot_id)
                    alert.append('<b><?= Yii::t('app', 'WARNING') ?></b> ' +
                        '<?= Yii::t('app', 'RVML_EDITOR_PAGE_FACT_TEMPLATE_SLOT_MESSAGE_RELATED_CHANGES') ?></br>');
                // Цикл по всем узлам дерева элементов БЗ
                $.each(tree.getNodes(), function(index, element) {
                    // Нахождение шаблонов правил свзанных с изменяемым шаблоном факта
                    if (fact_template_id == element.id && (element.operator || element.function)) {
                        if (!found_related_rule_templates) {
                            alert.append('<?= Yii::t('app', 'RVML_EDITOR_PAGE_RULE_TEMPLATES'); ?>: </br>');
                            alert.append('<ul class="related-elements">');
                        }
                        // Нахождение шаблона правила
                        $.each(tree.getNodes(), function(i, local_parent) {
                            if (element.parentId == local_parent.nodeId)
                                $.each(tree.getNodes(), function(j, global_parent) {
                                    if (local_parent.parentId == global_parent.nodeId)
                                        alert.append('<li><i>' + global_parent.text + '</i></li>');
                                });
                        });
                        found_related_rule_templates = true;
                    }
                });
                if (found_related_rule_templates)
                    alert.append('</ul>');
                // Цикл по всем узлам дерева элементов БЗ
                $.each(tree.getNodes(), function(index, element) {
                    // Нахождение начальных фактов свзанных с изменяемым шаблоном факта
                    if (fact_template_id == element.factTemplateId && !element.operator && !element.function) {
                        if (!found_related_initial_facts) {
                            alert.append('<?= Yii::t('app', 'RVML_EDITOR_PAGE_INITIAL_FACTS'); ?>: </br>');
                            alert.append('<ul class="related-elements">');
                        }
                        alert.append('<li><i>' + element.text + '</i></li>');
                        found_related_initial_facts = true;
                    }
                });
                if (found_related_initial_facts)
                    alert.append('</ul>');
                // Цикл по всем узлам дерева элементов БЗ
                $.each(tree.getNodes(), function(index, element) {
                    // Нахождение правил свзанных с изменяемым шаблоном факта
                    if (fact_template_id == element.factTemplateId && (element.operator || element.function)) {
                        if (!found_related_rules) {
                            alert.append('<?= Yii::t('app', 'RVML_EDITOR_PAGE_RULES'); ?>: </br>');
                            alert.append('<ul class="related-elements">');
                        }
                        // Нахождение правил
                        $.each(tree.getNodes(), function(i, local_parent) {
                            if (element.parentId == local_parent.nodeId)
                                $.each(tree.getNodes(), function(j, global_parent) {
                                    if (local_parent.parentId == global_parent.nodeId)
                                        alert.append('<li><i>' + global_parent.text + '</i></li>');
                                });
                        });
                        found_related_rules = true;
                    }
                });
                if (found_related_rules)
                    alert.append('</ul>');
                // Если найдены элементы связанные с данным шаблоном факта, то вывод сообщения
                if (found_related_initial_facts || found_related_rule_templates || found_related_rules)
                    alert.show();
                else
                    alert.hide();
                // Цикл по всем узлам дерева элементов БЗ
                $.each(tree.getNodes(), function (id, node) {
                    // Если данный узел выбран и его id совпадает с id шаблона факта
                    if (node.state.selected && node.id == fact_template_id) {
                        // Очистка полей ввода на форме
                        document.forms["edit-fact-template-form"].reset();
                        // Подстановка названия шаблона факта в поле ввода
                        document.forms["edit-fact-template-form"].elements["FactTemplate[name]"].value = node.text;
                        // Подстановка описания шаблона факта в поле ввода, если оно существует
                        if (node.description)
                            document.forms["edit-fact-template-form"].elements["FactTemplate[description]"].
                                value = node.description;
                    }
                    // Если данный узел выбран и его id совпадает с id слота шаблона факта
                    if (node.state.selected && node.id == fact_template_slot_id) {
                        // Очистка полей ввода на форме
                        document.forms["edit-fact-template-slot-form"].reset();
                        // Подстановка названия слота шаблона факта в поле ввода
                        document.forms["edit-fact-template-slot-form"].elements["FactTemplateSlot[name]"].
                            value = node.text;
                        // Подстановка типа данных для слота шаблона факта в поле ввода
                        document.forms["edit-fact-template-slot-form"].elements["FactTemplateSlot[data_type]"].value =
                            $("#facttemplateslot-data_type option:contains('" + node.dataType + "')").attr("value");
                        // Подстановка значения по умолчанию для слота шаблона факта в поле ввода
                        document.forms["edit-fact-template-slot-form"].elements["FactTemplateSlot[default_value]"].
                            value = node.defaultValue;
                        // Подстановка описания слота шаблона факта в поле ввода, если оно существует
                        if (node.description)
                            document.forms["edit-fact-template-slot-form"].elements["FactTemplateSlot[description]"].
                                value = node.description;
                    }
                });
            }
            // Если известен id выбранного начального факта
            if (fact_id != 0 && rule_condition_id == 0 && rule_action_id == 0) {
                // Цикл по всем узлам дерева элементов БЗ
                $.each(tree.getNodes(), function (id, node) {
                    // Если данный узел выбран и его id совпадает с id факта
                    if (node.state.selected && node.id == fact_id) {
                        // Скрытие слоя скрытого поля шаблона факта
                        $(".field-fact-fact_template").hide();
                        // Очистка полей ввода на форме
                        document.forms["edit-initial-fact-form"].reset();
                        // Выбор шаблона факта в списке
                        document.forms["edit-initial-fact-form"].elements["Fact[fact_template_name]"].value =
                            node.factTemplateId;
                        // Выбор шаблона факта в списке
                        document.forms["edit-initial-fact-form"].elements["Fact[fact_template]"].value =
                            node.factTemplateId;
                        // Подстановка названия факта в поле ввода
                        document.forms["edit-initial-fact-form"].elements["Fact[name]"].value = node.text;
                        // Подстановка коэффициента уверенности факта в поле ввода
                        document.forms["edit-initial-fact-form"].elements["Fact[certainty_factor]"].value =
                            node.certaintyFactor;
                        // Подстановка описания факта в поле ввода, если оно существует
                        if (node.description)
                            document.forms["edit-initial-fact-form"].elements["Fact[description]"].value =
                                node.description;
                    }
                });
            }
            // Если известен id выбранного факта (условия правила)
            if (fact_id != 0 && rule_condition_id != 0 && rule_action_id == 0) {
                // Цикл по всем узлам дерева элементов БЗ
                $.each(tree.getNodes(), function (id, node) {
                    // Если данный узел выбран и его id совпадает с id факта
                    if (node.state.selected && node.id == fact_id) {
                        // Скрытие слоя скрытого поля шаблона факта
                        $(".field-fact-fact_template").hide();
                        // Очистка полей ввода на форме
                        document.forms["edit-rule-condition-form"].reset();
                        // Выбор шаблона факта в списке
                        document.forms["edit-rule-condition-form"].elements["Fact[fact_template_name]"].value =
                            node.factTemplateId;
                        // Выбор шаблона факта в списке
                        document.forms["edit-rule-condition-form"].elements["Fact[fact_template]"].value =
                            node.factTemplateId;
                        // Подстановка названия факта в поле ввода
                        document.forms["edit-rule-condition-form"].elements["Fact[name]"].value = node.text;
                        // Подстановка коэффициентов уверенности факта в поле ввода
                        document.forms["edit-rule-condition-form"].elements["Fact[certainty_factor]"].value =
                            node.certaintyFactor;
                        // Подстановка описания факта в поле ввода, если оно существует
                        if (node.description)
                            document.forms["edit-rule-condition-form"].elements["Fact[description]"].value =
                                node.description;
                    }
                });
            }
            // Если известен id выбранного факта (действия правила)
            if (fact_id != 0 && rule_condition_id == 0 && rule_action_id != 0) {
                // Цикл по всем узлам дерева элементов БЗ
                $.each(tree.getNodes(), function (id, node) {
                    // Если данный узел выбран и его id совпадает с id факта
                    if (node.state.selected && node.id == fact_id) {
                        // Скрытие слоя скрытого поля шаблона факта
                        $(".field-fact-fact_template").hide();
                        // Очистка полей ввода на форме
                        document.forms["edit-rule-action-form"].reset();
                        // Выбор шаблона факта в списке
                        document.forms["edit-rule-action-form"].elements["Fact[fact_template_name]"].value =
                            node.factTemplateId;
                        // Выбор шаблона факта в списке
                        document.forms["edit-rule-action-form"].elements["Fact[fact_template]"].value =
                            node.factTemplateId;
                        // Подстановка названия факта в поле ввода
                        document.forms["edit-rule-action-form"].elements["Fact[name]"].value = node.text;
                        // Подстановка коэффициентов уверенности факта в поле ввода
                        document.forms["edit-rule-action-form"].elements["Fact[certainty_factor]"].value =
                            node.certaintyFactor;
                        // Подстановка описания факта в поле ввода, если оно существует
                        if (node.description)
                            document.forms["edit-rule-action-form"].elements["Fact[description]"].value =
                                node.description;
                    }
                });
            }
            // Если известен id выбранного начального факта и его слота
            if (fact_id != 0 && fact_slot_id != 0) {
                // Цикл по всем узлам дерева элементов БЗ
                $.each(tree.getNodes(), function (id, node) {
                    // Если данный узел выбран и его id совпадает с id слота факта
                    if (node.state.selected && node.id == fact_slot_id) {
                        // Очистка полей ввода на форме
                        document.forms["edit-initial-fact-slot-form"].reset();
                        // Подстановка названия слота факта в поле ввода
                        document.forms["edit-initial-fact-slot-form"].elements["FactSlot[name]"].value = node.text;
                        // Подстановка названия типа данных слота в поле ввода
                        document.forms["edit-initial-fact-slot-form"].elements["FactSlot[data_type]"].value =
                            $("#factslot-data_type option:contains('" + node.dataType + "')").attr("value");
                        // Подстановка текущего значения слота факта в поле ввода
                        document.forms["edit-initial-fact-slot-form"].elements["FactSlot[value]"].value = node.value;
                        // Подстановка описания слота факта в поле ввода, если оно существует
                        if (node.description)
                            document.forms["edit-initial-fact-slot-form"].elements["FactSlot[description]"].value =
                                node.description;
                    }
                });
            }
            // Если известен id выбранного условия правила и его слота
            if (rule_condition_id != 0 && rule_condition_slot_id != 0) {
                // Цикл по всем узлам дерева элементов БЗ
                $.each(tree.getNodes(), function (id, node) {
                    // Если данный узел выбран и его id совпадает с id слота условия правила
                    if (node.state.selected && node.id == rule_condition_slot_id) {
                        // Очистка полей ввода на форме
                        document.forms["edit-rule-condition-slot-form"].reset();
                        // Подстановка названия слота факта в поле ввода
                        document.forms["edit-rule-condition-slot-form"].elements["FactSlot[name]"].value = node.text;
                        // Подстановка названия типа данных слота в поле ввода
                        document.forms["edit-rule-condition-slot-form"].elements["FactSlot[data_type]"].value =
                            $("#factslot-data_type option:contains('" + node.dataType + "')").attr("value");
                        // Подстановка текущего значения слота факта в поле ввода
                        document.forms["edit-rule-condition-slot-form"].elements["FactSlot[value]"].value = node.value;
                        // Подстановка описания слота факта в поле ввода, если оно существует
                        if (node.description)
                            document.forms["edit-rule-condition-slot-form"].elements["FactSlot[description]"].value =
                                node.description;
                    }
                });
            }
            // Если известен id выбранного действия правила и его слота
            if (rule_action_id != 0 && rule_action_slot_id != 0) {
                // Цикл по всем узлам дерева элементов БЗ
                $.each(tree.getNodes(), function (id, node) {
                    // Если данный узел выбран и его id совпадает с id слота действия правила
                    if (node.state.selected && node.id == rule_action_slot_id) {
                        // Очистка полей ввода на форме
                        document.forms["edit-rule-action-slot-form"].reset();
                        // Подстановка названия слота факта в поле ввода
                        document.forms["edit-rule-action-slot-form"].elements["FactSlot[name]"].value = node.text;
                        // Подстановка названия типа данных слота в поле ввода
                        document.forms["edit-rule-action-slot-form"].elements["FactSlot[data_type]"].value =
                            $("#factslot-data_type option:contains('" + node.dataType + "')").attr("value");
                        // Подстановка текущего значения слота факта в поле ввода
                        document.forms["edit-rule-action-slot-form"].elements["FactSlot[value]"].value = node.value;
                        // Подстановка описания слота факта в поле ввода, если оно существует
                        if (node.description)
                            document.forms["edit-rule-action-slot-form"].elements["FactSlot[description]"].value =
                                node.description;
                    }
                });
            }
            // Если известен id выбранного шаблона правила
            if (rule_template_id != 0) {
                // Формирование предупредительного сообщения
                alert = $('.edit-rule-template-alert');
                alert.text('');
                // Цикл по всем узлам дерева элементов БЗ
                $.each(tree.getNodes(), function (id, node) {
                    // Если данный узел выбран и его id совпадает с id шаблона правила
                    if (node.state.selected && node.id == rule_template_id) {
                        // Определение сообщения о связанных элементах
                        if (found_related_rules == false)
                            alert.append('<b><?= Yii::t('app', 'WARNING') ?></b> ' +
                                '<?= Yii::t('app', 'RVML_EDITOR_PAGE_RULE_TEMPLATE_MESSAGE_RELATED_CHANGES') ?></br>');
                        // Цикл по всем узлам дерева элементов БЗ
                        $.each(tree.getNodes(), function(index, element) {
                            // Нахождение начальных фактов свзанных с изменяемым шаблоном факта
                            if (rule_template_id == element.ruleTemplateId) {
                                if (!found_related_rules) {
                                    alert.append('<?= Yii::t('app', 'RVML_EDITOR_PAGE_RULES'); ?>: </br>');
                                    alert.append('<ul class="related-elements">');
                                }
                                alert.append('<li><i>' + element.text + '</i></li>');
                                found_related_rules = true;
                            }
                        });
                        if (found_related_rules)
                            alert.append('</ul>');
                        // Если найдены правила связанные с данным шаблоном правила, то вывод сообщения
                        if (found_related_rules)
                            alert.show();
                        else
                            alert.hide();
                        // Очистка полей ввода на форме
                        document.forms["edit-rule-template-form"].reset();
                        // Подстановка названия шаблона правила в поле ввода
                        document.forms["edit-rule-template-form"].elements["RuleTemplate[name]"].value = node.text;
                        // Подстановка значимости (важности) шаблона правила в поле ввода
                        document.forms["edit-rule-template-form"].elements["RuleTemplate[salience]"].value =
                            node.salience;
                        // Подстановка описания шаблона правила в поле ввода, если оно существует
                        if (node.description)
                            document.forms["edit-rule-template-form"].elements["RuleTemplate[description]"].value =
                                node.description;
                    }
                });
            }
            // Если известен id выбранного правила
            if (rule_id != 0) {
                // Цикл по всем узлам дерева элементов БЗ
                $.each(tree.getNodes(), function (id, node) {
                    // Если данный узел выбран и его id совпадает с id правила
                    if (node.state.selected && node.id == rule_id) {
                        // Очистка полей ввода на форме
                        document.forms["edit-rule-form"].reset();
                        // Выбор шаблона правила в списке
                        document.forms["edit-rule-form"].elements["Rule[rule_template]"].value =
                            node.ruleTemplateId;
                        // Подстановка названия правила в поле ввода
                        document.forms["edit-rule-form"].elements["Rule[name]"].value = node.text;
                        // Подстановка коэффициента уверенности правила в поле ввода
                        document.forms["edit-rule-form"].elements["Rule[certainty_factor]"].value =
                            node.certaintyFactor;
                        // Подстановка значимости (важности) правила в поле ввода
                        document.forms["edit-rule-form"].elements["Rule[salience]"].value =
                            node.salience;
                        // Подстановка описания правила в поле ввода, если оно существует
                        if (node.description)
                            document.forms["edit-rule-form"].elements["Rule[description]"].value =
                                node.description;
                    }
                });
            }
        });

        // Обработка нажатия кнопки удаления элемента БЗ на панели дерева
        $("#delete-element-button").click(function(e) {
            var found_related_rule_templates = false;
            var found_related_initial_facts = false;
            var found_related_rules = false;
            var alert;
            // Получение дерева элементов БЗ
            var tree = $("#rvml-tree-view").treeview(true);
            // Если известен id выбранного шаблона факта
            if (fact_template_id != 0) {
                // Формирование предупредительного сообщения
                alert = $(".delete-fact-template-alert");
                alert.text("");
                // Если выбран шаблон факта
                if (fact_template_id && !fact_template_slot_id)
                    alert.append("<b><?= Yii::t('app', 'WARNING') ?></b> " +
                        "<?= Yii::t('app', 'RVML_EDITOR_PAGE_FACT_TEMPLATE_MESSAGE_RELATED_REMOVES') ?></br>");
                // Если выбран слот шаблона факта
                if (fact_template_id && fact_template_slot_id)
                    alert.append("<b><?= Yii::t('app', 'WARNING') ?></b> " +
                        "<?= Yii::t('app', 'RVML_EDITOR_PAGE_FACT_TEMPLATE_SLOT_MESSAGE_RELATED_REMOVES') ?></br>");
                // Цикл по всем узлам дерева элементов БЗ
                $.each(tree.getNodes(), function(index, element) {
                    // Нахождение шаблонов правил свзанных с удаляемым шаблоном факта
                    if (fact_template_id == element.id && (element.operator || element.function)) {
                        if (!found_related_rule_templates) {
                            alert.append("<?= Yii::t('app', 'RVML_EDITOR_PAGE_RULE_TEMPLATES'); ?>: </br>");
                            alert.append("<ul class='related-elements'>");
                        }
                        // Нахождение шаблона правила
                        $.each(tree.getNodes(), function(i, local_parent) {
                            if (element.parentId == local_parent.nodeId)
                                $.each(tree.getNodes(), function(j, global_parent) {
                                    if (local_parent.parentId == global_parent.nodeId)
                                        alert.append('<li><i>' + global_parent.text + '</i></li>');
                                });
                        });
                        found_related_rule_templates = true;
                    }
                });
                if (found_related_rule_templates)
                    alert.append('</ul>');
                // Цикл по всем узлам дерева элементов БЗ
                $.each(tree.getNodes(), function(index, element) {
                    // Нахождение начальных фактов свзанных с удаляемым шаблоном факта
                    if (fact_template_id == element.factTemplateId && !element.operator && !element.function) {
                        if (!found_related_initial_facts) {
                            alert.append("<?= Yii::t('app', 'RVML_EDITOR_PAGE_INITIAL_FACTS'); ?>: </br>");
                            alert.append("<ul class='related-elements'>");
                        }
                        alert.append("<li><i>" + element.text + "</i></li>");
                        found_related_initial_facts = true;
                    }
                });
                if (found_related_initial_facts)
                    alert.append("</ul>");
                // Цикл по всем узлам дерева элементов БЗ
                $.each(tree.getNodes(), function(index, element) {
                    // Нахождение правил свзанных с удаляемым шаблоном факта
                    if (fact_template_id == element.factTemplateId && (element.operator || element.function)) {
                        if (!found_related_rules) {
                            alert.append("<?= Yii::t('app', 'RVML_EDITOR_PAGE_RULES'); ?>: </br>");
                            alert.append("<ul class='related-elements'>");
                        }
                        // Нахождение правил
                        $.each(tree.getNodes(), function(i, local_parent) {
                            if (element.parentId == local_parent.nodeId)
                                $.each(tree.getNodes(), function(j, global_parent) {
                                    if (local_parent.parentId == global_parent.nodeId)
                                        alert.append("<li><i>" + global_parent.text + "</i></li>");
                                });
                        });
                        found_related_rules = true;
                    }
                });
                if (found_related_rules)
                    alert.append("</ul>");
                // Если найдены элементы связанные с данным шаблоном факта, то вывод сообщения
                if (found_related_initial_facts || found_related_rule_templates || found_related_rules)
                    alert.show();
                else
                    alert.hide();
            }
            // Если известен id выбранного шаблона правила
            if (rule_template_id != 0) {
                // Формирование предупредительного сообщения
                alert = $(".delete-rule-template-alert");
                alert.text("");
                // Формирование сообщения об удаляемых связанных элементах
                alert.append("<b><?= Yii::t('app', 'WARNING') ?></b> " +
                    "<?= Yii::t('app', 'RVML_EDITOR_PAGE_RULE_TEMPLATE_MESSAGE_RELATED_REMOVES') ?></br>");
                // Цикл по всем узлам дерева элементов БЗ
                $.each(tree.getNodes(), function(index, element) {
                    // Нахождение правил свзанных с удаляемым шаблоном правила
                    if (rule_template_id == element.ruleTemplateId) {
                        if (!found_related_rules) {
                            alert.append("<?= Yii::t('app', 'RVML_EDITOR_PAGE_RULES'); ?>: </br>");
                            alert.append("<ul class='related-elements'>");
                        }
                        alert.append("<li><i>" + element.text + "</i></li>");
                        found_related_rules = true;
                    }
                });
                // Если найдены элементы связанные с данным шаблоном правила, то вывод сообщения
                if (found_related_rules) {
                    alert.append("</ul>");
                    alert.show();
                } else
                    alert.hide();
            }
        });
    });
</script>

<!-- Определение дерева элементов базы знаний (продукционной модели) -->
<div id="production-tree-view" class="col-md-2">
    <!-- Панель с кнопками -->
    <div id="button-panel">
        <?php echo Button::widget([
            'label' => '<span class="glyphicon glyphicon-plus"></span>',
            'encodeLabel' => false,
            'options' => [
                'id' => 'add-element-button',
                'class' => 'disabled btn-xs btn-default',
                'data-toggle' => 'modal',
                'data-target' => '',
                'data-toggle-title' => 'tooltip',
                'data-original-title' => ''
            ]
        ]); ?>
        <?php echo Button::widget([
            'label' => '<span class="glyphicon glyphicon-pencil"></span>',
            'encodeLabel' => false,
            'options' => [
                'id' => 'edit-element-button',
                'class' => 'disabled btn-xs btn-default',
                'data-toggle' => 'modal',
                'data-target' => '',
                'data-toggle-title' => 'tooltip',
                'data-original-title' => ''
            ]
        ]); ?>
        <?php echo Button::widget([
            'label' => '<span class="glyphicon glyphicon-trash"></span>',
            'encodeLabel' => false,
            'options' => [
                'id' => 'delete-element-button',
                'class' => 'disabled btn-xs btn-default',
                'data-toggle' => 'modal',
                'data-target' => '',
                'data-toggle-title' => 'tooltip',
                'data-original-title' => ''
            ]
        ]); ?>
    </div>
    <!-- Дерево элементов БЗ -->
    <div id="tree-view" class="col-sm-12">
        <?php
            // Названия title для кнопок на панели дерава элементов БЗ
            $edit_fact_template_title = Yii::t('app', 'RVML_EDITOR_PAGE_EDIT_FACT_TEMPLATE');
            $delete_fact_template_title = Yii::t('app', 'RVML_EDITOR_PAGE_DELETE_FACT_TEMPLATE');
            $add_fact_template_slot_title = Yii::t('app', 'RVML_EDITOR_PAGE_ADD_NEW_FACT_TEMPLATE_SLOT');
            $edit_fact_template_slot_title = Yii::t('app', 'RVML_EDITOR_PAGE_EDIT_FACT_TEMPLATE_SLOT');
            $delete_fact_template_slot_title = Yii::t('app', 'RVML_EDITOR_PAGE_DELETE_FACT_TEMPLATE_SLOT');
            $edit_rule_template_title = Yii::t('app', 'RVML_EDITOR_PAGE_EDIT_RULE_TEMPLATE');
            $delete_rule_template_title = Yii::t('app', 'RVML_EDITOR_PAGE_DELETE_RULE_TEMPLATE');
            $edit_initial_fact_title = Yii::t('app', 'RVML_EDITOR_PAGE_EDIT_INITIAL_FACT');
            $delete_initial_fact_title = Yii::t('app', 'RVML_EDITOR_PAGE_DELETE_INITIAL_FACT');
            $edit_initial_fact_slot_value_title = Yii::t('app', 'RVML_EDITOR_PAGE_EDIT_INITIAL_FACT_SLOT_VALUE');
            $edit_rule_title = Yii::t('app', 'RVML_EDITOR_PAGE_EDIT_RULE');
            $delete_rule_title = Yii::t('app', 'RVML_EDITOR_PAGE_DELETE_RULE');
            $edit_rule_condition_title = Yii::t('app', 'RVML_EDITOR_PAGE_EDIT_RULE_CONDITION');
            $edit_rule_action_title = Yii::t('app', 'RVML_EDITOR_PAGE_EDIT_RULE_ACTION');
            $edit_rule_condition_slot_value_title = Yii::t('app', 'RVML_EDITOR_PAGE_EDIT_RULE_CONDITION_SLOT_VALUE');
            $edit_rule_action_slot_value_title = Yii::t('app', 'RVML_EDITOR_PAGE_EDIT_RULE_ACTION_SLOT_VALUE');
            // Обработчик выбора элемента дерева БЗ
            $onSelect = new JsExpression(<<<JS
                function selectedElement(undefined, item) {
                    // Обнуление глобальных переменных id выбранных элементов БЗ
                    fact_template_id = 0;
                    fact_id = 0;
                    fact_template_slot_id = 0;
                    fact_slot_id = 0;
                    rule_template_id = 0;
                    rule_id = 0;
                    rule_condition_id = 0;
                    rule_action_id = 0;
                    rule_condition_slot_id = 0;
                    rule_action_slot_id = 0;
                    // Удаление всех связей
                    instance.detachEveryConnection();
                    // Удаление всех точек связывания
                    instance.deleteEveryEndpoint();
                    // Кнопки на панели инструментов дерева элементов БЗ
                    var add_element_button = document.getElementById("add-element-button");
                    var edit_element_button = document.getElementById("edit-element-button");
                    var delete_element_button = document.getElementById("delete-element-button");
                    // Активация кнопок на панели дерева элементов БЗ
                    edit_element_button.classList.remove("disabled");
                    delete_element_button.classList.remove("disabled");
                    // Получение дерева элементов БЗ
                    var tree = $('#rvml-tree-view').treeview(true);
                    // Если выбранный элемент является слотом
                    if (item.icon == 'glyphicon glyphicon-tag') {
                        var flag = false;
                        // Цикл по всем узлам дерева элементов БЗ
                        $.each(tree.getNodes(), function(id, node) {
                            // Цикл по всем дочерним узлам элемента БЗ
                            $.each(node.nodes, function(id, attr_node) {
                                // Нахождение узла слота
                                if (attr_node.id == item.id && flag == false) {
                                    flag = true;
                                    // Если родительский элемент соответствует шаблону факта
                                    if (node.icon == 'glyphicon glyphicon-unchecked' && item.level == 3) {
                                        // Деактивация кнопки добавления на панели дерева элементов БЗ
                                        add_element_button.classList.add("disabled");
                                        // Установка целевой модальной формы
                                        add_element_button.setAttribute("data-target", "");
                                        edit_element_button.setAttribute("data-target",
                                            "#editFactTemplateSlotModalForm");
                                        delete_element_button.setAttribute("data-target",
                                            "#deleteFactTemplateSlotModalForm");
                                        // Установка подсказок для кнопок на панели дерева элементов БЗ
                                        add_element_button.setAttribute("data-original-title", "");
                                        edit_element_button.setAttribute("data-original-title",
                                            "$edit_fact_template_slot_title");
                                        delete_element_button.setAttribute("data-original-title",
                                            "$delete_fact_template_slot_title");
                                        // Запоминание id выбранного шаблона факта
                                        fact_template_id = node.id;
                                        // Запоминание id выбранного слота шаблона факта
                                        fact_template_slot_id = item.id;
                                        // Вызов функции выбора родительского элемента БЗ для данного слота
                                        selectedFactTemplate(node);
                                    }
                                    // Если родительский элемент соответствует шаблону факта (элменту в шаблоне правила)
                                    if (node.icon == 'glyphicon glyphicon-unchecked' && item.level == 5) {
                                        // Деактивация кнопок на панели дерева элементов БЗ
                                        add_element_button.classList.add("disabled");
                                        edit_element_button.classList.add("disabled");
                                        delete_element_button.classList.add("disabled");
                                        // Установка целевой модальной формы
                                        add_element_button.setAttribute("data-target", "");
                                        edit_element_button.setAttribute("data-target", "");
                                        delete_element_button.setAttribute("data-target", "");
                                        // Установка подсказок для кнопок на панели дерева элементов БЗ
                                        add_element_button.setAttribute("data-original-title", "");
                                        edit_element_button.setAttribute("data-original-title", "");
                                        delete_element_button.setAttribute("data-original-title", "");
                                        // Вызов функции выбора родительского элемента БЗ для данного слота
                                        selectedFactTemplate(node);
                                    }
                                    // Если родительский элемент соответствует начальному факту
                                    if (node.icon == 'glyphicon glyphicon-stop' && item.level == 3) {
                                        // Деактивация кнопки добавления на панели дерева элементов БЗ
                                        add_element_button.classList.add("disabled");
                                        // Деактивация кнопки удаления на панели дерева элементов БЗ
                                        delete_element_button.classList.add("disabled");
                                        // Установка целевой модальной формы
                                        add_element_button.setAttribute("data-target", "");
                                        edit_element_button.setAttribute("data-target", "#editInitialFactSlotModalForm");
                                        delete_element_button.setAttribute("data-target", "");
                                        // Установка подсказок для кнопок на панели дерева элементов БЗ
                                        add_element_button.setAttribute("data-original-title", "");
                                        edit_element_button.setAttribute("data-original-title",
                                            "$edit_initial_fact_slot_value_title");
                                        delete_element_button.setAttribute("data-original-title", "");
                                        // Запоминание id выбранного начального факта
                                        fact_id = node.id;
                                        // Запоминание id выбранного слота факта
                                        fact_slot_id = item.id;
                                        // Вызов функции выбора родительского элемента БЗ для данного слота
                                        selectedFact(node);
                                    }
                                    // Если родительский элемент соответствует факту (условию правила)
                                    if (node.icon == 'glyphicon glyphicon-stop' && node.ruleConditionId &&
                                        item.level == 5) {
                                        // Деактивация кнопки добавления на панели дерева элементов БЗ
                                        add_element_button.classList.add("disabled");
                                        // Деактивация кнопки удаления на панели дерева элементов БЗ
                                        delete_element_button.classList.add("disabled");
                                        // Установка целевой модальной формы
                                        add_element_button.setAttribute("data-target", "");
                                        edit_element_button.setAttribute("data-target",
                                            "#editRuleConditionSlotModalForm");
                                        delete_element_button.setAttribute("data-target", "");
                                        // Установка подсказок для кнопок на панели дерева элементов БЗ
                                        add_element_button.setAttribute("data-original-title", "");
                                        edit_element_button.setAttribute("data-original-title",
                                            "$edit_rule_condition_slot_value_title");
                                        delete_element_button.setAttribute("data-original-title", "");
                                        // Запоминание id выбранного факта
                                        fact_id = node.id;
                                        // Запоминание id выбранного условия правила
                                        rule_condition_id = node.ruleConditionId;
                                        // Запоминание id выбранного слота условия правила
                                        rule_condition_slot_id = item.id;
                                        // Вызов функции выбора родительского элемента БЗ для данного слота
                                        selectedFact(node);
                                    }
                                    // Если родительский элемент соответствует факту (действию правила)
                                    if (node.icon == 'glyphicon glyphicon-stop' && node.ruleActionId &&
                                        item.level == 5) {
                                        // Деактивация кнопки добавления на панели дерева элементов БЗ
                                        add_element_button.classList.add("disabled");
                                        // Деактивация кнопки удаления на панели дерева элементов БЗ
                                        delete_element_button.classList.add("disabled");
                                        // Установка целевой модальной формы
                                        add_element_button.setAttribute("data-target", "");
                                        edit_element_button.setAttribute("data-target",
                                            "#editRuleActionSlotModalForm");
                                        delete_element_button.setAttribute("data-target", "");
                                        // Установка подсказок для кнопок на панели дерева элементов БЗ
                                        add_element_button.setAttribute("data-original-title", "");
                                        edit_element_button.setAttribute("data-original-title",
                                            "$edit_rule_action_slot_value_title");
                                        delete_element_button.setAttribute("data-original-title", "");
                                        // Запоминание id выбранного факта
                                        fact_id = node.id;
                                        // Запоминание id выбранного действия правила
                                        rule_action_id = node.ruleActionId;
                                        // Запоминание id выбранного слота действия правила
                                        rule_action_slot_id = item.id;
                                        // Вызов функции выбора родительского элемента БЗ для данного слота
                                        selectedFact(node);
                                    }
                                }
                            });
                        });
                    }
                    // Если выбранный элемент является шаблоном факта
                    if (item.icon == 'glyphicon glyphicon-unchecked' && item.level == 2) {
                        // Запоминание id выбранного шаблона факта
                        fact_template_id = item.id;
                        // Активация кнопки добавления на панели дерева элементов БЗ
                        add_element_button.classList.remove("disabled");
                        // Установка целевой модальной формы
                        add_element_button.setAttribute("data-target", "#addFactTemplateSlotModalForm");
                        edit_element_button.setAttribute("data-target", "#editFactTemplateModalForm");
                        delete_element_button.setAttribute("data-target", "#deleteFactTemplateModalForm");
                        // Установка подсказок для кнопок на панели дерева элементов БЗ
                        add_element_button.setAttribute("data-original-title", "$add_fact_template_slot_title");
                        edit_element_button.setAttribute("data-original-title", "$edit_fact_template_title");
                        delete_element_button.setAttribute("data-original-title", "$delete_fact_template_title");
                        // Вызов функции выбора шаблона факта в дереве элементов БЗ
                        selectedFactTemplate(item);
                    }
                    // Если выбранный элемент является шаблоном факта (элементом в шаблоне правила)
                    if (item.icon == 'glyphicon glyphicon-unchecked' && item.level != 2) {
                        // Деактивация кнопок на панели дерева элементов БЗ
                        add_element_button.classList.add("disabled");
                        edit_element_button.classList.add("disabled");
                        delete_element_button.classList.add("disabled");
                        // Установка целевой модальной формы
                        add_element_button.setAttribute("data-target", "");
                        edit_element_button.setAttribute("data-target", "");
                        delete_element_button.setAttribute("data-target", "");
                        // Установка подсказок для кнопок на панели дерева элементов БЗ
                        add_element_button.setAttribute("data-original-title", "");
                        edit_element_button.setAttribute("data-original-title", "");
                        delete_element_button.setAttribute("data-original-title", "");
                        // Вызов функции выбора шаблона факта в дереве элементов БЗ
                        selectedFactTemplate(item);
                    }
                    // Если выбранный элемент является шаблоном правила
                    if(item.icon == 'glyphicon glyphicon-registration-mark') {
                        // Запоминание id выбранного шаблона правила
                        rule_template_id = item.id;
                        // Присваивание id выбранного шаблона правила скрытому полю pjax в модальном окне
                        document.getElementById("pjax-rule-template-input").value = item.id;
                        // Вызов события нажатия кнопки для pjax в модальном окне
                        document.getElementById("pjax-rule-template-button").click();
                        // Деактивация кнопки добавления на панели дерева элементов БЗ
                        add_element_button.classList.add("disabled");
                        // Установка целевой модальной формы
                        add_element_button.setAttribute("data-target", "");
                        edit_element_button.setAttribute("data-target", "#editRuleTemplateModalForm");
                        delete_element_button.setAttribute("data-target", "#deleteRuleTemplateModalForm");
                        // Установка подсказок для кнопок на панели дерева элементов БЗ
                        add_element_button.setAttribute("data-original-title", "");
                        edit_element_button.setAttribute("data-original-title", "$edit_rule_template_title");
                        delete_element_button.setAttribute("data-original-title", "$delete_rule_template_title");
                        // Вызов функции выбора шаблона правила в дереве элементов БЗ
                        selectedRuleTemplate(item);
                    }
                    // Если выбранный элемент является начальным фактом
                    if (item.icon == 'glyphicon glyphicon-stop' && item.level == 2) {
                        // Запоминание id выбранного начального факта
                        fact_id = item.id;
                        // Деактивация кнопки добавления на панели дерева элементов БЗ
                        add_element_button.classList.add("disabled");
                        // Установка целевой модальной формы
                        add_element_button.setAttribute("data-target", "");
                        edit_element_button.setAttribute("data-target", "#editInitialFactModalForm");
                        delete_element_button.setAttribute("data-target", "#deleteInitialFactModalForm");
                        // Установка подсказок для кнопок на панели дерева элементов БЗ
                        add_element_button.setAttribute("data-original-title", "");
                        edit_element_button.setAttribute("data-original-title", "$edit_initial_fact_title");
                        delete_element_button.setAttribute("data-original-title", "$delete_initial_fact_title");
                        // Вызов функции выбора факта в дереве элементов БЗ
                        selectedFact(item);
                    }
                    // Если выбранный элемент является условием в правиле
                    if (item.icon == 'glyphicon glyphicon-stop' && item.level == 4 && item.ruleConditionId) {
                        // Запоминание id выбранного факта
                        fact_id = item.id;
                        // Запоминание id выбранного условия правила
                        rule_condition_id = item.ruleConditionId;
                        // Деактивация кнопок на панели дерева элементов БЗ
                        add_element_button.classList.add("disabled");
                        delete_element_button.classList.add("disabled");
                        // Установка целевой модальной формы
                        add_element_button.setAttribute("data-target", "");
                        edit_element_button.setAttribute("data-target", "#editRuleConditionModalForm");
                        delete_element_button.setAttribute("data-target", "");
                        // Установка подсказок для кнопок на панели дерева элементов БЗ
                        add_element_button.setAttribute("data-original-title", "");
                        edit_element_button.setAttribute("data-original-title", "$edit_rule_condition_title");
                        delete_element_button.setAttribute("data-original-title", "");
                        // Вызов функции выбора факта в дереве элементов БЗ
                        selectedFact(item);
                    }
                    // Если выбранный элемент является действием в правиле
                    if (item.icon == 'glyphicon glyphicon-stop' && item.level == 4 && item.ruleActionId) {
                        // Запоминание id выбранного факта
                        fact_id = item.id;
                        // Запоминание id выбранного действия правила
                        rule_action_id = item.ruleActionId;
                        // Деактивация кнопок на панели дерева элементов БЗ
                        add_element_button.classList.add("disabled");
                        delete_element_button.classList.add("disabled");
                        // Установка целевой модальной формы
                        add_element_button.setAttribute("data-target", "");
                        edit_element_button.setAttribute("data-target", "#editRuleActionModalForm");
                        delete_element_button.setAttribute("data-target", "");
                        // Установка подсказок для кнопок на панели дерева элементов БЗ
                        add_element_button.setAttribute("data-original-title", "");
                        edit_element_button.setAttribute("data-original-title", "$edit_rule_action_title");
                        delete_element_button.setAttribute("data-original-title", "");
                        // Вызов функции выбора факта в дереве элементов БЗ
                        selectedFact(item);
                    }
                    // Если выбранный элемент является правилом
                    if(item.icon == 'glyphicon glyphicon-record') {
                        // Запоминание id выбранного правила
                        rule_id = item.id;
                        // Присваивание id выбранного правила скрытому полю pjax в модальном окне
                        document.getElementById("pjax-rule-input").value = item.id;
                        // Вызов события нажатия кнопки для pjax в модальном окне
                        document.getElementById("pjax-rule-button").click();
                        // Деактивация кнопки добавления на панели дерева элементов БЗ
                        add_element_button.classList.add("disabled");
                        // Установка целевой модальной формы
                        add_element_button.setAttribute("data-target", "");
                        edit_element_button.setAttribute("data-target", "#editRuleModalForm");
                        delete_element_button.setAttribute("data-target", "#deleteRuleModalForm");
                        // Установка подсказок для кнопок на панели дерева элементов БЗ
                        add_element_button.setAttribute("data-original-title", "");
                        edit_element_button.setAttribute("data-original-title", "$edit_rule_title");
                        delete_element_button.setAttribute("data-original-title", "$delete_rule_title");
                        // Вызов функции выбора правила в дереве элементов БЗ
                        selectedRule(item);
                    }
                    // Обновление формы редактора
                    instance.repaintEverything();
                }
JS
            );
            // Дерево элементов БЗ
            echo $groupsContent = TreeView::widget([
                'id' => 'rvml-tree-view',
                'data' => $data,
                'size' => TreeView::SIZE_MIDDLE,
                'header' => Yii::t('app', 'RVML_EDITOR_PAGE_ELEMENTS'),
                'searchOptions' => [
                    'inputOptions' => [
                        'placeholder' => Yii::t('app', 'RVML_EDITOR_PAGE_SEARCH'),
                    ],
                ],
                'clientOptions' => [
                    'onNodeSelected' => $onSelect,
                    'selectedBackColor' => '#428bca',
                    'searchResultBackColor' => '#800000',
                    'searchResultColor' => '#ffffff',
                    'borderColor' => '#ffffff',
                    'showBorder' => true,
                    'showTags' => true
                ],
            ]);
        ?>
    </div>
</div>

<!-- Определение рабочей области редактора -->
<div id="production-model" class="col-md-10"></div>