<?php

/* @var $this yii\web\View */
/* @var $model app\modules\software_component\models\Metamodel */
/* @var $metaclasses app\modules\software_component\models\Metaclass */
/* @var $metaattributes app\modules\software_component\models\Metaattribute */
/* @var $metarelations app\modules\software_component\models\Metarelation */
/* @var $metareferences app\modules\software_component\models\Metareference */
/* @var $default_metamodel app\modules\software_component\controllers\MetamodelsController */
/* @var $metarelation app\modules\software_component\models\Metarelation */
/* @var $metareference app\modules\software_component\models\Metareference */

use yii\helpers\Html;
use yii\bootstrap\Alert;
use yii\bootstrap\Button;
use yii\bootstrap\ButtonDropdown;
use app\modules\main\models\Lang;
use app\modules\software_component\models\Metamodel;

$this->title = Yii::t('app', 'METAMODELS_PAGE_METAMODEL_EDITOR');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'METAMODELS_PAGE_METAMODELS'),
    'url' => ['list']];
$this->params['breadcrumbs'][] = ['label' => $model->name,
    'url' => ['/metamodels/view/' . $model->id]];
$this->params['breadcrumbs'][] = $this->title;
?>

<?= $this->render('_modal_form_metamodel_editor', [
    'model' => $model,
    'metarelation' => $metarelation,
    'metareference' => $metareference,
]) ?>

<!-- Подключение стилей для редактора метамодели -->
<?php $this->registerCssFile('/css/jsPlumb-styles.css', ['position' => yii\web\View::POS_HEAD]) ?>
<!-- Подключение библиотеки jsPlumb 2.1.2 -->
<?php $this->registerJsFile('/js/jsPlumb-2.1.2.js', ['position' => yii\web\View::POS_HEAD]) ?>
<!-- Подключение скрипта для модальных форм -->
<?php $this->registerJsFile('/js/modal-form.js', ['position' => yii\web\View::POS_HEAD]) ?>

<script type="text/javascript">
    // Графическая сцена jsPlumb
    var instance;
    // Текущее значение id метаотношения
    var current_metarelation_id = 0;
    // Текущее значение id метассылки
    var current_metareference_id =0;
    // Текущее значение id левого элемента связи (метаатрибута)
    var current_left_attribute_id = 0;
    // Текущее значение id правого элемента связи (метаатрибута)
    var current_right_attribute_id = 0;
    // Текущая связь между элементами
    var current_connection;

    // Отрисовка элементов метамодели
    jsPlumb.ready(function () {
        // Слой на котором отрисовывается метамодель
        var metamodel = $("#metamodel");
        // Наименование связи между метаклассами
        var relation_name = '';

        // Настроика некоторых значении для jsPlumb по умолчанию
        var instance = jsPlumb.getInstance({
            Endpoint : ["Dot", { radius: 2, cssClass: "end-point" }],
            HoverPaintStyle : { strokeStyle: "#1e8151", lineWidth: 2 },
            ConnectionsDetachable: false,
            ConnectionOverlays : [
                [ "Arrow", {
                    location: 1,
                    id: "arrow",
                    length: 14,
                    foldback: 0.8,
                    cssClass: "arrow"
                } ],
                [ "Label", {
                    label: "<?php echo Yii::t('app', 'METAMODEL_EDITOR_PAGE_NEW_CONNECTION') ?>",
                    id: "label",
                    cssClass: "aLabel"
                }]
            ],
            PaintStyle : {
                strokeStyle: "#00ff00",
                lineWidth: 2,
                outlineColor: "transparent",
                outlineWidth: 4
            },
            Container: "metamodel"
        });

        var windows = jsPlumb.getSelector("#metamodel .concept");

        // Инициализация перетаскивания элементов
        instance.draggable(windows, { containment: "#metamodel" });

        // Обработка нажатия левой кнопки мыши на связи между элементами
        instance.bind("click", function(connection) {
            // Если цвет связи между понятиями зеленый (это связь по идентификатору)
            if (connection.getPaintStyle().strokeStyle == '#00ff00') {
                // Запоминаем id текущих метаатрибутов участвующих в данной связи
                var left_attribute_id = connection.sourceId.replace(/attribute-/g, '');
                var right_attribute_id = connection.targetId.replace(/attribute-/g, '');
                // Ajax-запрос на получение текущих значений атрибута
                $.ajax({
                    type: "POST",
                    url: "<?= Yii::$app->request->baseUrl . '/' . Lang::getCurrent()->url .
                        '/metamodels/get-relation-values' ?>",
                    data: "YII_CSRF_TOKEN=<?= Yii::$app->request->csrfToken ?>&left_attribute_id=" +
                        left_attribute_id + "&right_attribute_id=" + right_attribute_id,
                    dataType: "json",
                    success: function(data) {
                        // Подстановка текущих значений метаописания и метассылки в поля ввода
                        document.forms["edit-relation-model-form"].elements["Metarelation[name]"].value = data['name'];
                        document.forms["edit-relation-model-form"].elements["Metarelation[left_metaclass]"].value =
                            data['left_metaclass_id'];
                        document.forms["edit-relation-model-form"].elements["Metarelation[right_metaclass]"].value =
                            data['right_metaclass_id'];
                        // Массив всех метаатрибутов левого метакласса связи
                        var left_metaattributes_array = [];
                        // Массив всех метаатрибутов правого метакласса связи
                        var right_metaattributes_array = [];
                        // Обход метаатрибутов всех метаклассов
                        <?php foreach($metaattributes as $attribute): ?>
                            // Формирование массива всех метаатрибутов левого метакласса связи
                            if (data['left_metaclass_id'] == "<?= $attribute['metaclass']; ?>")
                                left_metaattributes_array.push(["<?= $attribute['id'] ?>",
                                    "<?= $attribute['name'] ?>"]);
                            // Формирование массива всех метаатрибутов правого метакласса связи
                            if (data['right_metaclass_id'] == "<?= $attribute['metaclass']; ?>")
                                right_metaattributes_array.push(["<?= $attribute['id'] ?>",
                                    "<?= $attribute['name'] ?>"]);
                        <?php endforeach; ?>
                        // Cпискок метаатрибутов левого метакласса
                        var left_select = document.forms["edit-relation-model-form"].
                            elements["Metareference[left_metaattribute]"];
                        // Очистка списка метаатрибутов левого метакласса
                        for (var i = left_select.options.length - 1 ; i >= 0 ; i--)
                            left_select.remove(i);
                        // Обход массива метаатрибутов левого метакласса связи
                        $.each(left_metaattributes_array, function(i, el) {
                            // Добавление элемента массива (левого метаатрибута) в откидной список
                            var option = document.createElement("option");
                            option.value = el[0];
                            option.innerHTML = el[1];
                            if (el[0] == data['left_metaattribute_id'])
                                option.selected = true;
                            left_select.appendChild(option);
                        });
                        // Список метаатрибутов правого метакласса
                        var right_select = document.forms["edit-relation-model-form"].
                            elements["Metareference[right_metaattribute]"];
                        // Очистка списка метаатрибутов правого метакласса
                        for (var j = right_select.options.length - 1 ; j >= 0 ; j--)
                            right_select.remove(j);
                        // Обход массива метаатрибутов правого метакласса связи
                        $.each(right_metaattributes_array, function(i, el) {
                            // Добавление элемента массива (правого метаатрибута) в откидной список
                            var option = document.createElement("option");
                            option.value = el[0];
                            option.innerHTML = el[1];
                            if (el[0] == data['right_metaattribute_id'])
                                option.selected = true;
                            right_select.appendChild(option);
                        });
                        // Запоминаем id текущего метаотношения и метассылки
                        current_metarelation_id = data['metarelation_id'];
                        current_metareference_id = data['metareference_id'];
                        // Запоминаем данную связь
                        current_connection = connection;
                        // Скрытие списка ошибок ввода
                        $(".error-summary").hide();
                        // Вызов модального окна редактирования связи между понятиями
                        $("#editRelationModalForm").modal("show");
                    },
                    error: function() {
                        alert("Error!");
                    }
                });
                return false;
            }
        });

        // Обработка нажатия правой кнопки мыши на связи между элементами
        instance.bind("contextmenu", function(connection, originalEvent) {
            originalEvent.preventDefault();
            // Если цвет связи между понятиями зеленый (это связь по идентификатору)
            if (connection.getPaintStyle().strokeStyle == '#00ff00') {
                // Запоминаем id текущих метаатрибутов участвующих в данной связи
                current_left_attribute_id = connection.sourceId;
                current_right_attribute_id = connection.targetId;
                // Запоминаем данную связь
                current_connection = connection;
                // Вызов модального окна удаления связи между понятиями
                $("#deleteRelationModalForm").modal("show");
            }
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
            var not_attributes = false;
            // Левый метакласс участвующий в связи
            var left_metaclass = document.getElementById(params.sourceId);
            // Правый метакласс участвующий в связи
            var right_metaclass = document.getElementById(params.targetId);
            // Поиск метаатрибутов у левого метакласса
            var left_attributes = left_metaclass.getElementsByClassName("attribute");
            // Поиск метаатрибутов у правого метакласса
            var right_attributes = right_metaclass.getElementsByClassName("attribute");
            // Если у данных метаклассов нет метаатрибутов
            if (left_attributes.length == 0 || right_attributes.length == 0)
                not_attributes = true;
            // Если у метаклассов участвующих в связи есть метаатрибуты
            if(not_attributes == false) {
                // Идентификатор левого метакласса
                var left_metaclass_id = params.sourceId.replace(/class-/g, '');
                // Идентификатор правого метакласса
                var right_metaclass_id = params.targetId.replace(/class-/g, '');
                // Присваивание значений id левого и правого метакласса
                $("#metarelation-left_metaclass").val(left_metaclass_id);
                $("#metarelation-right_metaclass").val(right_metaclass_id);
                // Массив всех метаатрибутов левого метакласса связи
                var left_metaattributes_array = [];
                // Массив всех метаатрибутов правого метакласса связи
                var right_metaattributes_array = [];
                // Обход метаатрибутов всех метаклассов
                <?php foreach($metaattributes as $attribute): ?>
                    // Формирование массива всех метаатрибутов левого метакласса связи
                    if (left_metaclass_id == "<?= $attribute['metaclass']; ?>")
                        left_metaattributes_array.push(["<?= $attribute['id'] ?>", "<?= $attribute['name'] ?>"]);
                    // Формирование массива всех метаатрибутов правого метакласса связи
                    if (right_metaclass_id == "<?= $attribute['metaclass']; ?>")
                        right_metaattributes_array.push(["<?= $attribute['id'] ?>", "<?= $attribute['name'] ?>"]);
                <?php endforeach; ?>
                // Обход массива метаатрибутов левого метакласса связи
                $('#metareference-left_metaattribute').empty();
                $.each(left_metaattributes_array, function(i, el) {
                    // Добавление элемента массива (метаатрибута) в откидной список
                    $('#metareference-left_metaattribute').append($('<option></option>').val(el[0]).html(el[1]));
                });
                // Обход массива метаатрибутов правого метакласса связи
                $('#metareference-right_metaattribute').empty();
                $.each(right_metaattributes_array, function(i, el) {
                    // Добавление элемента массива (метаатрибута) в откидной список
                    $('#metareference-right_metaattribute').append($('<option></option>').val(el[0]).html(el[1]));
                });
                // Скрытие списка ошибок ввода
                $(".error-summary").hide();
                // Вызов модального окна добавления новой связи между понятиями
                $("#addRelationModalForm").modal("show");
            }
            else {
                // Формирование текста с сообщением
                document.getElementById("message-text").lastChild.nodeValue =
                    "<?= Yii::t('app', 'METAMODEL_EDITOR_PAGE_NOT_EXISTING_METAATTRIBUTES_TEXT') ?>";
                // Вызов модального окна с сообщением
                $("#viewMessageModalForm").modal("show");
            }
            // Не позволяем добавление связи
            return false;
        });

        // suspend drawing and initialise.
        instance.batch(function () {
            instance.makeSource(windows, {
                filter: ".join",
                anchor: "Continuous",
                connector: [ "StateMachine", { curviness: 20 } ]
            });

            // initialise all '.concept' elements as connection targets.
            instance.makeTarget(windows, {
                dropOptions: { hoverClass: "dragHover" },
                anchor: "Continuous",
                allowLoopback: false // Нельзя создать кольцевую связь
            });

            // Первоначальное установление связи между метаклассами
            <?php foreach($metarelations as $relation): ?>
                <?php foreach($metaclasses as $metaclass): ?>
                    <?php if($relation['type'] == 0): ?>
                        <?php if($relation['left_metaclass'] == $metaclass['id']): ?>
                            relation_name = "<?= $relation['name'] ?>";
                            instance.connect({
                                source: "class-<?= $relation['left_metaclass'] ?>",
                                target: "class-<?= $relation['right_metaclass'] ?>",
                                paintStyle: {
                                    strokeStyle: "#5c96bc",
                                    lineWidth: 2,
                                    outlineColor: "transparent",
                                    outlineWidth: 4
                                }
                            });
                        <?php endif; ?>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endforeach; ?>

            // Первоначальное установление связи между метаатрибутами
            <?php foreach($metareferences as $metareference): ?>
                <?php foreach($metarelations as $relation): ?>
                    <?php if($metareference['metarelation'] == $relation['id']): ?>
                        relation_name = "<?= $relation['name'] ?>";
                        instance.connect({
                            source: "attribute-<?= $metareference['left_metaattribute'] ?>",
                            target: "attribute-<?= $metareference['right_metaattribute'] ?>",
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
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endforeach; ?>

            // Затираем наименование связи после добавления
            relation_name = '';
        });

        // Сохраняем графическую сцену jsPlumb в глобальную переменную
        window.instance = instance;
    });

    // Выполнение скрипта при загрузке страницы
    $(document).ready(function() {
        // Равномерное размещение всех метаклассов на рабочей области редактора
        $(".concept").each(function(i) {
            var left = $(this).offset().left;
            var top = $(this).offset().top;
            $(this).css({
                left: left - 250,
                top: top - 300
            });
        });
        // Удаление класса инициализации рабочей области
        $("#metamodel.initial").removeClass("initial");

        // Изменение высоты рабочей области редактора метамодели в зависимости от его содержимого
        var scroll_height = document.getElementById("metamodel").scrollHeight;
        var container = document.getElementById("metamodel");
        container.style.height = scroll_height + "px";
    });
</script>

<div class="metamodel-editor">

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
                                Yii::t('app', 'BUTTON_VIEW_ALL_METAMODELS'),
                            'url' => ['/metamodels/list']
                        ],
                        [
                            'label' => '<span class="glyphicon glyphicon-eye-open"></span> ' .
                                Yii::t('app', 'BUTTON_VIEW_METAMODEL'),
                            'url' => ['/metamodels/view/' . $model->id]
                        ],
                        [
                            'label' => '<span class="glyphicon glyphicon-pencil"></span> ' .
                                Yii::t('app', 'BUTTON_UPDATE_METAMODEL'),
                            'url' => ['/metamodels/update/' . $model->id]
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
                    'data-toggle'=>'modal',
                    'data-target'=>'#removeMetamodelModalForm'
                ]
            ]);
        ?>
    </div>

    <!-- Если метамодель уже сформирована (есть метаклассы) -->
    <?php if(!empty($metaclasses)): ?>
        <!-- Определение рабочей области редактора -->
        <div id="metamodel" class="initial">
            <!-- Обход всех метаклассов -->
            <?php foreach($metaclasses as $metaclass): ?>
                <!-- Определение слоя метакласса -->
                <div class="concept" id="class-<?= $metaclass['id'] ?>" title="<?= $metaclass['name'] ?>">
                    <?= $metaclass['name'] ?>
                    <!-- Если тип метамодели - по умолчанию (не пользовательская) -->
                    <?php if(!$default_metamodel): ?>
                        <!-- Определение слоя для связи -->
                        <div class="join" title="<?= Yii::t('app', 'METAMODEL_EDITOR_PAGE_HINT') ?>"></div>
                    <?php endif; ?>
                    <!-- Обход всех метаатрибутов у данного метакласса -->
                    <?php foreach($metaattributes as $attribute): ?>
                        <?php if($attribute['metaclass'] == $metaclass['id']): ?>
                            <!-- Определение слоя метаатрибута -->
                            <div class="attribute" id="attribute-<?= $attribute['id'] ?>" title="<?= $attribute['name'] ?>">
                                <?= $attribute['name'] ?>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <!-- Вывод предупредительного сообщения -->
        <?php echo Alert::widget([
            'options' => ['class' => 'alert-warning'],
            'body' => '<b>' . Yii::t('app', 'NOTICE_TITLE') . '</b> ' . Yii::t('app', 'NOTICE_TEXT')
        ]); ?>
        <div class="well">
            <?= Yii::t('app', 'METAMODEL_MODEL_MESSAGE_NOT_EXIST_CLASSES') ?>
        </div>
    <?php endif; ?>
</div>