<?php

/* @var $this yii\web\View */
/* @var $model app\modules\knowledge_base\models\KnowledgeBase */
/* @var $data_types app\modules\knowledge_base\models\DataType */
/* @var $classes app\modules\knowledge_base\models\OntologyClass */
/* @var $objects app\modules\knowledge_base\models\Object */
/* @var $relationships app\modules\knowledge_base\models\Relationship */
/* @var $properties app\modules\knowledge_base\models\Property */
/* @var $property_values app\modules\knowledge_base\models\PropertyValue */
/* @var $properties app\modules\knowledge_base\models\Property */
/* @var $left_hand_sides app\modules\knowledge_base\models\LeftHandSide */
/* @var $right_hand_sides app\modules\knowledge_base\models\RightHandSide */

use yii\bootstrap\Html;
use yii\bootstrap\Alert;

$this->title = Yii::t('app', 'ONTOLOGY_EDITOR_PAGE_ONTOLOGY_EDITOR');

$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'KNOWLEDGE_BASES_PAGE_KNOWLEDGE_BASES'),
    'url' => ['/knowledge-bases/list/']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'KNOWLEDGE_BASES_PAGE_KNOWLEDGE_BASE') . ' - ' . $model->name,
    'url' => ['/knowledge-bases/view/' . $model->id]];
$this->params['breadcrumbs'][] = $this->title;
?>

<!-- Подключение стилей для редактора онтологии -->
<?php $this->registerCssFile('/css/ontology-editor-styles.css', ['position' => yii\web\View::POS_HEAD]) ?>
<!-- Подключение библиотеки jsPlumb 2.1.2 -->
<?php $this->registerJsFile('/js/jsPlumb-2.1.2.js', ['position' => yii\web\View::POS_HEAD]) ?>

<script type="text/javascript">
    // Графическая сцена jsPlumb
    var instance;
    // Текущая связь между элементами
    var current_connection;

    // Отрисовка элементов модели онтологии
    jsPlumb.ready(function () {
        // Слой отрисовки модели онтологии
        var ontology_model = $("#ontology-model");
        // Наименование связи между классами
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
                    label: "<?php echo Yii::t('app', 'ONTOLOGY_EDITOR_PAGE_NEW_CONNECTION') ?>",
                    id: "label",
                    cssClass: "aLabel"
                }]
            ],
            PaintStyle : {
                strokeStyle: "#606060",
                lineWidth: 2,
                outlineColor: "transparent",
                outlineWidth: 4
            },
            Container: "ontology-model"
        });

        var windows = jsPlumb.getSelector("#ontology-model .concept");

        // Инициализация перетаскивания элементов
        instance.draggable(windows, { containment: "#ontology-model" });

        // Обработка нажатия левой кнопки мыши на связи между элементами
        instance.bind("click", function(connection) {
            //
            console.log('left click');
        });

        // Обработка нажатия правой кнопки мыши на связи между элементами
        instance.bind("contextmenu", function(connection, originalEvent) {
            originalEvent.preventDefault();
            //
            console.log('right click');
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

            // Первоначальное установление связи между классами
            var connection_color = "#5c96bc";
            <?php foreach($relationships as $relationship): ?>
                <?php foreach($left_hand_sides as $left_hand_side): ?>
                    <?php if($left_hand_side['relationship'] == $relationship['id']): ?>
                        <?php foreach($right_hand_sides as $right_hand_side): ?>
                            <?php if($right_hand_side['relationship'] == $relationship['id']): ?>
                                <?php if($relationship['is_association']): ?>
                                    connection_color = "#5c96bc";
                                <?php endif; ?>
                                <?php if($relationship['is_inheritance']): ?>
                                    connection_color = "#00ff00";
                                <?php endif; ?>
                                <?php if($relationship['is_equivalence']): ?>
                                    connection_color = "#ff0000";
                                <?php endif; ?>
                                relation_name = "<?= $relationship['name'] ?>";
                                instance.connect({
                                    source: "class-<?= $left_hand_side['ontology_class'] ?>",
                                    target: "class-<?= $right_hand_side['ontology_class'] ?>",
                                    paintStyle: {
                                        strokeStyle: connection_color,
                                        lineWidth: 2,
                                        outlineColor: "transparent",
                                        outlineWidth: 4
                                    }
                                });
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endforeach; ?>
            //
            <?php foreach($objects as $object): ?>
                <?php foreach($classes as $class): ?>
                    <?php if($object['ontology_class'] == $class['id']): ?>
                        relation_name = "is-a";
                        instance.connect({
                            source: "object-<?= $object['id'] ?>",
                            target: "class-<?= $class['id'] ?>",
                            paintStyle: {
                                strokeStyle: "#800080",
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

        // Сохранение графической сцены jsPlumb в глобальную переменную
        window.instance = instance;
    });

    // Выполнение скрипта при загрузке страницы
    $(document).ready(function() {
        // Равномерное размещение всех классов на рабочей области редактора
        $(".concept").each(function(i) {
            var left = $(this).offset().left;
            var top = $(this).offset().top;
            $(this).css({
                left: left - 250,
                top: top - 200
            });
        });
        // Удаление класса инициализации рабочей области
        $("#ontology-model.initial").removeClass("initial");

        // Изменение высоты рабочей области редактора онтологии в зависимости от его содержимого
        var scroll_height = document.getElementById("ontology-model").scrollHeight;
        var container = document.getElementById("ontology-model");
        container.style.height = scroll_height + "px";
    });
</script>

<div id="ontology-editor">

    <h1><?= Html::encode($this->title) ?></h1>

    <!-- Если модель онтологии уже сформирована (есть классы) -->
    <?php if(!empty($classes)): ?>
        <!-- Определение рабочей области редактора -->
        <div id="ontology-model" class="initial">
            <!-- Обход всех классов -->
            <?php foreach($classes as $class): ?>
                <!-- Определение слоя класса -->
                <div class="concept" id="class-<?= $class['id'] ?>" title="<?= $class['name'] ?>">
                    <?= $class['name'] ?>
                    <!-- Определение слоя для связи -->
                    <div class="join" title="<?= Yii::t('app', 'ONTOLOGY_EDITOR_PAGE_HINT') ?>"></div>
                    <!-- Обход всех свойств у данного класса -->
                    <?php foreach($properties as $property): ?>
                        <?php if($property['ontology_class'] == $class['id']): ?>
                            <?php foreach($data_types as $data_type): ?>
                                <?php if($property['data_type'] == $data_type['id']): ?>
                                    <!-- Определение слоя свойства -->
                                    <div class="attribute" id="property-<?= $property['id'] ?>"
                                         title="<?= $property['name'] ?>">
                                        <?= $property['name'] ?>: <?= $data_type['name'] ?>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
            <!-- Обход всех объектов -->
            <?php foreach($objects as $object): ?>
                <!-- Определение слоя объекта -->
                <div class="concept" id="object-<?= $object['id'] ?>" title="<?= $object['name'] ?>">
                    <?= $object['name'] ?>
                    <!-- Определение слоя для связи -->
                    <div class="join" title="<?= Yii::t('app', 'ONTOLOGY_EDITOR_PAGE_HINT') ?>"></div>
                    <!-- Обход всех значений свойств -->
                    <?php foreach($property_values as $property_value): ?>
                        <?php if($property_value['object'] == $object['id']): ?>
                            <?php foreach($properties as $property): ?>
                                <?php if($property['id'] == $property_value['property']): ?>
                                    <!-- Определение слоя свойства объекта со значением -->
                                    <div class="attribute" id="property-value-<?= $property_value['id'] ?>"
                                         title="<?= $property['name'] ?>">
                                        <?= $property['name'] ?> = <?= $property_value['name'] ?>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
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
            <?= Yii::t('app', 'ONTOLOGY_MODEL_MESSAGE_NOT_EXIST_CLASSES') ?>
        </div>
    <?php endif; ?>
</div>