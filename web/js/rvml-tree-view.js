<!-- Обработка выбора шаблона факта в дереве элементов БЗ -->
function selectedFactTemplate(item) {
    // Рабочая область (слой) RVML редактора
    var production_model = document.getElementById('production-model');
    // Высота дерева элементов метамоделей
    var production_tree_view_height = document.getElementById("production-tree-view").offsetHeight;
    // Очистка рабочей области (удаление всех элементов)
    while(production_model.firstChild)
        production_model.removeChild(production_model.firstChild);
    // Формирование слоя шаблона факта
    var fact_template = $('<div>').attr('id', 'fact-template-' + item['id']).
        addClass('fact-template').text(item['text']);
    // Обход текущего узла шаблона факта (слотов), если они существуют
    if ('nodes' in item)
        $.each(item['nodes'], function(id, slot_node) {
            // Формирование слоя слота шаблона факта
            var fact_template_slot = $('<div>').attr('id', 'fact-template-slot-' + slot_node['id']).
                addClass('fact-template-slot').text(slot_node['text'] + " : " + slot_node['dataType']);
            if (slot_node['defaultValue'])
                fact_template_slot.append(" = \"" + slot_node['defaultValue'] + "\"");
            // Отметка первого слота шаблона факта
            if (id == 0)
                fact_template_slot.addClass('first-slot');
            // Добавление слоя слота шаблона факта к слою шаблона факта
            fact_template.append(fact_template_slot);
        });
    // Добавление слоя шаблона факта на рабочую область (слой) редактора продукционной модели
    $('#production-model').append(fact_template);
    // Позиционирование слоя шаблона факта
    var current_fact_template = document.getElementById('fact-template-' + item['id']).style;
    current_fact_template.top = '50px';
    current_fact_template.left = '50px';
    // Добавление возможности перетаскивания слоя шаблона факта
    instance.draggable(fact_template, { containment: '#production-model' });
    // Изменение высоты рабочей области редактора в зависимости от его содержимого
    production_model.style.height = production_tree_view_height + "px";
}

<!-- Обработка выбора начального факта в дереве элементов БЗ -->
function selectedFact(item) {
    // Рабочая область (слой) RVML редактора
    var production_model = document.getElementById('production-model');
    // Высота дерева элементов метамоделей
    var production_tree_view_height = document.getElementById("production-tree-view").offsetHeight;
    // Очистка рабочей области (удаление всех элементов)
    while(production_model.firstChild)
        production_model.removeChild(production_model.firstChild);
    var production_model_layer = $('#production-model');
    // Переменная для слоя шаблона факта
    var fact_template;
    // Получение дерева элементов БЗ
    var tree = $('#rvml-tree-view').treeview(true);
    // Цикл по всем узлам дерева элементов БЗ
    $.each(tree.getNodes(), function(id, node) {
        // Нахождение узла шаблона факта
        if (node.id == item['factTemplateId']) {
            // Формирование слоя шаблона факта
            fact_template = $('<div>').attr('id', 'fact-template-' + item['factTemplateId']).
                addClass('fact-template').text(node.text);
            // Обход текущего узла шаблона факта (слотов)
            $.each(node.nodes, function(id, slot_node) {
                // Формирование слоя слота шаблона факта
                var fact_template_slot = $('<div>').attr('id', 'fact-template-slot-' + slot_node.id).
                    addClass('fact-template-slot').text(slot_node.text + " : " + slot_node.dataType);
                if (slot_node.defaultValue)
                    fact_template_slot.append(" = \"" + slot_node.defaultValue + "\"");
                // Отметка первого слота шаблона факта
                if (id == 0)
                    fact_template_slot.addClass('first-slot');
                // Добавление слоя слота шаблона факта к слою шаблона факта
                fact_template.append(fact_template_slot);
            });

        }
    });
    // Добавление слоя шаблона факта на рабочую область (слой) редактора продукционной модели
    production_model_layer.append(fact_template);
    // Позиционирование слоя шаблона факта
    var current_fact_template = document.getElementById('fact-template-' + item['factTemplateId']).style;
    current_fact_template.top = '50px';
    current_fact_template.left = '50px';
    // Добавление возможности перетаскивания слоя шаблона факта
    instance.draggable(fact_template, { containment: '#production-model' });
    // Формирование слоя коэффициента уверенности факта
    var certainty_factor = '-';
    if (item['certaintyFactor'])
        certainty_factor = item['certaintyFactor'];
    var fact_cf = $('<div>').attr('id', 'fact-cf-' + item['id']).addClass('fact-cf').text(certainty_factor);
    // Формирование слоя факта
    var fact = $('<div>').attr('id', 'fact-' + item['id']).addClass('fact').text(item['text']);
    // Добавление слоя коэффициента уверенности факта в слой факта
    fact.append(fact_cf);
    var slot_number = 0;
    // Обход текущего узла факта (слотов)
    $.each(item['nodes'], function(id, slot_node) {
        slot_number++;
        // Формирование слоя слота факта
        var fact_slot = $('<div>').attr('id', 'fact-slot-' + slot_node['id']).addClass('fact-slot').
            text(slot_node['text']);
        if (slot_node['value'])
            fact_slot.append(" = \"" + slot_node['value'] + "\"");
        // Отметка первого слота факта
        if (id == 0)
            fact_slot.addClass('first-slot');
        // Добавление слоя слота факта к слою факта
        fact.append(fact_slot);
    });
    // Добавление слоя факта на рабочую область (слой) редактора продукционной модели
    production_model_layer.append(fact);
    // Позиционирование слоя факта
    var current_fact = document.getElementById('fact-' + item['id']).style;
    current_fact.top = slot_number * 50 + 130 + 'px';
    current_fact.left = '50px';
    // Добавление возможности перетаскивания слоя факта
    instance.draggable(fact, { containment: '#production-model' });
    // Добавление связи между фактом и его шаблоном
    var current_connection = instance.connect({
        source: fact,
        target: fact_template,
        anchors: [
            [ "Perimeter", { shape: "Rectangle" } ],
            [ "Perimeter", { shape: "Rectangle" } ]
        ],
        connector: [ "Straight" ],
        endpointHoverStyle: { fillStyle: "#428bca" },
        overlays : [
            [ "Arrow", {
                location: 1,
                id: "arrow",
                length: 20,
                foldback: 1,
                cssClass: "arrow",
                paintStyle: { fillStyle: "#ffffff" }
            } ]
        ],
        paintStyle: {
            strokeStyle: "#800000",
            lineWidth: 2,
            outlineWidth: 4,
            outlineColor: "transparent",
            dashstyle: "2 2"
        }
    });
    // Изменение высоты рабочей области редактора в зависимости от его содержимого
    production_model.style.height = production_tree_view_height + "px";
}

<!-- Обработка выбора шаблона правила в дереве элементов БЗ -->
function selectedRuleTemplate(item) {
    // Рабочая область (слой) RVML редактора
    var production_model = document.getElementById('production-model');
    // Высота дерева элементов метамоделей
    var production_tree_view_height = document.getElementById("production-tree-view").offsetHeight;
    // Очистка рабочей области (удаление всех элементов)
    while(production_model.firstChild)
        production_model.removeChild(production_model.firstChild);
    // Переменные для позиционирования слоев
    var left;
    var top;
    var condition_top;
    // Формирование слоя блока текста для шаблона правила
    var rule_template_text = $('<div>').attr('id', 'rule-template-text-' + item['id']).
        addClass('rule-template-text').text(item['text']);
    // Формирование слоя блока коэффициентов для шаблона правила
    var rule_template_block = $('<div>').attr('id', 'rule-template-block-' + item['id']).
        addClass('rule-template-block');
    // Формирование слоя блока коэффициента уверенности
    var rule_template_cf = $('<div>').attr('id', 'rule-template-cf-' + item['id']).
        addClass('rule-template-cf').text('-');
    // Формирование слоя блока важности
    var salience = '-';
    if (item['salience'] != ('' || null))
        salience = item['salience'];
    var rule_template_salience = $('<div>').attr('id', 'rule-template-salience-' + item['id']).
        addClass('rule-template-salience').text(salience);
    // Добавление слоя коэффициента уверенности и важности в блок коэффициентов
    rule_template_block.append(rule_template_cf);
    rule_template_block.append(rule_template_salience);
    // Формирование слоя шаблона правила
    var rule_template = $('<div>').attr('id', 'rule-template-' + item['id']).
        addClass('rule-template');
    // Добавление слоя блока текста для шаблона правила
    rule_template.append(rule_template_text);
    // Добавление слоя блока коэффициентов для шаблона правила
    rule_template.append(rule_template_block);
    // Добавление слоя шаблона правила на рабочую область (слой) редактора продукционной модели
    $('#production-model').append(rule_template);
    // Добавление возможности перетаскивания слоя шаблона правила
    instance.draggable(rule_template, { containment: '#production-model' });
    // Слой оператора условий шаблона правила
    var rule_template_operator;

    // Количество операторов "ИЛИ" у условий в данном шаблоне правила
    var rule_template_operator_number = 0;
    // Обход текущего узла факта (условия)
    $.each(item['nodes'][0]['nodes'], function(id, condition_node) {
        // Подсчет операторов условий "ИЛИ"
        if (condition_node['operator'] == 'OR')
            rule_template_operator_number++;
    });

    // Обход текущего узла шаблона факта (условия)
    $.each(item['nodes'][0]['nodes'], function(id, condition_node) {
        // Если операторов условий "ИЛИ" больше одного и не существует слоя данного оператора
        if (rule_template_operator_number > 1 && $('div').hasClass('operator') == false) {
            // Формирование слоя оператора условий правила
            rule_template_operator = $('<div>').attr('id', 'operator').
                addClass('operator').text('OR');
            // Добавление слоя оператора условий правила на слой рабочей области редактора
            $('#production-model').append(rule_template_operator);
            // Добавление возможности перетаскивания слоя оператора условий правила
            instance.draggable(rule_template_operator, { containment: '#production-model' });
        }
        // Если операторов условий "ИЛИ" больше одного и у текущего условия оператор "ИЛИ"
        if (rule_template_operator_number > 1 && condition_node['operator'] == 'OR') {
            // Формирование слоя условия шаблона правила
            var rule_template_condition = $('<div>').attr('id', 'rule-template-condition-' +
                condition_node['ruleTemplateConditionId']).addClass('rule-template-condition').
                text(condition_node['text']);
            // Обход текущего узла условия шаблона правила (слотов шаблонов фактов)
            $.each(condition_node['nodes'], function(id, slot_node) {
                // Формирование слоя слота шаблона факта
                var fact_template_slot = $('<div>').attr('id', 'fact-template-slot-' + slot_node['id']).
                    addClass('fact-template-slot').text(slot_node['text'] + " : " + slot_node['dataType']);
                if (slot_node['defaultValue'])
                    fact_template_slot.append(" = \"" + slot_node['defaultValue'] + "\"");
                // Отметка первого слота шаблона факта
                if (id == 0)
                    fact_template_slot.addClass('first-slot');
                // Добавление слоя слота шаблона факта к слою условия шаблона правила
                rule_template_condition.append(fact_template_slot);
            });
            // Добавление слоя условия шаблона правила на слой рабочей области редактора
            $('#production-model').append(rule_template_condition);
            // Добавление возможности перетаскивания слоя условия
            instance.draggable(rule_template_condition, { containment: '#production-model' });
            // Добавление связи между условием и оператором условий шаблона правила
            var current_connection = instance.connect({
                source: rule_template_condition,
                target: rule_template_operator,
                anchors: [
                    [ "Perimeter", { shape: "Rectangle" } ],
                    [ "Perimeter", { shape: "Ellipse" } ]
                ],
                connector: [ "Straight" ],
                endpointHoverStyle: { fillStyle: "#428bca" },
                overlays : [
                    [ "Arrow", {
                        location: 1,
                        id: "arrow",
                        length: 14,
                        foldback: 0.8,
                        cssClass: "arrow"
                    } ]
                ]
            });
        }
    });
    // Если слой оператора условий шаблона правила существует
    if (rule_template_operator != null) {
        // Добавление связи между оператором условий шаблона правила и шаблоном правила
        var rule_template_operator_connection = instance.connect({
            source: rule_template_operator,
            target: rule_template,
            anchors: [
                [ "Perimeter", { shape: "Ellipse" } ],
                [ "Perimeter", { shape: "Rectangle" } ]
            ],
            connector: [ "Straight" ],
            endpointHoverStyle: { fillStyle: "#428bca" },
            overlays : [
                [ "Arrow", {
                    location: 1,
                    id: "arrow",
                    length: 14,
                    foldback: 0.8,
                    cssClass: "arrow"
                } ]
            ]
        });
    }
    // Обход текущего узла шаблона факта (условия)
    $.each(item['nodes'][0]['nodes'], function(id, condition_node) {
        // Если у текущего условия нет оператора "ИЛИ"
        if (condition_node['operator'] == 'NONE' || condition_node['operator'] == 'AND' ||
            condition_node['operator'] == 'NOT' ||
            (rule_template_operator_number < 2 && condition_node['operator'] == 'OR')) {
            // Формирование слоя условия шаблона правила
            var rule_template_condition = $('<div>').attr('id', 'rule-template-condition-' +
                condition_node['ruleTemplateConditionId']).addClass('rule-template-condition').
                text(condition_node['text']);
            // Обход текущего узла условия шаблона правила (слотов шаблонов фактов)
            $.each(condition_node['nodes'], function(id, slot_node) {
                // Формирование слоя слота шаблона факта
                var fact_template_slot = $('<div>').attr('id', 'fact-template-slot-' + slot_node['id']).
                    addClass('fact-template-slot').text(slot_node['text'] + " : " + slot_node['dataType']);
                if (slot_node['defaultValue'])
                    fact_template_slot.append(" = \"" + slot_node['defaultValue'] + "\"");
                // Отметка первого слота шаблона факта
                if (id == 0)
                    fact_template_slot.addClass('first-slot');
                // Добавление слоя слота шаблона факта к слою условия шаблона правила
                rule_template_condition.append(fact_template_slot);
            });
            // Добавление слоя условия шаблона правила на слой рабочей области редактора
            $('#production-model').append(rule_template_condition);
            // Добавление возможности перетаскивания слоя условия
            instance.draggable(rule_template_condition, { containment: '#production-model' });
            // Формирование точек связывания
            var endpoint = [];
            if (condition_node['operator'] == 'NOT')
                endpoint = [
                    [ "Dot", { radius: 6, cssClass: "end-point" } ],
                    [ "Dot", { radius: 1, cssClass: "end-point" } ]
                ];
            // Добавление связи между условием шаблона правила и шаблоном правила
            var current_connection = instance.connect({
                source: rule_template_condition,
                target: rule_template,
                anchors: [
                    [ "Perimeter", { shape: "Rectangle" } ],
                    [ "Perimeter", { shape: "Rectangle" } ]
                ],
                connector: [ "Straight" ],
                endpoints: endpoint,
                endpointStyles: [
                    { fillStyle: "#faf4b8", strokeStyle: "#800000" },
                    { fillStyle: "#800000" }
                ],
                endpointHoverStyle: { fillStyle: "#428bca" },
                overlays : [
                    [ "Arrow", {
                        location: 1,
                        id: "arrow",
                        length: 14,
                        foldback: 0.8,
                        cssClass: "arrow"
                    } ]
                ]
            });
        }
    });
    // Позиционирование всех условий шаблона правила на рабочей области редактора
    left = 50;
    top = 50;
    condition_top = 50;
    $(".rule-template-condition").each(function(i) {
        if ((left + this.offsetWidth) > production_model.offsetWidth) {
            left = 50;
            condition_top = top + 100;
            top = top + 100;
        }
        $(this).css({
            left: left,
            top: condition_top
        });
        left = left + this.offsetWidth + 30;
        if (top < this.offsetHeight)
            top = this.offsetHeight;
        // Обновление формы редактора
        instance.repaintEverything();
    });
    // Если слой оператора условий шаблона правила существует
    if (rule_template_operator != null) {
        // Позиционирование слоя оператора условий шаблона правила на рабочей области редактора
        $(".operator").each(function(i) {
            $(this).css({
                left: 50,
                top: top + 100
            });
            // Обновление формы редактора
            instance.repaintEverything();
        });
        top = top + 100;
    }
    // Позиционирование узла шаблона правила на рабочей области редактора
    $(".rule-template").each(function(i) {
        $(this).css({
            left: 50,
            top: top + 100
        });
        // Обновление формы редактора
        instance.repaintEverything();
    });

    // Обход текущего узла шаблона факта (действия)
    $.each(item['nodes'][1]['nodes'], function(id, action_node) {
        // Формирование слоя действия шаблона правила
        var rule_template_action = $('<div>').attr('id', 'rule-template-action-' +
            action_node['ruleTemplateActionId']).addClass('rule-template-action').text(action_node['text']);
        // Обход текущего узла действия шаблона правила (слотов шаблонов фактов)
        $.each(action_node['nodes'], function(id, slot_node) {
            // Формирование слоя слота шаблона факта
            var fact_template_slot = $('<div>').attr('id', 'fact-template-slot-' + slot_node['id']).
                addClass('fact-template-slot').text(slot_node['text'] + " : " + slot_node['dataType']);
            if (slot_node['defaultValue'])
                fact_template_slot.append(" = \"" + slot_node['defaultValue'] + "\"");
            // Отметка первого слота шаблона факта
            if (id == 0)
                fact_template_slot.addClass('first-slot');
            // Добавление слоя слота к слою действия
            rule_template_action.append(fact_template_slot);
        });
        // Добавление слоя действия шаблона правила на слой рабочей области редактора
        $('#production-model').append(rule_template_action);
        // Добавление возможности перетаскивания слоя действия шаблона правила
        instance.draggable(rule_template_action, { containment: '#production-model' });
        // Добавление связи между действием шаблона правила и шаблоном правила
        var current_connection = instance.connect({
            source: rule_template,
            target: rule_template_action,
            anchors: [
                [ "Perimeter", { shape: "Rectangle" } ],
                [ "Perimeter", { shape: "Rectangle" } ]
            ],
            connector: [ "Straight" ],
            endpointHoverStyle: { fillStyle: "#428bca" },
            overlays : [
                [ "Arrow", {
                    location: 1,
                    id: "arrow",
                    length: 14,
                    foldback: 0.8,
                    cssClass: "arrow"
                } ],
                [ "Label", {
                    id: "label",
                    cssClass: action_node['function'] + "Label"
                } ]
            ]
        });
        // Добавление слоя наименования связи, если функция добавления (assert или none)
        if (action_node['function'] == 'none' || action_node['function'] == 'assert')
            current_connection.getOverlay("label").setLabel('+');
        // Добавление слоя наименования связи, если функция модификации (modify)
        if (action_node['function'] == 'modify')
            current_connection.getOverlay("label").setLabel('~');
        // Добавление слоя наименования связи, если функция удаления (retract)
        if (action_node['function'] == 'retract')
            current_connection.getOverlay("label").setLabel('-');
        // Добавление слоя наименования связи, если функция копирования (duplicate)
        if (action_node['function'] == 'duplicate')
            current_connection.getOverlay("label").setLabel('&#215;');
    });
    // Позиционирование всех действий шаблона правила на рабочей области редактора
    left = 50;
    condition_top = top + 200;
    $(".rule-template-action").each(function(i) {
        if ((left + this.offsetWidth) > production_model.offsetWidth) {
            left = 50;
            condition_top = top + 300;
            top = top + 100;
        }
        $(this).css({
            left: left,
            top: condition_top
        });
        left = left + this.offsetWidth + 30;
        if (top < this.offsetHeight)
            top = this.offsetHeight;
        // Обновление формы редактора
        instance.repaintEverything();
    });
    // Изменение высоты рабочей области редактора в зависимости от его содержимого
    if (top + 300 > production_tree_view_height)
        production_model.style.height = top + 300 + "px";
    else
        production_model.style.height = production_tree_view_height + "px";
}

<!-- Обработка выбора правила в дереве элементов БЗ -->
function selectedRule(item) {
    // Рабочая область (слой) RVML редактора
    var production_model = document.getElementById('production-model');
    // Высота дерева элементов метамоделей
    var production_tree_view_height = document.getElementById("production-tree-view").offsetHeight;
    // Очистка рабочей области (удаление всех элементов)
    while(production_model.firstChild)
        production_model.removeChild(production_model.firstChild);
    // Переменные для позиционирования слоев
    var left;
    var top;
    var condition_top;
    // Формирование слоя блока текста для правила
    var rule_text = $('<div>').attr('id', 'rule-text-' + item['id']).addClass('rule-text').
        text(item['text']);
    // Формирование слоя блока коэффициентов для правила
    var rule_block = $('<div>').attr('id', 'rule-block-' + item['id']).addClass('rule-block');
    // Формирование слоя блока коэффициента уверенности
    var rule_cf = $('<div>').attr('id', 'rule-cf-' + item['id']).addClass('rule-cf').text('-');
    if (item['certaintyFactor'] != null)
        rule_cf = $('<div>').attr('id', 'rule-cf-' + item['id']).addClass('rule-cf').text(item['certaintyFactor']);
    // Формирование слоя блока важности
    var salience = '-';
    if (item['salience'] != ('' || null))
        salience = item['salience'];
    var rule_salience = $('<div>').attr('id', 'rule-salience-' + item['id']).
        addClass('rule-salience').text(salience);
    // Добавление слоя коэффициента уверенности и важности в блок коэффициентов
    rule_block.append(rule_cf);
    rule_block.append(rule_salience);
    // Формирование слоя правила
    var rule = $('<div>').attr('id', 'rule-' + item['id']).addClass('rule');
    // Добавление слоя блока текста для правила
    rule.append(rule_text);
    // Добавление слоя блока коэффициентов для правила
    rule.append(rule_block);
    // Добавление слоя правила на рабочую область (слой) редактора продукционной модели
    $('#production-model').append(rule);
    // Добавление возможности перетаскивания слоя правила
    instance.draggable(rule, { containment: '#production-model' });
    // Слой оператора условий правила
    var rule_operator;

    // Количество операторов "ИЛИ" у условий в данном правиле
    var rule_operator_number = 0;
    // Обход текущего узла факта (условия)
    $.each(item['nodes'][0]['nodes'], function(id, condition_node) {
        // Подсчет операторов условий "ИЛИ"
        if (condition_node['operator'] == 'OR')
            rule_operator_number++;
    });

    // Обход текущего узла факта (условия)
    $.each(item['nodes'][0]['nodes'], function(id, condition_node) {
        // Если операторов условий "ИЛИ" больше одного и не существует слоя данного оператора
        if (rule_operator_number > 1 && $('div').hasClass('operator') == false) {
            // Формирование слоя оператора условий правила
            rule_operator = $('<div>').attr('id', 'operator').addClass('operator').text('OR');
            // Добавление слоя оператора условий правила на слой рабочей области редактора
            $('#production-model').append(rule_operator);
            // Добавление возможности перетаскивания слоя оператора условий правила
            instance.draggable(rule_operator, { containment: '#production-model' });
        }
        // Если операторов условий "ИЛИ" больше одного и у текущего условия оператор "ИЛИ"
        if (rule_operator_number > 1 && condition_node['operator'] == 'OR') {
            // Формирование слоя коэффициента уверенности условия правила
            var certainty_factor = '-';
            if (condition_node['certaintyFactor'])
                certainty_factor = condition_node['certaintyFactor'];
            var rule_condition_cf = $('<div>').attr('id', 'rule-condition-cf-' + item['id']).
                addClass('rule-condition-cf').text(certainty_factor);
            // Формирование слоя условия правила
            var rule_condition = $('<div>').attr('id', 'rule-condition-' + condition_node['ruleConditionId']).
                addClass('rule-condition').text(condition_node['text']);
            // Добавление слоя коэффициента уверенности условия правила в слой условия правила
            rule_condition.append(rule_condition_cf);
            // Обход текущего узла условия правила (слотов фактов)
            $.each(condition_node['nodes'], function(id, slot_node) {
                // Формирование слоя слота факта
                var fact_slot = $('<div>').attr('id', 'fact-slot-' + slot_node['id']).
                    addClass('fact-slot').text(slot_node['text']);
                if (slot_node['value'])
                    fact_slot.append(" = \"" + slot_node['value'] + "\"");
                // Отметка первого слота факта
                if (id == 0)
                    fact_slot.addClass('first-slot');
                // Добавление слоя слота факта к слою условия правила
                rule_condition.append(fact_slot);
            });
            // Добавление слоя условия правила на слой рабочей области редактора
            $('#production-model').append(rule_condition);
            // Добавление возможности перетаскивания слоя условия правила
            instance.draggable(rule_condition, { containment: '#production-model' });
            // Добавление связи между условием правила и оператором условий правила
            var current_connection = instance.connect({
                source: rule_condition,
                target: rule_operator,
                anchors: [
                    [ "Perimeter", { shape: "Rectangle" } ],
                    [ "Perimeter", { shape: "Ellipse" } ]
                ],
                connector: [ "Straight" ],
                endpointHoverStyle: { fillStyle: "#428bca" },
                overlays : [
                    [ "Arrow", {
                        location: 1,
                        id: "arrow",
                        length: 14,
                        foldback: 0.8,
                        cssClass: "arrow"
                    } ]
                ]
            });
        }
    });
    // Если слой оператора условий правила существует
    if (rule_operator != null) {
        // Добавление связи между оператором условий правила и правилом
        var rule_operator_connection = instance.connect({
            source: rule_operator,
            target: rule,
            anchors: [
                [ "Perimeter", { shape: "Ellipse" } ],
                [ "Perimeter", { shape: "Rectangle" } ]
            ],
            connector: [ "Straight" ],
            endpointHoverStyle: { fillStyle: "#428bca" },
            overlays : [
                [ "Arrow", {
                    location: 1,
                    id: "arrow",
                    length: 14,
                    foldback: 0.8,
                    cssClass: "arrow"
                } ]
            ]
        });
    }
    // Обход текущего узла факта (условия)
    $.each(item['nodes'][0]['nodes'], function(id, condition_node) {
        // Если у текущего условия нет оператора "ИЛИ"
        if (condition_node['operator'] == 'NONE' || condition_node['operator'] == 'AND' ||
            condition_node['operator'] == 'NOT' ||
            (rule_operator_number < 2 && condition_node['operator'] == 'OR')) {
            // Формирование слоя коэффициента уверенности условия правила
            var certainty_factor = '-';
            if (condition_node['certaintyFactor'])
                certainty_factor = condition_node['certaintyFactor'];
            var rule_condition_cf = $('<div>').attr('id', 'rule-condition-cf-' + item['id']).
                addClass('rule-condition-cf').text(certainty_factor);
            // Формирование слоя условия правила
            var rule_condition = $('<div>').attr('id', 'rule-condition-' + condition_node['ruleConditionId']).
                addClass('rule-condition').text(condition_node['text']);
            // Добавление слоя коэффициента уверенности условия правила в слой условия правила
            rule_condition.append(rule_condition_cf);
            // Обход текущего узла условия правила (слотов фактов)
            $.each(condition_node['nodes'], function(id, slot_node) {
                // Формирование слоя слота факта
                var fact_slot = $('<div>').attr('id', 'fact-slot-' + slot_node['id']).
                    addClass('fact-slot').text(slot_node['text']);
                if (slot_node['value'])
                    fact_slot.append(" = \"" + slot_node['value'] + "\"");
                // Отметка первого слота факта
                if (id == 0)
                    fact_slot.addClass('first-slot');
                // Добавление слоя слота факта к слою условия правила
                rule_condition.append(fact_slot);
            });
            // Добавление слоя условия правила на слой рабочей области редактора
            $('#production-model').append(rule_condition);
            // Добавление возможности перетаскивания слоя условия правила
            instance.draggable(rule_condition, { containment: '#production-model' });
            // Формирование точек связывания
            var endpoint = [];
            if (condition_node['operator'] == 'NOT')
                endpoint = [
                    [ "Dot", { radius: 6, cssClass: "end-point" } ],
                    [ "Dot", { radius: 1, cssClass: "end-point" } ]
                ];
            // Добавление связи между условием правила и правилом
            var current_connection = instance.connect({
                source: rule_condition,
                target: rule,
                anchors: [
                    [ "Perimeter", { shape: "Rectangle" } ],
                    [ "Perimeter", { shape: "Rectangle" } ]
                ],
                connector: [ "Straight" ],
                endpoints: endpoint,
                endpointStyles: [
                    { fillStyle: "#faf4b8", strokeStyle: "#800000" },
                    { fillStyle: "#800000" }
                ],
                endpointHoverStyle: { fillStyle: "#428bca" },
                overlays : [
                    [ "Arrow", {
                        location: 1,
                        id: "arrow",
                        length: 14,
                        foldback: 0.8,
                        cssClass: "arrow"
                    } ]
                ]
            });
        }
    });

    // Позиционирование всех условий правила на рабочей области редактора
    left = 50;
    top = 50;
    condition_top = 50;
    $(".rule-condition").each(function(i) {
        if ((left + this.offsetWidth) > production_model.offsetWidth) {
            left = 50;
            condition_top = top + 100;
            top = top + 100;
        }
        $(this).css({
            left: left,
            top: condition_top
        });
        left = left + this.offsetWidth + 30;
        if (top < this.offsetHeight)
            top = this.offsetHeight;
        // Обновление формы редактора
        instance.repaintEverything();
    });
    // Если слой оператора условий правила существует
    if (rule_operator != null) {
        // Позиционирование слоя оператора условий правила на рабочей области редактора
        $(".operator").each(function(i) {
            $(this).css({
                left: 50,
                top: top + 100
            });
            // Обновление формы редактора
            instance.repaintEverything();
        });
        top = top + 100;
    }
    // Позиционирование узла правила на рабочей области редактора
    $(".rule").each(function(i) {
        $(this).css({
            left: 50,
            top: top + 100
        });
        // Обновление формы редактора
        instance.repaintEverything();
    });

    // Обход текущего узла факта (действия)
    $.each(item['nodes'][1]['nodes'], function(id, action_node) {
        // Формирование слоя коэффициента уверенности действия правила
        var certainty_factor = '-';
        if (action_node['certaintyFactor'])
            certainty_factor = action_node['certaintyFactor'];
        var rule_action_cf = $('<div>').attr('id', 'rule-action-cf-' + item['id']).
            addClass('rule-action-cf').text(certainty_factor);
        // Формирование слоя действия правила
        var rule_action = $('<div>').attr('id', 'rule-action-' + action_node['ruleActionId']).
            addClass('rule-action').text(action_node['text']);
        // Добавление слоя коэффициента уверенности действия правила в слой действия правила
        rule_action.append(rule_action_cf);
        // Обход текущего узла действия правила (слотов фактов)
        $.each(action_node['nodes'], function(id, slot_node) {
            // Формирование слоя слота факта
            var fact_slot = $('<div>').attr('id', 'fact-slot-' + slot_node['id']).
                addClass('fact-slot').text(slot_node['text']);
            if (slot_node['value'])
                fact_slot.append(" = \"" + slot_node['value'] + "\"");
            // Отметка первого слота факта
            if (id == 0)
                fact_slot.addClass('first-slot');
            // Добавление слоя слота факта к слою действия правила
            rule_action.append(fact_slot);
        });
        // Добавление слоя действия правила на слой рабочей области редактора
        $('#production-model').append(rule_action);
        // Добавление возможности перетаскивания слоя действия правила
        instance.draggable(rule_action, { containment: '#production-model' });
        // Добавление связи между действием правила и правилом
        var current_connection = instance.connect({
            source: rule,
            target: rule_action,
            anchors: [
                [ "Perimeter", { shape: "Rectangle" } ],
                [ "Perimeter", { shape: "Rectangle" } ]
            ],
            connector: [ "Straight" ],
            endpointHoverStyle: { fillStyle: "#428bca" },
            overlays : [
                [ "Arrow", {
                    location: 1,
                    id: "arrow",
                    length: 14,
                    foldback: 0.8,
                    cssClass: "arrow"
                } ],
                [ "Label", {
                    id: "label",
                    cssClass: action_node['function'] + "Label"
                } ]
            ]
        });
        // Добавление слоя наименования связи, если функция добавления (assert или none)
        if (action_node['function'] == 'none' || action_node['function'] == 'assert')
            current_connection.getOverlay("label").setLabel('+');
        // Добавление слоя наименования связи, если функция модификации (modify)
        if (action_node['function'] == 'modify')
            current_connection.getOverlay("label").setLabel('~');
        // Добавление слоя наименования связи, если функция удаления (retract)
        if (action_node['function'] == 'retract')
            current_connection.getOverlay("label").setLabel('-');
        // Добавление слоя наименования связи, если функция копирования (duplicate)
        if (action_node['function'] == 'duplicate')
            current_connection.getOverlay("label").setLabel('&#215;');
    });
    // Позиционирование всех действий правила на рабочей области редактора
    left = 50;
    condition_top = top + 200;
    $(".rule-action").each(function(i) {
        if ((left + this.offsetWidth) > production_model.offsetWidth) {
            left = 50;
            condition_top = top + 300;
            top = top + 100;
        }
        $(this).css({
            left: left,
            top: condition_top
        });
        left = left + this.offsetWidth + 30;
        if (top < this.offsetHeight)
            top = this.offsetHeight;
        // Обновление формы редактора
        instance.repaintEverything();
    });
}