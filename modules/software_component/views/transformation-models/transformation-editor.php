<?php

/* @var $this yii\web\View */
/* @var $model app\modules\software_component\models\TransformationModel */
/* @var $software_component app\modules\software_component\models\SoftwareComponent */
/* @var $source_metaclasses app\modules\software_component\controllers\TransformationModelsController */
/* @var $target_metaclasses app\modules\software_component\controllers\TransformationModelsController */
/* @var $metaattributes app\modules\software_component\controllers\TransformationModelsController */
/* @var $metarelations app\modules\software_component\controllers\TransformationModelsController */
/* @var $transformation_rules app\modules\software_component\controllers\TransformationModelsController */
/* @var $transformation_bodies app\modules\software_component\controllers\TransformationModelsController */
/* @var $transformation_rule_model app\modules\software_component\models\TransformationRule */
/* @var $transformation_body_model app\modules\software_component\models\TransformationBody */
/* @var $visible_metaclasses app\modules\software_component\models\MetaclassVisibility */

use yii\helpers\Html;
use yii\bootstrap\Button;
use yii\bootstrap\ButtonDropdown;
use yii\web\JsExpression;
use execut\widget\TreeView;
use app\modules\main\models\Lang;
use app\modules\software_component\models\SoftwareComponent;

$this->title = Yii::t('app', 'TRANSFORMATION_MODELS_PAGE_TRANSFORMATION_EDITOR');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'TRANSFORMATION_MODELS_PAGE_TRANSFORMATION_MODELS'),
    'url' => ['list']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'TRANSFORMATION_MODELS_PAGE_TRANSFORMATION_MODEL') .
    ' - ' . $model->name, 'url' => ['/transformation-models/view/' . $model->id]];
$this->params['breadcrumbs'][] = $this->title;
?>

<?= $this->render('_modal_form_transformation_rules', [
    'model' => $model,
    'software_component' => $software_component,
    'transformation_rule_model'=>$transformation_rule_model,
    'transformation_body_model'=>$transformation_body_model
]) ?>

<!-- Подключение стилей для редактора модели трансформации -->
<?php $this->registerCssFile('/css/jsPlumb-styles.css', ['position' => yii\web\View::POS_HEAD]) ?>
<!-- Подключение библиотеки jsPlumb 2.1.2 -->
<?php $this->registerJsFile('/js/jsPlumb-2.1.2.js', ['position' => yii\web\View::POS_HEAD]) ?>
<!-- Подключение скрипта для модальных форм -->
<?php $this->registerJsFile('/js/modal-form.js', ['position' => yii\web\View::POS_HEAD]) ?>

<!-- Главный скрипт с определением редактора трансформации -->
<script type="text/javascript">
    // Графическая сцена jsPlumb
    var instance;
    // Точка связывания метаклассов
    var class_endpoint;
    // Точка связывания метаатрибутов
    var attribute_endpoint;
    // Текущее значение id левого элемента связи (исходный метакласс соответствия)
    var current_source_class_id = 0;
    // Текущее значение id правого элемента связи (целевой метакласс соответствия)
    var current_target_class_id = 0;
    // Текущее название левого элемента связи (исходный метакласс соответствия)
    var current_source_class_name = '';
    // Текущее название правого элемента связи (целевой метакласс соответствия)
    var current_target_class_name = '';
    // Текущее значение id метаатрибута для исходного метакласса
    var current_source_attribute_id = 0;
    // Текущее значение id метаатрибута для целевого метакласса
    var current_target_attribute_id = 0;
    // Текущая связь между элементами (соответствие между метаклассами)
    var current_connection;
    // Приоритет правила трансформации
    var priority = '';
    // Идентификатор правила трансформации
    var transformation_rule_id = 0;
    // Идентификатор модели трансформации
    var transformation_model_id = '<?= $model->id ?>';
    // URL-адрес страницы проверки чекбокса элемента метамодели (определение видимости метакласса)
    var ajax_url = '<?= Yii::$app->request->baseUrl . '/' . Lang::getCurrent()->url ?>' +
        '/transformation-models/check-class-visibility';
    // CSRF токен
    var csrf_token = 'YII_CSRF_TOKEN=<?= Yii::$app->request->csrfToken ?>';
    // Текст сообщения
    var metaclass_visibility_message_text = '<?= Yii::t('app', 'TRANSFORMATION_EDITOR_PAGE_NOT_VISIBILITY_TEXT') ?>';

    // Отрисовка элементов модели трансформации
    jsPlumb.ready(function () {
        // Создание экземпляра jsPlumb для области модели трансформации и настроика некоторых значении по умолчанию
        var instance = jsPlumb.getInstance({
            DragOptions: { cursor: "pointer", zIndex: 2000 },
            PaintStyle : {
                strokeStyle: "#00f",
                lineWidth: 4,
                outlineColor: "transparent",
                outlineWidth: 4,
                dashstyle: "2 2"
            },
            ConnectionsDetachable: false,
            EndpointHoverStyle: { fillStyle: "orange" },
            HoverPaintStyle : { strokeStyle: "#1e8151" },
            EndpointStyle: { width: 10, height: 10, strokeStyle: "#666666" },
            Endpoint: "Rectangle",
            Container: "transformation-model"
        });

        // Все элементы (метаклассы) рабочей области редактора
        var metaclasses = jsPlumb.getSelector(".concept");
        // Все метаклассы исходной метамодели
        var source_classes = jsPlumb.getSelector(".source-class");
        // Все метаклассы целевой метамодели
        var target_classes = jsPlumb.getSelector(".target-class");
        // Все метаатрибуты исходного метакласса
        var source_attributes = jsPlumb.getSelector(".source-attribute");
        // Все метаатрибуты целевого метакласса
        var target_attributes = jsPlumb.getSelector(".target-attribute");

        // Инициализация перетаскивания элементов (метаклассов) внутри рабочей области редактора
        instance.draggable(metaclasses, { containment: "#transformation-model" });

        // Обработка нажатия левой кнопки мыши на связи между элементами (соответствия между метаклассами)
        instance.bind("click", function(connection) {
            // Если цвет связи между понятиями синий (это соответствия между метаклассами)
            if (connection.getPaintStyle().strokeStyle == '#00f') {
                // Запоминаем данную связь
                current_connection = connection;
                // Запоминаем id текущих метаклассов участвующих в данном соответствии
                var source_class_id = connection.sourceId.replace(/source-class-/g, '');
                var target_class_id = connection.targetId.replace(/target-class-/g, '');
                // Ajax-запрос на получение значений данного соответствия (правила трансформации)
                $.ajax({
                    type: "POST",
                    url: "<?= Yii::$app->request->baseUrl . '/' . Lang::getCurrent()->url .
                        '/transformation-models/get-class-connection-values' ?>",
                    data: "YII_CSRF_TOKEN=<?= Yii::$app->request->csrfToken ?>&transformation_model_id=" +
                        "<?= $model->id ?>&source_class_id=" + source_class_id + "&target_class_id=" + target_class_id,
                    dataType: "json",
                    success: function(data) {
                        // Запоминаем id правила трансформации
                        transformation_rule_id = data['transformation_rule_id'];
                        // Подстановка текущих значений соответствия в поля ввода
                        document.forms["edit-class-connection-model-form"].
                            elements["TransformationRule[source_metaclass]"].value = data['source_metaclass'];
                        document.forms["edit-class-connection-model-form"].
                            elements["TransformationRule[target_metaclass]"].value = data['target_metaclass'];
                        document.forms["edit-class-connection-model-form"].
                            elements["TransformationRule[priority]"].value = data['priority'];
                        // Скрытие списка ошибок ввода
                        $(".error-summary").hide();
                        // Вызов модального окна изменения соответствия между метаклассами
                        $("#editClassConnectionModalForm").modal("show");
                    },
                    error: function() {
                        alert("Error!");
                    }
                });
                return false;
            }
        });

        // Обработка нажатия правой кнопки мыши на связи между элементами (соответствия между метаклассами)
        instance.bind("contextmenu", function(connection, originalEvent) {
            originalEvent.preventDefault();
            // Запоминаем id текущих метаклассов участвующих в данной связи (соответствия)
            current_source_class_id = connection.sourceId;
            current_target_class_id = connection.targetId;
            // Запоминаем данную связь
            current_connection = connection;
            // Если нажата связь между метаклассами, то вызывается модальное окно удаления данного соответствия
            // Иначе удаление соответствия между метаатрибутами
            if (current_source_class_id.indexOf("class") + 1) {
                // Вызов модального окна удаления соответствия между метаклассами
                $('#deleteClassConnectionModalForm').modal('show');
            } else {
                // Запоминаем id текущих метаатрибутов участвующих в данной связи (соответствия)
                current_source_attribute_id = connection.sourceId;
                current_target_attribute_id = connection.targetId;
                // Запоминаем id метаклассов которым принадлежат данные метаатрибуты
                current_source_class_id = $('#' + current_source_attribute_id).parent('.source-class').attr('id');
                current_target_class_id = $('#' + current_target_attribute_id).parent('.target-class').attr('id');
                // Вызов модального окна удаления соответствия между метаатрибутами
                $('#deleteAttributeConnectionModalForm').modal('show');
            }
        });

        // Обработка при связывании элементов
        instance.bind("connection", function(connection) {
            // Отображение точек связывания метаатрибутов для текущего исходного метакласса
            var current_attributes = document.getElementById(connection.sourceId).childNodes;
            [].forEach.call(current_attributes, function(attribute) {
                instance.show("" + attribute.id, true);
            });
            // Отображение точек связывания метаатрибутов для текущего целевого метакласса
            current_attributes = document.getElementById(connection.targetId).childNodes;
            [].forEach.call(current_attributes, function(attribute) {
                instance.show("" + attribute.id, true);
            });
            // Если задан приоритет правила
            if (priority != '') {
                // Присваивание наименованию связи приоритета текущего правила
                connection.connection.getOverlay("label").setLabel(priority);
                // Затирание приоритета
                priority = '';
            }
            // Обновление формы редактора
            instance.repaintEverything();
        });

        // Suspend drawing and initialise
        instance.batch(function () {
            // Расположение точки связывания (якорь) для исходного метакласса
            var source_anchor = [1, 0, 0, 0, 0, 11];
            // Расположение точки связывания (якорь) для целевого метакласса
            var target_anchor = [0, 0, 0, 0, 0, 11];
            // Настройка некоторых параметров для использования всеми точками связывания
            var drop_options = {
                tolerance: "touch",
                hoverClass: "dropHover",
                activeClass: "dragActive"
            };

            // Определение точки связывания элементов (метаклассов)
            class_endpoint = {
                endpoint: "Rectangle",
                paintStyle: { width: 10, height: 10, fillStyle: "#00f" },
                isSource: true,
                reattach: true,
                scope: "blue",
                isTarget: true,
                // Обработка события до установления связи между элементами
                beforeDrop: function (params) {
                    // Запоминаем id текущих слоев метаклассов участвующих в данной связи (соответствии)
                    current_source_class_id = params.sourceId;
                    current_target_class_id = params.targetId;
                    // Запоминаем id текущих метаклассов участвующих в данном соответствии
                    var source_id = current_source_class_id.replace(/source-class-/g, '');
                    var target_id = current_target_class_id.replace(/target-class-/g, '');
                    // Если соответствие устанавливается между метаклассами исходной метамодели
                    if (current_source_class_id.indexOf('source') + 1) {
                        if (current_target_class_id.indexOf('source') + 1) {
                            // Формирование текста с сообщением
                            document.getElementById("message-text").lastChild.nodeValue =
                                "<?= Yii::t('app',
                                'TRANSFORMATION_EDITOR_PAGE_EXISTING_CORRESPONDENCE_BETWEEN_SOURCE_ELEMENTS_TEXT') ?>";
                            // Вызов модального окна с сообщением
                            $("#viewMessageModalForm").modal("show");
                            return false;
                        }
                    }
                    // Нахождение связи между элементами
                    var exist_connection = false;
                    $.each(instance.getAllConnections(), function(id, connection) {
                        if (current_source_class_id == connection.sourceId &&
                            current_target_class_id == connection.targetId)
                            exist_connection = true;
                    });
                    // Невозможно установить соответствие, если исходный и целевой метакласс уже связаны
                    if (exist_connection == false) {
                        // Присваивание значений id исходного и целевого метакласса
                        $("#transformationrule-source_metaclass").val(source_id);
                        $("#transformationrule-target_metaclass").val(target_id);
                        // Скрытие списка ошибок ввода
                        $(".error-summary").hide();
                        // Вызов модального окна добавления нового соответствия между метаклассами
                        $("#addClassConnectionModalForm").modal("show");
                    } else {
                        // Формирование текста с сообщением
                        document.getElementById("message-text").lastChild.nodeValue =
                            "<?= Yii::t('app',
                            'TRANSFORMATION_EDITOR_PAGE_EXISTING_CORRESPONDENCE_BETWEEN_ELEMENTS_TEXT') ?>";
                        // Вызов модального окна с сообщением
                        $("#viewMessageModalForm").modal("show");
                    }
                },
                dropOptions: drop_options
            };

            // Определение точки связывания элементов (метаатрибутов)
            attribute_endpoint = {
                endpoint: ["Dot", { radius: 5 }],
                paintStyle: { fillStyle: "#316b31" },
                isSource: true,
                scope: "green",
                connectorStyle: { strokeStyle: "#316b31", lineWidth: 4 },
                connector: ["Bezier", { curviness: 63 } ],
                isTarget: true,
                // Обработка события до установления связи между элементами (метаатрибутами)
                beforeDrop: function (params) {
                    // Запоминаем id текущих слоев метаатрибутов участвующих в данной связи (соответствии)
                    current_source_attribute_id = params.sourceId;
                    current_target_attribute_id = params.targetId;
                    // Запоминаем id текущих метаатрибутов участвующих в данном соответствии
                    var source_id = current_source_attribute_id.replace(/source-attribute-/g, '');
                    var target_id = current_target_attribute_id.replace(/target-attribute-/g, '');
                    // Если соответствие устанавливается между метаатрибутами исходного метакласса
                    if (current_source_attribute_id.indexOf('source') + 1) {
                        if (current_target_attribute_id.indexOf('source') + 1) {
                            // Формирование текста с сообщением
                            document.getElementById("message-text").lastChild.nodeValue =
                                "<?= Yii::t('app',
                                'TRANSFORMATION_EDITOR_PAGE_EXISTING_CORRESPONDENCE_BETWEEN_SOURCE_ELEMENTS_TEXT') ?>";
                            // Вызов модального окна с сообщением
                            $("#viewMessageModalForm").modal("show");
                            return false;
                        }
                    }
                    // Запоминаем метаклассы текущих слоев метаатрибутов участвующих в данной связи (соответствии)
                    current_source_class_id = document.getElementById(params.sourceId).parentNode.id;
                    current_target_class_id = document.getElementById(params.targetId).parentNode.id;
                    // Нахождение связи между элементами (метаклассами)
                    var exist_classes_connection = false;
                    $.each(instance.getAllConnections(), function(id, connection) {
                        if(current_source_class_id == connection.sourceId &&
                            current_target_class_id == connection.targetId)
                            exist_classes_connection = true;
                    });
                    // Нахождение связи между элементами (метаатрибутами)
                    var exist_attributes_connection = false;
                    $.each(instance.getAllConnections(), function(id, connection) {
                        if (current_source_attribute_id == connection.sourceId &&
                            current_target_attribute_id == connection.targetId)
                            exist_attributes_connection = true;
                    });
                    // Невозможно установить соответствие между метаатрибутами,
                    // если между исходным и целевым метаклассом нет соответствия
                    if (exist_classes_connection) {
                        // Невозможно установить соответствие, если исходный и целевой метаатрибут уже связаны
                        if (exist_attributes_connection == false) {
                            // Присваивание значений id исходного и целевого метакласса
                            $("#transformationbody-source_metaattribute").val(source_id);
                            $("#transformationbody-target_metaattribute").val(target_id);
                            // Вызов модального окна добавления нового соответствия между метаатрибутами
                            $("#addAttributeConnectionModalForm").modal("show");
                        } else {
                            // Формирование текста с сообщением
                            document.getElementById("message-text").lastChild.nodeValue =
                                "<?= Yii::t('app',
                            'TRANSFORMATION_EDITOR_PAGE_EXISTING_CORRESPONDENCE_BETWEEN_ELEMENTS_TEXT') ?>";
                            // Вызов модального окна с сообщением
                            $("#viewMessageModalForm").modal("show");
                        }
                    } else {
                        // Формирование текста с сообщением
                        document.getElementById("message-text").lastChild.nodeValue =
                            "<?= Yii::t('app',
                            'TRANSFORMATION_EDITOR_PAGE_NOT_EXISTING_CORRESPONDENCE_BETWEEN_ELEMENTS_TEXT') ?>";
                        // Вызов модального окна с сообщением
                        $("#viewMessageModalForm").modal("show");
                    }
                },
                dropOptions: drop_options
            };

            // Добавление точек связывания к исходным метаклассам
            instance.addEndpoint(source_classes, { anchor: source_anchor }, class_endpoint);
            // Добавление точек связывания к целевым метаклассам
            instance.addEndpoint(target_classes, { anchor: target_anchor, isSource: false }, class_endpoint);

            // Добавление точек связывания к метаатрибутам исходного метакласса
            instance.addEndpoint(source_attributes, { anchor: source_anchor }, attribute_endpoint);
            // Добавление точек связывания к метаатрибутам целевого метакласса
            instance.addEndpoint(target_attributes, { anchor: target_anchor, isSource: false }, attribute_endpoint);

            // Скрывание всех точек связывания метаатрибутов
            instance.selectEndpoints().each(function(endpoint) {
                var str = "" + endpoint.element.id;
                if (str.indexOf("attribute") + 1) {
                    instance.hide(endpoint.element.id, true);
                }
            });

            // Скрытие всех слоев исходных и целевых метакласса (и их точек связавания) отмеченных как не видимые
            <?php foreach($visible_metaclasses as $visible_metaclass): ?>
                <?php if($visible_metaclass['visibility'] == false): ?>
                    $('#source-class-<?= $visible_metaclass['metaclass'] ?>').hide();
                    instance.hide('source-class-<?= $visible_metaclass['metaclass'] ?>', true);
                    $('#target-class-<?= $visible_metaclass['metaclass'] ?>').hide();
                    instance.hide('target-class-<?= $visible_metaclass['metaclass'] ?>', true);
                <?php endif; ?>
            <?php endforeach; ?>

            // Первоначальное установление соответствий между метаклассами
            <?php foreach($transformation_rules as $transformation_rule): ?>
                priority = "<?=$transformation_rule['priority']?>";
                instance.connect({
                    source: "source-class-<?=$transformation_rule['source_metaclass']?>",
                    target: "target-class-<?=$transformation_rule['target_metaclass']?>",
                    anchors: [source_anchor, target_anchor],
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
                instance.addEndpoint(jsPlumb.getSelector("#source-class-<?=$transformation_rule['source_metaclass']?>"),
                    { anchor: source_anchor }, class_endpoint);
                // Добавление еще одной точки связывания к целевому метаклассу
                instance.addEndpoint(jsPlumb.getSelector("#target-class-<?=$transformation_rule['target_metaclass']?>"),
                    { anchor: target_anchor, isSource: false }, class_endpoint);

                // Первоначальное установление соответствий между метаатрибутами
                <?php foreach($transformation_bodies as $transformation_body): ?>
                    <?php if($transformation_body['transformation_rule'] == $transformation_rule['id']): ?>
                        instance.connect({
                            source: "source-attribute-<?=$transformation_body['source_metaattribute']?>",
                            target: "target-attribute-<?=$transformation_body['target_metaattribute']?>",
                            anchors: [source_anchor, target_anchor],
                            endpoint: ["Dot", { radius: 5 }],
                            endpointStyles:[
                                { fillStyle: "#316b31" },
                                { fillStyle: "#316b31" }
                            ],
                            paintStyle:{ strokeStyle: "#316b31", lineWidth: 4 }
                        });
                        // Добавление еще одной точки связывания к метаатрибуту исходного метакласса
                        instance.addEndpoint(
                            jsPlumb.getSelector("#source-attribute-<?=$transformation_body['source_metaattribute']?>"),
                            { anchor: source_anchor },
                            attribute_endpoint
                        );
                        // Добавление еще одной точки связывания к метаатрибуту целевого метакласса
                        instance.addEndpoint(
                            jsPlumb.getSelector("#target-attribute-<?=$transformation_body['target_metaattribute']?>"),
                            { anchor: target_anchor, isSource: false },
                            attribute_endpoint
                        );
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endforeach; ?>
        });

        // Сохранение графической сцены jsPlumb (модели трансформации) в виде глобальной переменной
        window.instance = instance;
    });

    // Выполнение скрипта при загрузке страницы
    $(document).ready(function() {
        // Текущее значение высоты слоя метакласса
        var current_height = 20;
        // Текущее значение ширины слоя метакласса
        var current_width = 0;
        // Текущее значение высоты рабочей области редактора
        var transformation_model_width = $("#transformation-model").width();
        // Равномерное размещение всех видимых метаклассов исходной метамодели на рабочей области редактора
        $(".source-class.visible-class").each(function(i) {
            $(this).css("left", 20);
            $(this).css("top", current_height);
            current_height = current_height + $(this).height() + 30;
        });
        // Обнуление текущего значения высоты слоя метакласса
        current_height = 20;
        // Равномерное размещение всех видимых метаклассов целевой метамодели на рабочей области редактора
        $(".target-class.visible-class").each(function(i) {
            current_width =  transformation_model_width - $(this).width();
            $(this).css("left", current_width);
            $(this).css("top", current_height);
            current_height = current_height + $(this).height() + 30;
        });
        // Изменение высоты рабочей области редактора модели трансформации в зависимости от его содержимого
        var scroll_height = document.getElementById("transformation-model").scrollHeight;
        var container = document.getElementById("transformation-model");
        container.style.height = scroll_height + 20 + "px";
    });
</script>

<!-- Слой рабочей области редактора модели трансформации -->
<div class="transformation-editor">

    <h1><?= Html::encode($this->title) ?></h1>

    <!-- Определение дополнительных кнопок -->
    <div class="well col-md-12">
        <?php
            echo ButtonDropdown::widget([
                'label' => '<span class="glyphicon glyphicon-share-alt"></span> ' . Yii::t('app', 'BUTTON_RETURN'),
                'encodeLabel' => false,
                'options' => [
                    'class' => 'btn btn-default',
                    'style' => 'margin:5px'
                ],
                'dropdown' => [
                    'encodeLabels' => false,
                    'items' => [
                        [
                            'label' => '<span class="glyphicon glyphicon-list-alt"></span> ' .
                                Yii::t('app', 'BUTTON_VIEW_ALL_TRANSFORMATION_MODELS'),
                            'url' => ['/transformation-models/list']
                        ],
                        [
                            'label' => '<span class="glyphicon glyphicon-eye-open"></span> ' .
                                Yii::t('app', 'BUTTON_VIEW_TRANSFORMATION_MODEL'),
                            'url' => ['/transformation-models/view/' . $model->id]
                        ],
                        [
                            'label' => '<span class="glyphicon glyphicon-pencil"></span> ' .
                                Yii::t('app', 'BUTTON_UPDATE_TRANSFORMATION_MODEL'),
                            'url' => ['/transformation-models/update/' . $model->id]
                        ],
                        [
                            'label' => '<span class="glyphicon glyphicon-file"></span> ' .
                                Yii::t('app', 'BUTTON_VIEW_TRANSFORMATION_MODEL_CODE_TMRL'),
                            'url' => ['/transformation-models/view-tmrl-code/' . $model->id]
                        ]
                    ]
                ]
            ]);
            echo Button::widget([
                'label' => '<span class="glyphicon glyphicon-trash"></span> ' . Yii::t('app', 'BUTTON_DELETE'),
                'encodeLabel' => false,
                'options' => [
                    'class' => 'btn btn-danger',
                    'style' => 'margin:5px',
                    'data-toggle' => 'modal',
                    'data-target' => '#removeTransformationModelModalForm'
                ]
            ]);
            echo Button::widget([
                'label' => ($software_component->status == SoftwareComponent::STATUS_GENERATED ||
                    $software_component->status == SoftwareComponent::STATUS_DESIGN) ?
                    '<span class="glyphicon glyphicon-floppy-save"></span> ' .
                    Yii::t('app', 'BUTTON_GENERATE_SOFTWARE_COMPONENT') :
                    '<span class="glyphicon glyphicon-floppy-save"></span> ' .
                    Yii::t('app', 'BUTTON_REGENERATE_SOFTWARE_COMPONENT'),
                'encodeLabel' => false,
                'options' => [
                    'id' => 'software-component-generation-button',
                    'class' => ($software_component->status == SoftwareComponent::STATUS_GENERATED ||
                        $software_component->status == SoftwareComponent::STATUS_DESIGN) ? 'btn btn-success' :
                        'btn btn-warning',
                    'style' => 'margin:5px',
                    'disabled' => empty($transformation_rules) ? true : false,
                    'data-toggle' => 'modal',
                    'data-target' => '#generateSoftwareComponentModalForm'
                ]
            ]);
        ?>
    </div>

    <!-- Определение дерева метаклассов исходной и целевой метамодели -->
    <div class="col-md-3">
        <?php
            // Массивы хранения элементов исходной и целевой метамодели для дерева элементов метамоделей
            $source_metaclass_array = array();
            $target_metaclass_array = array();
            // Обход всех исходных метаклассов
            foreach ($source_metaclasses as $source_metaclass) {
                $source_metaattribute_array = array();
                foreach ($visible_metaclasses as $visible_metaclass)
                    if ($visible_metaclass['metaclass'] == $source_metaclass['id']) {
                        // Формирование массива метаатрибутов для элементов исходной метамодели
                        foreach ($metaattributes as $metaattribute)
                            if ($metaattribute['metaclass'] == $source_metaclass['id'])
                                array_push($source_metaattribute_array,
                                    [
                                        'id' => $metaattribute['id'],
                                        'text' => $metaattribute['name'],
                                        'icon' => "glyphicon glyphicon-tag",
                                        'selectable' => false,
                                        'state' => ['checked' => $visible_metaclass['visibility']],
                                    ]);
                        // Формирование массива для дерева элементов исходной метамодели
                        array_push($source_metaclass_array,
                            [
                                'id' => $source_metaclass['id'],
                                'text' => $source_metaclass['name'],
                                'selectable' => false,
                                'state' => [
                                    'checked' => $visible_metaclass['visibility'],
                                    'expanded' => empty($source_metaattribute_array) ? true : false
                                ],
                                'nodes' => $source_metaattribute_array
                            ]);
                    }
            }
            // Обход всех целевых метаклассов
            foreach ($target_metaclasses as $target_metaclass) {
                $target_metaattribute_array = array();
                foreach ($visible_metaclasses as $visible_metaclass)
                    if ($visible_metaclass['metaclass'] == $target_metaclass['id']) {
                        // Формирование массива метаатрибутов для элементов целевой метамодели
                        foreach ($metaattributes as $metaattribute)
                            if ($metaattribute['metaclass'] == $target_metaclass['id'])
                                array_push($target_metaattribute_array,
                                    [
                                        'id' => $metaattribute['id'],
                                        'text' => $metaattribute['name'],
                                        'icon' => "glyphicon glyphicon-tag",
                                        'selectable' => false,
                                        'state' => ['checked' => $visible_metaclass['visibility']],
                                    ]);
                        // Формирование массива метаатрибутов для элементов целевой метамодели
                        array_push($target_metaclass_array,
                            [
                                'id' => $target_metaclass['id'],
                                'text' => $target_metaclass['name'],
                                'selectable' => false,
                                'state' => [
                                    'checked' => $visible_metaclass['visibility'],
                                    'expanded' => empty($target_metaattribute_array) ? true : false
                                ],
                                'nodes' => $target_metaattribute_array
                            ]);
                    }
            }
            // Формирование массива дерева элементов метамоделей
            $data = [
                [
                    'text' => Yii::t('app', 'TRANSFORMATION_EDITOR_PAGE_SOURCE_METAMODEL'),
                    'color' => "#428bca",
                    'selectable' => false,
                    'state' => ['checked' => true],
                    'icon' => "glyphicon glyphicon-export",
                    'tags' => [count($source_metaclass_array)],
                    'nodes' => $source_metaclass_array
                ],
                [
                    'text' => Yii::t('app', 'TRANSFORMATION_EDITOR_PAGE_TARGET_METAMODEL'),
                    'color' => "#428bca",
                    'selectable' => false,
                    'state' => ['checked' => true],
                    'icon' => "glyphicon glyphicon-import",
                    'tags' => [count($target_metaclass_array)],
                    'nodes' => $target_metaclass_array
                ]
            ];
            // Обработчик присвоения и снятия флажка с элемента дерева метамоделей
            $onCheck = new JsExpression(<<<JS
                function checkedElement(undefined, item) {
                    // Если выбран метакласс
                    if (item['icon'] != 'glyphicon glyphicon-export' && item['icon'] != 'glyphicon glyphicon-import' &&
                        item['icon'] != 'glyphicon glyphicon-tag') {
                        // Слой исходного метакласса
                        var source_class = $('#source-class-' + item['id']);
                        // Слой целевого метакласса
                        var target_class = $('#target-class-' + item['id']);
                        // Нахождение связей у выбранного метакласса
                        var exist_connection = false;
                        $.each(instance.getAllConnections(), function(id, connection) {
                            if (connection.sourceId == 'source-class-' + item['id'] ||
                                connection.targetId == 'target-class-' + item['id'])
                                exist_connection = true;
                        });
                        // Если у выбранного метакласса нет связей
                        if (exist_connection == false) {
                            // Ajax-запрос на определение видимости данного метакласса
                            $.ajax({
                                type: 'POST',
                                url: ajax_url,
                                data: csrf_token + '&metaclass_id=' + item['id'] +
                                    '&transformation_model_id=' + transformation_model_id,
                                dataType: 'json',
                                success: function(data) {
                                    // Если метакласс видим
                                    if (data['visibility'] == true) {
                                        if (source_class.length) {
                                            // Отображение исходного метакласса на рабочей области редактора
                                            source_class.show();
                                            // Позиционирование слоя исходного метакласса
                                            var current_source_metaclass = document.getElementById('source-class-' +
                                                item['id']).style;
                                            current_source_metaclass.top = '50px';
                                            current_source_metaclass.left = '50px';
                                            // Отображение точки связывания для исходного метакласса
                                            instance.show('source-class-' + item['id'], true);
                                        }
                                        if (target_class.length) {
                                            // Отображение целевого метакласса на рабочей области редактора
                                            target_class.show();
                                            // Позиционирование слоя целевого метакласса
                                            var current_target_metaclass = document.getElementById('target-class-' +
                                                item['id']).style;
                                            current_target_metaclass.top = '50px';
                                            current_target_metaclass.left = '50px';
                                            // Отображение точки связывания для целевого метакласса
                                            instance.show('target-class-' + item['id'], true);
                                        }
                                        // Цикл по метаатрибутам в дереве метамоделей
                                        $.each(item['nodes'], function(id, node) {
                                            // Присвоение флажка узлам метаатрибутов
                                            $('#transformation-model-tree-view').treeview('checkNode',
                                                [node['nodeId'], {silent: true}]);
                                        });
                                    }
                                    else {
                                        if (source_class.length) {
                                            // Скрытие исходного метакласса на рабочей области редактора
                                            source_class.hide();
                                            // Скрытие точки связывания для исходного метакласса
                                            instance.hide('source-class-' + item['id'], true);
                                        }
                                        if (target_class.length) {
                                            // Скрытие целевого метакласса на рабочей области редактора
                                            target_class.hide();
                                            // Скрытие точки связывания для целевого метакласса
                                            instance.hide('target-class-' + item['id'], true);
                                        }
                                        // Цикл по метаатрибутам в дереве метамоделей
                                        $.each(item['nodes'], function(id, node) {
                                            // Снятие флажка с узлов метаатрибутов
                                            $('#transformation-model-tree-view').treeview('uncheckNode',
                                                [node['nodeId'], {silent: true}]);
                                        });
                                    }
                                    // Обновление формы редактора
                                    instance.repaintEverything();
                                },
                                error: function() {
                                    alert("Error!");
                                }
                            });
                        }
                        else {
                            // Формирование текста с сообщением
                            document.getElementById('message-text').lastChild.nodeValue =
                                metaclass_visibility_message_text;
                            // Вызов модального окна с сообщением
                            $('#viewMessageModalForm').modal('show');
                            // Присвоение флажка текущему узлу
                            $('#transformation-model-tree-view').treeview('checkNode',
                                [item['nodeId'], {silent: true}]);
                        }
                    }
                    //
                    if (item['icon'] == 'glyphicon glyphicon-export' || item['icon'] == 'glyphicon glyphicon-import')
                        // Присвоение флажка текущему узлу
                        $('#transformation-model-tree-view').treeview('checkNode', [item['nodeId'], {silent: true}]);
                    //
                    if (item['icon'] == 'glyphicon glyphicon-tag' && item['state']['checked'] == false)
                        // Присвоение флажка текущему узлу
                        $('#transformation-model-tree-view').treeview('checkNode', [item['nodeId'], {silent: true}]);
                    //
                    if (item['icon'] == 'glyphicon glyphicon-tag' && item['state']['checked'] == true)
                        // Снятие флажка у текущего узла
                        $('#transformation-model-tree-view').treeview('uncheckNode', [item['nodeId'], {silent: true}]);
                }
JS
            );
            // Дерево элементов метамоделей
            $groupsContent = TreeView::widget([
                'id' => 'transformation-model-tree-view',
                'data' => $data,
                'size' => TreeView::SIZE_MIDDLE,
                'header' => Yii::t('app', 'TRANSFORMATION_EDITOR_PAGE_ELEMENTS'),
                'searchOptions' => [
                    'inputOptions' => [
                        'placeholder' => Yii::t('app', 'TRANSFORMATION_EDITOR_PAGE_SEARCH'),
                    ],
                ],
                'clientOptions' => [
                    'onNodeChecked' => $onCheck,
                    'onNodeUnchecked' => $onCheck,
                    'selectedBackColor' => '#428bca',
                    'searchResultBackColor' => '#428bca',
                    'searchResultColor' => '#ffffff',
                    'borderColor' => '#ffffff',
                    'showCheckbox' => true,
                    'showBorder' => true,
                    'showTags' => true
                ],
            ]);
            echo $groupsContent;
        ?>
    </div>

    <!-- Определение слоя рабочей области редактора модели трансформации -->
    <div class="col-md-9" id="transformation-model">
        <!-- Обход всех исходных метаклассов -->
        <?php foreach($source_metaclasses as $source_metaclass): ?>
            <?php foreach($visible_metaclasses as $visible_metaclass): ?>
                <?php if($visible_metaclass['metaclass'] == $source_metaclass['id'] &&
                    $visible_metaclass['visibility'] == true): ?>
                    <!-- Определение слоев видимых исходных метаклассов -->
                    <div class="concept source-class visible-class" id="source-class-<?= $source_metaclass['id'] ?>"
                         title="<?= $source_metaclass['name'] ?>">
                        <?= $source_metaclass['name'] ?>
                        <!-- Определение атрибутов для исходного метакласса -->
                        <?php foreach($metaattributes as $attribute): ?>
                            <?php if($attribute['metaclass'] == $source_metaclass['id']): ?>
                                <div class="attribute source-attribute" id="source-attribute-<?= $attribute['id'] ?>"
                                     title="<?= $attribute['name'] ?>">
                                    <?= $attribute['name'] ?>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endforeach; ?>
        <!-- Обход всех исходных метаклассов -->
        <?php foreach($source_metaclasses as $source_metaclass): ?>
            <?php foreach($visible_metaclasses as $visible_metaclass): ?>
                <?php if($visible_metaclass['metaclass'] == $source_metaclass['id'] &&
                    $visible_metaclass['visibility'] == false): ?>
                    <!-- Определение слоев не видимых исходных метаклассов -->
                    <div class="concept source-class hidden-class" id="source-class-<?= $source_metaclass['id'] ?>"
                         title="<?= $source_metaclass['name'] ?>">
                        <?= $source_metaclass['name'] ?>
                        <!-- Определение атрибутов для исходного метакласса -->
                        <?php foreach($metaattributes as $attribute): ?>
                            <?php if($attribute['metaclass'] == $source_metaclass['id']): ?>
                                <div class="attribute source-attribute" id="source-attribute-<?= $attribute['id'] ?>"
                                     title="<?= $attribute['name'] ?>">
                                    <?= $attribute['name'] ?>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endforeach; ?>
        <!-- Обход всех целевых метаклассов -->
        <?php foreach($target_metaclasses as $target_metaclass): ?>
            <?php foreach($visible_metaclasses as $visible_metaclass): ?>
                <?php if($visible_metaclass['metaclass'] == $target_metaclass['id'] &&
                    $visible_metaclass['visibility'] == true): ?>
                    <!-- Определение слоев видимых целевых метаклассов -->
                    <div class="concept target-class visible-class" id="target-class-<?= $target_metaclass['id'] ?>"
                         title="<?= $target_metaclass['name'] ?>">
                        <?= $target_metaclass['name'] ?>
                        <!-- Определение атрибутов для исходного метакласса -->
                        <?php foreach($metaattributes as $attribute): ?>
                            <?php if($attribute['metaclass'] == $target_metaclass['id']): ?>
                                <div class="attribute target-attribute" id="target-attribute-<?= $attribute['id'] ?>"
                                     title="<?= $attribute['name'] ?>">
                                    <?= $attribute['name'] ?>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endforeach; ?>
        <!-- Обход всех целевых метаклассов -->
        <?php foreach($target_metaclasses as $target_metaclass): ?>
            <?php foreach($visible_metaclasses as $visible_metaclass): ?>
                <?php if($visible_metaclass['metaclass'] == $target_metaclass['id'] &&
                    $visible_metaclass['visibility'] == false): ?>
                    <!-- Определение слоев не видимых целевых метаклассов -->
                    <div class="concept target-class hidden-class" id="target-class-<?= $target_metaclass['id'] ?>"
                         title="<?= $target_metaclass['name'] ?>">
                        <?= $target_metaclass['name'] ?>
                        <!-- Определение атрибутов для исходного метакласса -->
                        <?php foreach($metaattributes as $attribute): ?>
                            <?php if($attribute['metaclass'] == $target_metaclass['id']): ?>
                                <div class="attribute target-attribute" id="target-attribute-<?= $attribute['id'] ?>"
                                     title="<?= $attribute['name'] ?>">
                                    <?= $attribute['name'] ?>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </div>
</div>