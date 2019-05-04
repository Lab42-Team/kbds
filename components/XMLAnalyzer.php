<?php

namespace app\components;

use Yii;
use app\modules\software_component\models\Metaclass;
use app\modules\software_component\models\Metaattribute;
use app\modules\software_component\models\Metarelation;
use app\modules\software_component\models\Metareference;
use app\modules\software_component\models\SoftwareComponent;
use app\modules\software_component\models\TransformationModel;
use app\modules\software_component\models\TransformationRule;
use app\modules\software_component\models\TransformationBody;

/**
 * XMLAnalyzer.
 * Класс XMLAnalyzer обеспечивает анализ (обработку) XML-файла концептуальной модели.
 */
class XMLAnalyzer
{
    // Наименование атрибута маркера для отметки текущего анализируемого элемента
    const MARKER = 'KBDS_MARKER';
    // Наименование атрибута идентификатора для проанализированного и созданного элемента
    const ELEMENT_ID = 'KBDS_ELEMENT_ID';
    // Все строки XML-документа концептуальной модели
    public $xml_rows;
    // Пространство имен объявленое в XML-документе концептуальной модели
    public $namespaces;
    // Массив правил трансформации
    // (элементом данного массива является двумерный массив с ключом идентификатора правила трансформации)
    public $transformation_rules = array();
    // Массив тел (атрибутов) правил трансформации
    // (элементом данного массива является двумерный массив, состоящий из идентификатора правила трансформации и
    // двумерного массива с исходным и целевым атрибутам)
    public $transformation_bodies = array();
    // Массив всех извлеченных элементов по одному правилу трансформации
    public $extraction_values = array();
    // Массив текущих извлеченных значений элемента
    public $current_extraction_values = array();
    // Переменная для определения существования наименования элемента
    public $exist_name = false;
    // Переменная для определения текущего номера (индекса) извлекаемого элемента для формирования условий и действий
    public $association_number = 0;
    // Текущее значение id (из БД) связанного элемента
    public $current_element_id = 0;
    // Текущее значение id типа данных
    public $current_data_type_id = 0;
    // Текущее значение id шаблона факта
    public $current_fact_template_id = 0;
    // Текущее значение id слота шаблона факта
    public $current_fact_template_slot_id = 0;
    // Текущее значение id факта
    public $current_fact_id = 0;
    // Текущее значение id слота факта
    public $current_fact_slot_id = 0;
    // Текущее значение id шаблона правила
    public $current_rule_template_id = 0;
    // Текущее значение id правила
    public $current_rule_id = 0;
    // Массив извлеченных условий продукционных правил (id-правила => условие{id-факта})
    public $rule_condition_array = array();
    // Массив извлеченных сложных продукционных правил (действие{id-факта} => [[условие{id-факта}, оператор], ...])
    public $complex_rule_array = array();
    // Текущее значение id класса онтологии
    public $current_class_id = 0;
    // Текущее значение id объекта онтологии
    public $current_object_id = 0;
    // Текущее значение id отношения онтологии
    public $current_relationship_id = 0;
    // Текущее значение id свойства класса
    public $current_property_id = 0;
    //
    public $optional_array = array();
    //
    public $cmap_topic_array = array();
    //
    public $cmap_topic_attribute_array = array();

    /**
     * Создание массива правил трансформации и их тел (атрибутов).
     * @param $id - идентификатор программного компонента
     */
    public function createTransformationArrays($id)
    {
        // Получение программного компонента по его идентификатору
        $software_component = SoftwareComponent::findOne($id);
        // Поиск модели трансформации связанной с данным программным компонентом
        $transformation_model = TransformationModel::find()
            ->where(array('software_component' => $software_component->id))
            ->one();
        // Поиск всех правил трансформации входящих в данную модель трансформации (сортировка по приоритету правила)
        $transformation_rules = TransformationRule::find()
            ->where(array('transformation_model' => $transformation_model->id))
            ->orderBy(['priority' => SORT_ASC])
            ->asArray()
            ->all();
        // Цикл по всем правилам трансформации входящих в модель трансформации
        $previous_priority = null;
        $current_id = 0;
        foreach ($transformation_rules as $transformation_rule) {
            // Если текущий приоритет правила совпадает с предыдущим
            if ($previous_priority != $transformation_rule['priority']) {
                // Массив исходных и целевых метаклассов участвующих в правилах трансформации
                $metaclasses = array();
                // Формирование подмассива исходных метаклассов
                $list = array();
                array_push($list, Metaclass::findOne($transformation_rule['source_metaclass'])->name);
                // Формирование массива исходных метаклассов участвующих в правилах трансформации
                array_push($metaclasses, $list);
                // Формирование массива целевых метаклассов участвующих в правилах трансформации
                array_push($metaclasses, Metaclass::findOne($transformation_rule['target_metaclass'])->name);
                // Формирование массива правил трансформации
                $this->transformation_rules[$transformation_rule['id']] = $metaclasses;
                // Запоминание текущего добавленного id правила трансформации
                $current_id = $transformation_rule['id'];
            } else
                array_push($this->transformation_rules[$current_id][0],
                    Metaclass::findOne($transformation_rule['source_metaclass'])->name);
            // Поиск всех атрибутов участвующих в данном правиле трансформации
            $transformation_bodies = TransformationBody::find()
                ->where(array('transformation_rule' => $transformation_rule['id']))
                ->asArray()
                ->all();
            // Цикл по всем телам правил трансформации
            foreach ($transformation_bodies as $transformation_body) {
                // Массив исходных и целевых метаатрибутов участвующих в правилах трансформации
                $metaattributes = array();
                // Формирование массива исходных метаатрибутов участвующих в правилах трансформации
                array_push($metaattributes, Metaattribute::findOne($transformation_body['source_metaattribute'])->name);
                // Формирование массива целевых метаатрибутов участвующих в правилах трансформации
                array_push($metaattributes, Metaattribute::findOne($transformation_body['target_metaattribute'])->name);
                // Если текущий приоритет правила совпадает с предыдущим
                if ($previous_priority != $transformation_rule['priority'])
                    // Формирование массива тел (атрибутов) правил трансформации
                    array_push($this->transformation_bodies, [$transformation_rule['id'], $metaattributes]);
                else {
                    $exist_metaattributes = false;
                    // Обход по всем текущим сформированным телам правил трансформации
                    foreach ($this->transformation_bodies as $current_transformation_body)
                        // Нахождение тел для текущего правила трансформации
                        if ($current_transformation_body[0] == $current_id)
                            // Если такая пара метаатрибутов уже добавлена в массив
                            if ($current_transformation_body[1][0] == $metaattributes[0] &&
                                $current_transformation_body[1][1] == $metaattributes[1])
                                $exist_metaattributes = true;
                    // Если такая пара метаатрибутов не добавлена в массив
                    if ($exist_metaattributes == false)
                        // Формирование массива тел (атрибутов) правил трансформации
                        array_push($this->transformation_bodies, [$current_id, $metaattributes]);
                }
            }
            // Обновление предыдущего приоритета правила трансформации
            $previous_priority = $transformation_rule['priority'];
        }
    }

    /**
     * Поиск элемента связанного по внутреннему идентификатору с текущим исходным элементом правила трансформации,
     * с целью извлечения его идентификатора (вторая часть).
     * @param $xml_rows - все строки XML-документа концептуальной модели
     * @param $find_element - элемент который необходимо найти
     * @param $find_element_attribute - атрибут элемента (внутренний идентификатор) по которому связаны элементы
     * @param $associated_element_id - идентификатор связанного элемента
     */
    public function findElementId($xml_rows, $find_element, $find_element_attribute, $associated_element_id)
    {
        // Обход всех тегов без пространства имен внутри корневого элемента
        foreach ($xml_rows->children() as $child) {
            // Если найден элемент который необходимо было найти в XML-документе
            if ($find_element == $child->getName()) {
                $exist_element_id = false;
                // Цикл по атрибутам элемента без пространства имен
                foreach ($child->attributes() as $attribute_name => $attribute_value) {
                    if ($attribute_name == $find_element_attribute)
                        if ($attribute_value == $associated_element_id)
                            $exist_element_id = true;
                }
                // Цикл по атрибутам элемента без пространства имен
                foreach ($child->attributes() as $attribute_name => $attribute_value) {
                    if ($attribute_name == self::ELEMENT_ID && $exist_element_id)
                        $this->current_element_id = $attribute_value;
                }
                // Цикл по атрибутам элемента с учетом пространства имен
                foreach ($this->namespaces as $prefix => $namespace)
                    foreach ($child->attributes($prefix, true) as $attribute_name => $attribute_value) {
                        if ($attribute_name == $find_element_attribute)
                            if ($attribute_value == $associated_element_id)
                                $exist_element_id = true;
                    }
                // Цикл по атрибутам элемента с учетом пространства имен
                foreach ($this->namespaces as $prefix => $namespace)
                    foreach ($child->attributes($prefix, true) as $attribute_name => $attribute_value) {
                        if ($attribute_name == self::ELEMENT_ID && $exist_element_id)
                            $this->current_element_id = $attribute_value;
                    }
            }
            // Поиск дочерних элементов (если дочернии элементы существуют, то повторно вызываем данную функцию)
            self::findElementId(
                $child,
                $find_element,
                $find_element_attribute,
                $associated_element_id
            );
        }
        // Обход всех тегов с пространством имен внутри корневого элемента
        foreach ($this->namespaces as $prefix => $namespace) {
            foreach ($xml_rows->children($this->namespaces[$prefix]) as $child) {
                // Если найден элемент который необходимо было найти в XML-документе
                if ($find_element == $child->getName()) {
                    $exist_element_id = false;
                    // Цикл по атрибутам элемента без пространства имен
                    foreach ($child->attributes() as $attribute_name => $attribute_value) {
                        if ($attribute_name == $find_element_attribute)
                            if ($attribute_value == $associated_element_id)
                                $exist_element_id = true;
                    }
                    // Цикл по атрибутам элемента без пространства имен
                    foreach ($child->attributes() as $attribute_name => $attribute_value) {
                        if ($attribute_name == self::ELEMENT_ID && $exist_element_id)
                            $this->current_element_id = $attribute_value;
                    }
                    // Цикл по атрибутам элемента с учетом пространства имен
                    foreach ($this->namespaces as $prefix => $namespace)
                        foreach ($child->attributes($prefix, true) as $attribute_name => $attribute_value) {
                            if ($attribute_name == $find_element_attribute)
                                if ($attribute_value == $associated_element_id)
                                    $exist_element_id = true;
                        }
                    // Цикл по атрибутам элемента с учетом пространства имен
                    foreach ($this->namespaces as $prefix => $namespace)
                        foreach ($child->attributes($prefix, true) as $attribute_name => $attribute_value) {
                            if ($attribute_name == self::ELEMENT_ID && $exist_element_id)
                                $this->current_element_id = $attribute_value;
                        }
                }
                // Поиск дочерних элементов (повторно вызываем данную функцию)
                self::findElementId(
                    $child,
                    $find_element,
                    $find_element_attribute,
                    $associated_element_id
                );
            }
        }
    }

    /**
     * Поиск элемента связанного по внутреннему идентификатору с текущим исходным элементом правила трансформации,
     * с целью извлечения его идентификатора (первая часть).
     * @param $child - текущий узел XML-документа концептуальной модели
     * @param $associated_element - элемент связанный с текущим исходным элементом правила трансформации
     * @param $current_source_element - текущий исходный элемент правила трансформации
     */
    public function findRelatedIdentifierElementId($child, $associated_element, $current_source_element)
    {
        // Цикл по всем правилам трансформации
        foreach ($this->transformation_rules as $tr_key => $transformation_rule) {
            // Если текущий исходный элемент равен исходному элементу правила трансформации
            if ($current_source_element == $transformation_rule[0][0]) {
                // Поиск правила трансформации по id
                $tr = TransformationRule::findOne($tr_key);
                // Поиск всех метаотношений по id исходного метакласса
                $metarelations = Metarelation::find()
                    ->where(array('right_metaclass' => $tr->source_metaclass))
                    ->all();
                // Цикл по всем метаотношениям
                foreach ($metarelations as $metarelation) {
                    // Поиск метассылки (дополнительной связи по метаатрибутам) по id метаотношения
                    $metareference = Metareference::find()
                        ->where(array('metarelation' => $metarelation->id))
                        ->one();
                    // Если выборка метассылки не пуста
                    if (!empty($metareference)) {
                        // Поиск левого метаатрибута
                        $left_metaattribute = Metaattribute::findOne($metareference->left_metaattribute);
                        // Поиск правого метаатрибута
                        $right_metaattribute = Metaattribute::findOne($metareference->right_metaattribute);
                        $associated_element_id = '';
                        // Цикл по атрибутам элемента без пространства имен (нахождение id)
                        foreach ($child->attributes() as $attribute_name => $attribute_value)
                            if ($attribute_name == $right_metaattribute->name)
                                $associated_element_id = (string)$attribute_value;
                        // Цикл по атрибутам элемента с учетом пространства имен (нахождение id)
                        foreach ($this->namespaces as $prefix => $namespace)
                            foreach ($child->attributes($prefix, true) as $attribute_name => $attribute_value)
                                if ($attribute_name == $right_metaattribute->name)
                                    $associated_element_id = (string)$attribute_value;
                        // Если существует атрибут,
                        // который должен быть связан по идентификатору с другим атрибутом элемента
                        if ($associated_element_id !== '')
                            // Поиск id элемента связанного по идентификатору с текущим исходным элементом
                            self::findElementId(
                                $this->xml_rows,
                                $associated_element,
                                $left_metaattribute->name,
                                $associated_element_id
                            );
                    }
                }
            }
        }
    }

    /**
     * Поиск элемента связанного с текущим исходным элементом правила трансформации,
     * с целью извлечения его идентификатора.
     * @param $xml_rows - все строки XML-документа концептуальной модели
     * @param $find_element - элемент который необходимо найти
     * @param $marker_element - промаркированный элемент (текущий исходный элемент правила трансформации)
     * @param $element_id - идентификатор связанного элемента
     */
    public function findAssociatedElementId($xml_rows, $find_element, $marker_element, $element_id)
    {
        // Обход всех тегов без пространства имен внутри корневого элемента
        foreach ($xml_rows->children() as $child) {
            // Если найден элемент который необходимо было найти в XML-документе
            if ($find_element == $child->getName()) {
                // Цикл по атрибутам элемента без пространства имен
                foreach ($child->attributes() as $attribute_name => $attribute_value) {
                    if ($attribute_name == self::ELEMENT_ID)
                        self::findAssociatedElementId(
                            $child,
                            $marker_element, // Элемент который необходимо искать теперь маркерный
                            $marker_element,
                            $attribute_value // id элемента - значение атрибута ELEMENT_ID
                        );
                    if ($attribute_name == self::MARKER && $find_element == $marker_element)
                        $this->current_element_id = $element_id;
                }
                // Цикл по атрибутам элемента с учетом пространства имен
                foreach ($this->namespaces as $prefix => $namespace)
                    foreach ($child->attributes($prefix, true) as $attribute_name => $attribute_value) {
                        if ($attribute_name == self::ELEMENT_ID)
                            self::findAssociatedElementId(
                                $child,
                                $marker_element, // Элемент который необходимо искать теперь маркерный
                                $marker_element,
                                $attribute_value // id элемента - значение атрибута ELEMENT_ID
                            );
                        if ($attribute_name == self::MARKER && $find_element == $marker_element)
                            $this->current_element_id = $element_id;
                    }
            }
            // Поиск дочерних элементов (повторный вызов данного метода, если дочернии элементы существуют)
            self::findAssociatedElementId(
                $child,
                $find_element,
                $marker_element,
                $element_id
            );
        }
        // Если анализируется XTM концепт-карта, то этот блок не выполняется
        if ($this->xml_rows->getName() != 'topicMap')
            // Обход всех тегов с пространством имен внутри корневого элемента
            foreach ($this->namespaces as $prefix => $namespace) {
                foreach ($xml_rows->children($this->namespaces[$prefix]) as $child) {
                    // Если найден элемент который необходимо было найти в XML-документе
                    if ($find_element == $child->getName()) {
                        // Цикл по атрибутам элемента без пространства имен
                        foreach ($child->attributes() as $attribute_name => $attribute_value) {
                            if ($attribute_name == self::ELEMENT_ID)
                                self::findAssociatedElementId(
                                    $child,
                                    $marker_element, // Элемент который необходимо искать теперь маркерный
                                    $marker_element,
                                    $attribute_value // id элемента - значение атрибута ELEMENT_ID
                                );
                            if ($attribute_name == self::MARKER && $find_element == $marker_element)
                                $this->current_element_id = $element_id;
                        }
                        // Цикл по атрибутам элемента с учетом пространства имен
                        foreach ($this->namespaces as $prefix => $namespace)
                            foreach ($child->attributes($prefix, true) as $attribute_name => $attribute_value) {
                                if ($attribute_name == self::ELEMENT_ID)
                                    self::findAssociatedElementId(
                                        $child,
                                        $marker_element, // Элемент который необходимо искать теперь маркерный
                                        $marker_element,
                                        $attribute_value // id элемента - значение атрибута ELEMENT_ID
                                    );
                                if ($attribute_name == self::MARKER && $find_element == $marker_element)
                                    $this->current_element_id = $element_id;
                            }
                    }
                    // Поиск дочерних элементов (повторный вызов данного метода)
                    self::findAssociatedElementId(
                        $child,
                        $find_element,
                        $marker_element,
                        $element_id
                    );
                }
            }
    }

    /**
     * Извлечение всех атрибутов (свойств) у дополнительного элемента концептуальной модели.
     * @param $child - текущий узел XML-документа концептуальной модели
     * @param $current_source_element - текущий исходный элемент правила трансформации
     * @param $current_target_element - текущий целевой элемент правила трансформации
     * @param $current_attributes - массив исходных и целевых атрибутов текущих элементов в правлиле трансформации
     */
    public function extractAdditionalPropertyValues($child, $current_source_element, $current_target_element,
                                                    $current_attributes)
    {
        // Если найден элемент который необходимо было найти в XML-документе
        if ($current_source_element == $child->getName()) {
            if ($this->exist_name == false) {
                // Удаление наименование из массива текущих извлеченных элементов, если оно там есть
                $current_extraction_values = $this->current_extraction_values;
                foreach ($current_extraction_values as $key => $list)
                    if ($list[0] == 'name')
                        unset($this->current_extraction_values[$key]);
            }
            // Цикл по всем атрибутам текущего исходного элемента
            foreach ($current_attributes as $current_attribute) {
                // Цикл по атрибутам элемента без пространства имен
                foreach ($child->attributes() as $attribute_name => $attribute_value)
                    if ($attribute_name == $current_attribute[0]) {
                        if ($current_attribute[1] == 'name' && $this->exist_name == false &&
                            (string)$attribute_value != '') {
                            $this->exist_name = true;
                            array_push($this->current_extraction_values, [$current_attribute[1],
                                (string)$attribute_value]);
                        }
                        if ($current_attribute[1] != 'name')
                            array_push($this->current_extraction_values, [$current_attribute[1],
                                (string)$attribute_value]);
                    }
                // Цикл по атрибутам элемента с учетом пространства имен
                foreach ($this->namespaces as $prefix => $namespace)
                    foreach ($child->attributes($prefix, true) as $attribute_name => $attribute_value)
                        if ($attribute_name == $current_attribute[0]) {
                            if ($current_attribute[1] == 'name' && $this->exist_name == false &&
                                (string)$attribute_value != '') {
                                $this->exist_name = true;
                                array_push($this->current_extraction_values, [$current_attribute[1],
                                    (string)$attribute_value]);
                            }
                            if ($current_attribute[1] != 'name')
                                array_push($this->current_extraction_values,
                                    [$current_attribute[1], (string)$attribute_value]);
                        }
            }
            // Если в массиве текущих атрибутов элемента содержится атрибут "name" и его нет в XML-документе,
            // то наименование элемента ищется как текст внутри данного элемента
            if ($this->exist_name == false) {
                // Удаление из начала и конца строки пробелы
                $text_content = trim(preg_replace('/\s\s+/', ' ', (string)$child));
                // Если есть значение для наименования элемента продукционной модели, то добавляем его в массив
                if ($text_content != '') {
                    array_push($this->current_extraction_values, ['name', $text_content]);
                    $this->exist_name = true;
                }
                else
                    // Добавление наименования (если текст внутри элемента не был найден)
                    array_push($this->current_extraction_values, ['name', $child->getName()]);
            }
        }
        // Если текущий анализируемый элемент участвует в других правилах трансформации,
        // то поиск дополнительных элементов (частей) внутри него не осуществляется
        $exist_source_element = false;
        foreach ($this->transformation_rules as $transformation_rule)
            foreach ($transformation_rule[0] as $source_element)
                if ($source_element == $child->getName())
                    $exist_source_element = true;
        if ($exist_source_element == false)
            // Поиск дочерних элементов (повторный вызов данного метода, если дочернии элементы существуют)
            self::extractAdditionalElements(
                $child,
                $current_source_element,
                $current_target_element,
                $current_attributes
            );
    }

    /**
     * Извлечение дополнительных элементов (частей) из концептуальной модели.
     * @param $xml_rows - текущий узел XML-документа концептуальной модели
     * @param $current_source_element - текущий исходный элемент правила трансформации
     * @param $current_target_element - текущий целевой элемент правила трансформации
     * @param $current_attributes - массив исходных и целевых атрибутов текущих элементов в правлиле трансформации
     */
    public function extractAdditionalElements($xml_rows, $current_source_element, $current_target_element,
                                              $current_attributes)
    {
        // Обход всех тегов без пространства имен внутри корневого элемента
        foreach ($xml_rows->children() as $child)
            // Извлечение значений свойств (атрибутов) у текущего дополнительного элемента
            self::extractAdditionalPropertyValues(
                $child,
                $current_source_element,
                $current_target_element,
                $current_attributes
            );
        // Если анализируется XTM концепт-карта, то этот блок не выполняется
        if ($this->xml_rows->getName() != 'topicMap')
            // Обход всех тегов с пространством имен внутри корневого элемента
            foreach ($this->namespaces as $prefix => $namespace)
                foreach ($xml_rows->children($this->namespaces[$prefix]) as $child)
                    // Извлечение значений свойств (атрибутов) у текущего дополнительного элемента
                    self::extractAdditionalPropertyValues(
                        $child,
                        $current_source_element,
                        $current_target_element,
                        $current_attributes
                    );
    }

    /**
     * Извлечение всех атрибутов (свойств) у данного элемента концептуальной модели.
     * @param $child - текущий узел XML-документа концептуальной модели
     * @param $current_source_elements - текущие исходные элементы правила трансформации
     * @param $current_target_element - текущий целевой элемент правила трансформации
     * @param $current_attributes - массив исходных и целевых атрибутов текущих элементов в правлиле трансформации
     * @param $associated_elements - массив всех связанных элементов с текущим исходным элементом правила трансформации
     * @param $knowledge_base_id - идентификатор базы знаний
     */
    public function extractPropertyValues($child, $current_source_elements, $current_target_element,
                                          $current_attributes, $knowledge_base_id, $associated_elements)
    {
        // Если текущий исходный элемент правила трансформации совпадает с найденным элементом в XML-документе
        if ($current_source_elements[0] == $child->getName()) {
            $this->exist_name = false;
            // Цикл по всем атрибутам текущего исходного элемента
            foreach ($current_attributes as $current_attribute) {
                // Цикл по атрибутам элемента без пространства имен
                foreach ($child->attributes() as $attribute_name => $attribute_value)
                    if ($attribute_name == $current_attribute[0]) {
                        if ($current_attribute[1] == 'name' && (string)$attribute_value != '')
                            $this->exist_name = true;
                        array_push($this->current_extraction_values, [$current_attribute[1], (string)$attribute_value]);
                    }
                // Цикл по атрибутам элемента с учетом пространства имен
                foreach ($this->namespaces as $prefix => $namespace)
                    foreach ($child->attributes($prefix, true) as $attribute_name => $attribute_value)
                        if ($attribute_name == $current_attribute[0]) {
                            if ($current_attribute[1] == 'name' && (string)$attribute_value != '')
                                $this->exist_name = true;
                            array_push($this->current_extraction_values,
                                [$current_attribute[1], (string)$attribute_value]);
                        }
            }
            // Если в массиве текущих атрибутов элемента содержится атрибут "name" и его нет в XML-документе,
            // то наименование элемента ищется как текст внутри данного элемента
            if ($this->exist_name == false) {
                // Удаление из начала и конца строки пробелы
                $text_content = trim(preg_replace('/\s\s+/', ' ', (string)$child));
                // Если есть значение для наименования элемента продукционной модели, то добавляем его в массив
                if ($text_content != '') {
                    array_push($this->current_extraction_values, ['name', $text_content]);
                    $this->exist_name = true;
                }
                else
                    // Добавление наименования, если текст внутри элемента не был найден
                    array_push($this->current_extraction_values, ['name', $child->getName()]);
            }

            // Если текущий исходный элемент связан с другими элементами
            if (!empty($associated_elements)) {
                // Добавление нового атрибута-маркера к текущему узлу
                $child->addAttribute(self::MARKER, 'true');
                // Цикл по всем элементам связанных с текущим исходным элементом
                foreach ($associated_elements as $key => $associated_element) {
                    // Обнуление значения найденного id для текущего элемента
                    $this->current_element_id = 0;
                    // Поиск id элемента связанного с текущим исходным элементом
                    self::findAssociatedElementId(
                        $this->xml_rows,
                        $associated_element,
                        $child->getName(),
                        $this->current_element_id
                    );
                    // Если id связанного элемента не установлен
                    if ($this->current_element_id == 0)
                        // Поиск id элемента связанного с текущим исходным элементом через связь по идентификатору
                        self::findRelatedIdentifierElementId(
                            $child,
                            $associated_element,
                            $current_source_elements[0]
                        );
                    // Если текущий связанный элемент относится к DataType, то сохраняем id типа данных
                    if ($key == 'DataType' && $this->current_data_type_id == 0)
                        $this->current_data_type_id = $this->current_element_id;
                    // Если текущий связанный элемент относится к FactTemplate, то сохраняем id шаблона факта
                    if ($key == 'FactTemplate' && $this->current_fact_template_id == 0)
                        $this->current_fact_template_id = $this->current_element_id;
                    // Если текущий связанный элемент относится к FactTemplateSlot, то сохраняем id слота шаблона факта
                    if ($key == 'FactTemplateSlot' && $this->current_fact_template_slot_id == 0)
                        $this->current_fact_template_slot_id = $this->current_element_id;
                    // Если текущий связанный элемент относится к Fact, то сохраняем id факта
                    if ($key == 'Fact' && $this->current_fact_id == 0)
                        $this->current_fact_id = $this->current_element_id;
                    // Если текущий связанный элемент относится к FactSlot, то сохраняем id слота факта
                    if ($key == 'FactSlot' && $this->current_fact_slot_id == 0)
                        $this->current_fact_slot_id = $this->current_element_id;
                    // Если текущий связанный элемент относится к RuleTemplate, то сохраняем id шаблона правила
                    if ($key == 'RuleTemplate' && $this->current_rule_template_id == 0)
                        $this->current_rule_template_id = $this->current_element_id;
                    // Если текущий связанный элемент относится к Rule, то сохраняем id правила
                    if ($key == 'Rule' && $this->current_rule_id == 0)
                        $this->current_rule_id = $this->current_element_id;

                    // Если текущий связанный элемент относится к Class, то сохраняем id класса
                    if ($key == 'Class' && $this->current_class_id == 0)
                        $this->current_class_id = $this->current_element_id;
                    // Если текущий связанный элемент относится к Object, то сохраняем id класса
                    if ($key == 'Object' && $this->current_object_id == 0)
                        $this->current_object_id = $this->current_element_id;
                    // Если текущий связанный элемент относится к Relationship, то сохраняем id класса
                    if ($key == 'Relationship' && $this->current_relationship_id == 0)
                        $this->current_relationship_id = $this->current_element_id;
                    // Если текущий связанный элемент относится к Property, то сохраняем id класса
                    if ($key == 'Property' && $this->current_property_id == 0)
                        $this->current_property_id = $this->current_element_id;
                }
                // Удаление атрибута-маркера у текущего узла
                $dom = dom_import_simplexml($child);
                $dom->removeAttribute(self::MARKER);
            }

            // Поиск дополнительной информации по данному исходному элементу правила трансформации
            $i = 0;
            foreach ($current_source_elements as $current_source_element) {
                if ($i > 0)
                    self::extractAdditionalElements(
                        $child,
                        $current_source_element,
                        $current_target_element,
                        $current_attributes
                    );
                $i++;
            }

            // Если текущий целевой элемент правила трансформации DataType
            if ($current_target_element == 'DataType') {
                // Добавление нового типа данных
                $data_type_id = ProductionModelGenerator::addDataType(
                    $knowledge_base_id,
                    $this->current_extraction_values
                );
                // Добавление нового атрибута (id созданного элемента) к элементу в XML-документ
                $child->addAttribute(self::ELEMENT_ID, $data_type_id);
            }
            // Если текущий целевой элемент правила трансформации FactTemplate
            if ($current_target_element == 'FactTemplate') {
                // Добавление нового шаблона факта
                $fact_template_id = ProductionModelGenerator::addFactTemplate(
                    $knowledge_base_id,
                    $this->current_extraction_values
                );
                // Добавление нового атрибута (id созданного элемента) к элементу в XML-документ
                $child->addAttribute(self::ELEMENT_ID, $fact_template_id);
            }
            // Если текущий целевой элемент правила трансформации FactTemplateSlot
            if ($current_target_element == 'FactTemplateSlot') {
                // Добавление нового слота шаблону факта
                $fact_template_slot_id = ProductionModelGenerator::addFactTemplateSlot(
                    $knowledge_base_id,
                    $this->current_fact_template_id,
                    $this->current_data_type_id,
                    $this->current_extraction_values
                );
                // Добавление нового атрибута (id созданного элемента) к элементу в XML-документ
                $child->addAttribute(self::ELEMENT_ID, $fact_template_slot_id);
            }
            // Если текущий целевой элемент правила трансформации Fact
            if ($current_target_element == 'Fact') {
                // Добавление нового факта
                $fact_id = ProductionModelGenerator::addFact(
                    $knowledge_base_id,
                    $this->current_fact_template_id,
                    $this->current_extraction_values
                );
                // Добавление нового атрибута (id созданного элемента) к элементу в XML-документ
                $child->addAttribute(self::ELEMENT_ID, $fact_id);
            }
            // Если текущий целевой элемент правила трансформации FactSlot
            if ($current_target_element == 'FactSlot') {
                // Добавление нового слота факту
                $fact_slot_id = ProductionModelGenerator::addFactSlot(
                    $knowledge_base_id,
                    $this->current_fact_id,
                    $this->current_data_type_id,
                    $this->current_extraction_values
                );
                // Добавление нового атрибута (id созданного элемента) к элементу в XML-документ
                $child->addAttribute(self::ELEMENT_ID, $fact_slot_id);
            }
            // Если текущий целевой элемент правила трансформации RuleTemplate
            if ($current_target_element == 'RuleTemplate') {
                // Добавление нового шаблона правила
                $rule_template_id = ProductionModelGenerator::addRuleTemplate(
                    $knowledge_base_id,
                    $this->current_extraction_values
                );
                // Добавление нового атрибута (id созданного элемента) к элементу в XML-документ
                $child->addAttribute(self::ELEMENT_ID, $rule_template_id);
            }
            // Если текущий целевой элемент правила трансформации RuleTemplateCondition
            if ($current_target_element == 'RuleTemplateCondition') {
                if ($this->association_number % 2 == 0) {
                    // Добавление нового условия для шаблона правила
                    $rule_template_condition_id = ProductionModelGenerator::addRuleTemplateCondition(
                        $this->current_rule_template_id,
                        $this->current_fact_template_id,
                        $this->current_extraction_values
                    );
                    // Добавление нового атрибута (id созданного элемента) к элементу в XML-документ
                    $child->addAttribute(self::ELEMENT_ID, $rule_template_condition_id);
                }
                $this->association_number++;
            }
            // Если текущий целевой элемент правила трансформации RuleTemplateAction
            if ($current_target_element == 'RuleTemplateAction') {
                if ($this->association_number % 2 != 0) {
                    // Добавление нового действия для шаблона правила
                    $rule_template_action_id = ProductionModelGenerator::addRuleTemplateAction(
                        $this->current_rule_template_id,
                        $this->current_fact_template_id,
                        $this->current_extraction_values
                    );
                    // Добавление нового атрибута (id созданного элемента) к элементу в XML-документ
                    $child->addAttribute(self::ELEMENT_ID, $rule_template_action_id);
                }
                $this->association_number++;
            }
            // Если текущий целевой элемент правила трансформации Rule
            if ($current_target_element == 'Rule') {
                // Добавление нового правила
                $rule_id = ProductionModelGenerator::addRule(
                    $knowledge_base_id,
                    $this->current_rule_template_id,
                    $this->current_extraction_values
                );
                // Добавление нового атрибута (id созданного элемента) к элементу в XML-документ
                $child->addAttribute(self::ELEMENT_ID, $rule_id);
            }
            // Если текущий целевой элемент правила трансформации RuleCondition
            if ($current_target_element == 'RuleCondition') {
                if ($this->association_number % 2 == 0) {
                    // Добавление нового условия для правила
                    $rule_condition_id = ProductionModelGenerator::addRuleCondition(
                        $this->current_rule_id,
                        $this->current_fact_id,
                        $this->current_extraction_values
                    );
                    // Формирование массива условий
                    $this->rule_condition_array[(int)$this->current_rule_id] = (int)$this->current_fact_id;
                    // Добавление нового атрибута (id созданного элемента) к элементу в XML-документ
                    $child->addAttribute(self::ELEMENT_ID, $this->current_fact_id);
                }
                $this->association_number++;
            }
            // Если текущий целевой элемент правила трансформации RuleAction
            if ($current_target_element == 'RuleAction') {
                if ($this->association_number % 2 != 0) {
                    // Извлечение наименования функции из стереотипа
                    $association_id = self::findAssociation($this->xml_rows, 0, (int)$this->current_rule_id);
                    $stereotype_name = self::findStereotype($this->xml_rows, 'none', $association_id);
                    array_push($this->current_extraction_values, ['function', $stereotype_name]);
                    // Добавление нового действия для правила
                    $rule_action_id = ProductionModelGenerator::addRuleAction(
                        $this->current_rule_id,
                        $this->current_fact_id,
                        $this->current_extraction_values
                    );
                    // Извлечение оператора для условия правила
                    $operator = self::extractMultiplicityRange($child);
                    // Формирование массива сложных правил
                    $exist_complex_rule = false;
                    foreach ($this->rule_condition_array as $key => $value)
                        if ($key == (int)$this->current_rule_id)
                            if (array_key_exists((int)$this->current_fact_id, $this->complex_rule_array)) {
                                $flag = false;
                                foreach ($this->complex_rule_array[(int)$this->current_fact_id] as $condition)
                                    if ($condition[0] == $value)
                                        $flag = true;
                                if ($flag == false) {
                                    array_push($this->complex_rule_array[(int)$this->current_fact_id],
                                        [$value, $operator]);
                                    $exist_complex_rule = true;
                                }
                            }
                    if ($exist_complex_rule == false)
                        foreach ($this->rule_condition_array as $key => $value)
                            if ($key == (int)$this->current_rule_id)
                                $this->complex_rule_array[(int)$this->current_fact_id] = [[$value, $operator]];
                    // Добавление нового атрибута (id созданного элемента) к элементу в XML-документ
                    $child->addAttribute(self::ELEMENT_ID, $this->current_fact_id);
                }
                $this->association_number++;
            }

            // Если текущий целевой элемент правила трансформации Class
            if ($current_target_element == 'Class') {
                // Добавление нового класса
                $class_id = OntologyGenerator::addClass(
                    $knowledge_base_id,
                    $this->current_extraction_values
                );
                // Добавление нового атрибута (id созданного элемента) к элементу в XML-документ
                $child->addAttribute(self::ELEMENT_ID, $class_id);
            }
            // Если текущий целевой элемент правила трансформации Object
            if ($current_target_element == 'Object') {
                // Добавление нового объекта
                $object_id = OntologyGenerator::addObject(
                    $knowledge_base_id,
                    $this->current_class_id,
                    $this->current_extraction_values
                );
                // Добавление нового атрибута (id созданного элемента) к элементу в XML-документ
                $child->addAttribute(self::ELEMENT_ID, $object_id);
            }
            // Если текущий целевой элемент правила трансформации Relationship
            if ($current_target_element == 'Relationship') {
                // Добавление нового отношения
                $relationship_id = OntologyGenerator::addRelationship(
                    $knowledge_base_id,
                    $this->current_extraction_values
                );
                // Добавление нового атрибута (id созданного элемента) к элементу в XML-документ
                $child->addAttribute(self::ELEMENT_ID, $relationship_id);
            }
            // Если текущий целевой элемент правила трансформации Property
            if ($current_target_element == 'Property') {
                // Добавление нового свойства класса
                $property_id = OntologyGenerator::addProperty(
                    $knowledge_base_id,
                    $this->current_class_id,
                    $this->current_data_type_id,
                    $this->current_extraction_values
                );
                // Добавление нового атрибута (id созданного элемента) к элементу в XML-документ
                $child->addAttribute(self::ELEMENT_ID, $property_id);
            }
            // Если текущий целевой элемент правила трансформации PropertyValue
            if ($current_target_element == 'PropertyValue') {
                // Добавление нового значения свойства
                $property_value_id = OntologyGenerator::addPropertyValue(
                    $this->current_property_id,
                    $this->current_object_id,
                    $this->current_extraction_values
                );
                // Добавление нового атрибута (id созданного элемента) к элементу в XML-документ
                $child->addAttribute(self::ELEMENT_ID, $property_value_id);
            }
            // Если текущий целевой элемент правила трансформации ObjectRelationship
            if ($current_target_element == 'ObjectRelationship') {
                // Добавление нового отношения объекта
                $object_relationship_id = OntologyGenerator::addObjectRelationship(
                    $this->current_relationship_id,
                    $this->current_object_id,
                    $this->current_extraction_values
                );
                // Добавление нового атрибута (id созданного элемента) к элементу в XML-документ
                $child->addAttribute(self::ELEMENT_ID, $object_relationship_id);
            }
            // Если текущий целевой элемент правила трансформации LeftHandSide
            if ($current_target_element == 'LeftHandSide') {
                if ($this->association_number % 2 == 0) {
                    // Добавление левой части отношения
                    $left_hand_side_id = OntologyGenerator::addLeftHandSide(
                        $this->current_relationship_id,
                        $this->current_class_id
                    );
                    // Добавление нового атрибута (id созданного элемента) к элементу в XML-документ
                    $child->addAttribute(self::ELEMENT_ID, $left_hand_side_id);
                }
                $this->association_number++;
            }
            // Если текущий целевой элемент правила трансформации RightHandSide
            if ($current_target_element == 'RightHandSide') {
                if ($this->association_number % 2 != 0) {
                    // Добавление правой части отношения
                    $right_hand_side_id = OntologyGenerator::addRightHandSide(
                        $this->current_relationship_id,
                        $this->current_class_id
                    );
                    // Добавление нового атрибута (id созданного элемента) к элементу в XML-документ
                    $child->addAttribute(self::ELEMENT_ID, $right_hand_side_id);
                }
                $this->association_number++;
            }

            // Добавление массива текущих извлеченных значений в массив всех извлеченных значений элементов
            array_push($this->extraction_values, $this->current_extraction_values);
            $this->current_extraction_values = array();
            // Обнуление текущих id элементов
            $this->current_data_type_id = 0;
            $this->current_fact_template_id = 0;
            $this->current_fact_template_slot_id = 0;
            $this->current_fact_id = 0;
            $this->current_fact_slot_id = 0;
            $this->current_rule_template_id = 0;
            $this->current_rule_id = 0;
            $this->current_class_id = 0;
            $this->current_object_id = 0;
            $this->current_relationship_id = 0;
            $this->current_property_id = 0;
        }
    }

    /**
     * Извлечение элементов концептуальной модели.
     * @param $xml_rows - все строки XML-документа концептуальной модели
     * @param $current_source_elements - текущие исходные элементы правила трансформации
     * @param $current_target_element - текущий целевой элемент правила трансформации
     * @param $current_attributes - массив исходных и целевых атрибутов текущих элементов в правлиле трансформации
     * @param $associated_elements - массив всех связанных элементов с текущим исходным элементом правила трансформации
     * @param $knowledge_base_id - идентификатор базы знаний
     */
    public function extractElements($xml_rows, $current_source_elements, $current_target_element, $current_attributes,
                                    $associated_elements, $knowledge_base_id)
    {
        // Если анализируется XTM концепт-карта, то этот блок не выполняется
        if ($this->xml_rows->getName() != 'topicMap') {
            // Обход всех тегов без пространства имен внутри корневого элемента
            foreach ($xml_rows->children() as $child) {
                // Извлечение значений свойств (атрибутов) у текущего элемента
                self::extractPropertyValues(
                    $child,
                    $current_source_elements,
                    $current_target_element,
                    $current_attributes,
                    $knowledge_base_id,
                    $associated_elements
                );
                // Поиск дочерних элементов (повторный вызов данного метода, если дочернии элементы существуют)
                self::extractElements(
                    $child,
                    $current_source_elements,
                    $current_target_element,
                    $current_attributes,
                    $associated_elements,
                    $knowledge_base_id
                );
            }
            // Обход всех тегов с пространством имен внутри корневого элемента
            foreach ($this->namespaces as $prefix => $namespace) {
                foreach ($xml_rows->children($this->namespaces[$prefix]) as $child) {
                    // Извлечение значений свойств (атрибутов) у текущего элемента
                    self::extractPropertyValues(
                        $child,
                        $current_source_elements,
                        $current_target_element,
                        $current_attributes,
                        $knowledge_base_id,
                        $associated_elements
                    );
                    // Поиск дочерних элементов (повторный вызов данного метода)
                    self::extractElements(
                        $child,
                        $current_source_elements,
                        $current_target_element,
                        $current_attributes,
                        $associated_elements,
                        $knowledge_base_id
                    );
                }
            }
        }
    }

    /**
     * Извлечение оператора для условия правила.
     * @param $association_end - элемент части ассоциации, где ищется оператор условия
     * @return string - оператор условия продукционного правила
     */
    public function extractMultiplicityRange($association_end)
    {
        $lower = 0;
        $upper = 0;
        $operator = 'AND';
        $multiplicity_range = 'Multiplicity.range';
        // Обход всех пространств имен
        foreach ($this->namespaces as $prefix => $namespace)
            // Поиск кратности данной части ассоциации
            foreach ($association_end->children($this->namespaces[$prefix]) as $association_end_element)
                if ($association_end_element->getName() == 'AssociationEnd.multiplicity')
                    foreach ($association_end_element->Multiplicity->$multiplicity_range->MultiplicityRange->
                        attributes() as $attribute_name => $attribute_value) {
                        if ($attribute_name == 'lower')
                            $lower = (int)$attribute_value;
                        if ($attribute_name == 'upper')
                            $upper = (int)$attribute_value;
                    }
        // Определение оператора "ИЛИ"
        if (($lower == 0 && $upper == 1))
            $operator = 'OR';
        // Определение оператора "НЕ"
        if ($lower == 0 && $upper == 0)
            $operator = 'NOT';

        return $operator;
    }

    /**
     * Извлечение id ассоциации.
     * @param $child - текущий узел XML-документа концептуальной модели
     * @param $association_id - текущее значение id ассоциации
     * @param $rule_id - id правила
     * @return string - извлеченное значение id ассоциации
     */
    public function extractAssociationId($child, $association_id, $rule_id)
    {
        // Если найден элемент "Association"
        if ($child->getName() == 'Association') {
            $found_rule_id = false;
            $current_association_id = '';
            // Цикл по атрибутам элемента "Association" без учета пространства имен
            foreach ($child->attributes() as $attribute_name => $attribute_value) {
                // Если id правила текущей ассоциации совпадает с id искомого правила
                if ($attribute_name == self::ELEMENT_ID && (int)$attribute_value == $rule_id)
                    $found_rule_id = true;
                // Запоминание id текущей ассоциации
                if ($attribute_name == 'xmi.id')
                    $current_association_id = (string)$attribute_value;
            }
            // Запоминание id искомой ассоциации
            if ($found_rule_id)
                $association_id = $current_association_id;
        }
        // Поиск дочерних элементов
        $association_id = self::findAssociation($child, $association_id, $rule_id);

        return $association_id;
    }

    /**
     * Поиск ассоциации по id правила.
     * @param $xml_rows - все строки XML-документа концептуальной модели
     * @param $association_id - текущее значение id ассоциации
     * @param $rule_id - id правила
     * @return string - найденное значение id ассоциации
     */
    public function findAssociation($xml_rows, $association_id, $rule_id)
    {
        // Обход всех тегов без пространства имен внутри корневого элемента
        foreach ($xml_rows->children() as $child)
            // Извлечение id ассоциации по id правила
            $association_id = self::extractAssociationId($child, $association_id, $rule_id);
        // Обход всех тегов с пространством имен внутри корневого элемента
        foreach ($this->namespaces as $prefix => $namespace)
            foreach ($xml_rows->children($this->namespaces[$prefix]) as $child)
                // Извлечение id ассоциации по id правила
                $association_id = self::extractAssociationId($child, $association_id, $rule_id);

        return $association_id;
    }

    /**
     * Извлечение наименования стереотипа.
     * @param $child - текущий узел XML-документа концептуальной модели
     * @param $stereotype_name - текущее наименование стереотипа
     * @param $association_id - id ассоциации
     * @return string - извлеченное наименование стереотипа
     */
    public function extractStereotypeName($child, $stereotype_name, $association_id)
    {
        // Если найден элемент "Stereotype"
        if ($child->getName() == 'Stereotype') {
            $found_stereotype_name = false;
            $current_stereotype_name = 'none';
            // Цикл по атрибутам элемента "Stereotype" без учета пространства имен
            foreach ($child->attributes() as $attribute_name => $attribute_value) {
                // Поиск ассоциации связанной со стереотипом по id
                if ($attribute_name == 'extendedElement')
                    $found_stereotype_name = stristr((string)$attribute_value, $association_id);
                // Запоминание наименования текущего стереотипа
                if ($attribute_name == 'name')
                    $current_stereotype_name = (string)$attribute_value;
            }
            // Запоминание наименования искомого стереотипа
            if ($found_stereotype_name !== false)
                $stereotype_name = $current_stereotype_name;
        }
        // Поиск дочерних элементов
        $stereotype_name = self::findStereotype($child, $stereotype_name, $association_id);

        return $stereotype_name;
    }

    /**
     * Поиск стереотипа по id ассоциации.
     * @param $xml_rows - все строки XML-документа концептуальной модели
     * @param $stereotype_name - текущее наименование стереотипа
     * @param $association_id - id ассоциации
     * @return string - извлеченное наименование стереотипа
     */
    public function findStereotype($xml_rows, $stereotype_name, $association_id)
    {
        // Обход всех тегов без пространства имен внутри корневого элемента
        foreach ($xml_rows->children() as $child)
            // Извлечение наименования стереотипа по id ассоциации
            $stereotype_name = self::extractStereotypeName($child, $stereotype_name, $association_id);
        // Обход всех тегов с пространством имен внутри корневого элемента
        foreach ($this->namespaces as $prefix => $namespace)
            foreach ($xml_rows->children($this->namespaces[$prefix]) as $child)
                // Извлечение наименования стереотипа по id ассоциации
                $stereotype_name = self::extractStereotypeName($child, $stereotype_name, $association_id);

        return $stereotype_name;
    }

    /**
     * Извлечение понятий (topic) и сохранение фактов.
     * @param $knowledge_base_id - идентификатор базы знаний
     */
    public function extractCmapTopic($knowledge_base_id)
    {
        $link = 'http://cmap.coginst.uwf.edu/#concept';
        // Обход всех тегов без пространства имен внутри корневого элемента
        foreach ($this->xml_rows->children() as $child)
            if ($child->getName() == 'topic')
                // Цикл по атрибутам элемента с учетом пространства имен
                foreach ($this->namespaces as $prefix => $namespace)
                    foreach ($child->instanceOf->subjectIndicatorRef->attributes($prefix, true) as
                             $attribute_name => $attribute_value)
                        if ($attribute_name == 'href' && $attribute_value == $link)
                            foreach ($child->attributes() as $child_attribute_name => $child_attribute_value) {
                                // Извлечение наименования понятия (topic)
                                $topic_name = trim(preg_replace('/\s\s+/', ' ',
                                    (string)$child->baseName->baseNameString));
                                $chr = mb_substr($topic_name, 0, 1, 'utf-8');
                                // Если первая буква в наименовании понятия - заглавная
                                if (mb_strtolower($chr, 'utf-8') != $chr) {
                                    // Добавление нового факта
                                    $fact_id = ProductionModelGenerator::addFact(
                                        $knowledge_base_id,
                                        $this->current_fact_template_id,
                                        [['name', $topic_name]]
                                    );
                                    // Формирование массива извлеченных понятий (topic)
                                    array_push($this->cmap_topic_array, [
                                        ['fact_id', $fact_id],
                                        ['topic_id', (string)$child_attribute_value],
                                        ['name', $topic_name],
                                    ]);
                                }
                                else {
                                    // Формирование массива извлеченных атрибутов понятий
                                    array_push($this->cmap_topic_attribute_array, [
                                        ['topic_id', (string)$child_attribute_value],
                                        ['name', $topic_name],
                                    ]);
                                }
                            }
    }

    /**
     * Извлечение атрибутов понятий (topic) и сохранение сложных правил и слотов фактов.
     * @param $knowledge_base_id - идентификатор базы знаний
     */
    public function extractCmapAssociation($knowledge_base_id)
    {
        // Обход всех тегов без пространства имен внутри корневого элемента
        foreach ($this->xml_rows->children() as $child)
            if ($child->getName() == 'association') {
                $member_number = 0;
                $left_member = 0;
                $right_member = 0;
                $exist_left_member = false;
                $exist_right_member = false;
                $exist_topic_attribute = false;
                $topic_attribute_array = array();
                foreach ($child->children() as $association_child)
                    if ($association_child->getName() == 'member') {
                        $member_number++;
                        // Цикл по атрибутам элемента с учетом пространства имен
                        foreach ($this->namespaces as $prefix => $namespace)
                            foreach ($association_child->topicRef->attributes($prefix, true) as
                                     $attribute_name => $attribute_value)
                                if ($attribute_name == 'href') {
                                    foreach ($this->cmap_topic_array as $topic)
                                        if ('#' . $topic[1][1] == $attribute_value) {
                                            if ($member_number == 1) {
                                                $left_member = $topic[0][1];
                                                $exist_left_member = true;
                                            }
                                            if ($member_number == 2) {
                                                $right_member = $topic[0][1];
                                                $exist_right_member = true;
                                            }
                                        }
                                    foreach ($this->cmap_topic_attribute_array as $topic_attribute)
                                        if ('#' . $topic_attribute[0][1] == $attribute_value) {
                                            $exist_topic_attribute = true;
                                            $topic_attribute_array = $topic_attribute[1];
                                        }
                                }
                    }
                // Формирование массива сложных правил
                if ($exist_left_member && $exist_right_member)
                    if (array_key_exists($right_member, $this->complex_rule_array))
                        array_push($this->complex_rule_array[$right_member], [$left_member, 'AND']);
                    else
                        $this->complex_rule_array[$right_member] = [[$left_member, 'AND']];
                // Добавление нового слота факту
                if ($exist_left_member && $exist_topic_attribute)
                    $fact_slot_id = ProductionModelGenerator::addFactSlot(
                        $knowledge_base_id,
                        $left_member,
                        $this->current_data_type_id,
                        [$topic_attribute_array]
                    );
            }
        // Создание сложных правил с несколькими условиями и оператором "AND"
        ProductionModelGenerator::addComplexRules($knowledge_base_id, $this->complex_rule_array);
    }

    /**
     * Создание продукционной модели на основе извлеченных элементах концептуальной модели.
     * @param $knowledge_base_id - идентификатор продукционной базы знаний
     * @param $software_component_id - идентификатор программного компонента
     * @param $xml_rows - набор строк XML-документа
     * @return string - текст хода выполнения импорта концептуальной модели (генерации элементов продукций)
     */
    public function createProductionModel($knowledge_base_id, $software_component_id, $xml_rows)
    {
        // Получение всех строк в XML-документе концептуальной модели
        $this->xml_rows = $xml_rows;
        // Получение всех пространств имен объявленых в XML-документе концептуальной модели
        $this->namespaces = $xml_rows->getDocNamespaces(true);
        // Создание массива правил трансформации и их тел (атрибутов)
        self::createTransformationArrays($software_component_id);

        $import_progress = '<h2>Ход выполнения импорта</h2><br />';
        $import_progress = $import_progress . '<b>Правила трансформации:</b><br />';
        $import_progress = $import_progress . json_encode($this->transformation_rules);
        $import_progress = $import_progress . "<br /><br /><b>Тела правил трансформации:</b><br />";
        $import_progress = $import_progress . json_encode($this->transformation_bodies);
        $import_progress = $import_progress . "<br /><br />";

        $exist_rule = false;
        // Цикл по всем правилам трансформации
        foreach ($this->transformation_rules as $tr_key => $transformation_rule) {
            // Текущий исходный элемент в правлиле трансформации
            $current_source_elements = $transformation_rule[0];
            // Текущий целевой элемент в правлиле трансформации
            $current_target_element = $transformation_rule[1];
            // Обнуление массива извлеченных значений текущего элемента
            $this->extraction_values = array();
            // Массив исходных и целевых атрибутов текущих элементов в правлиле трансформации
            $current_attributes = array();
            // Обход всех тел (атрибутов) правил трансформации
            foreach ($this->transformation_bodies as $transformation_body)
                // Если идентификатор правила трансформации совпадает
                if ($tr_key == $transformation_body[0])
                    // Формирование массива исходных и целевых атрибутов текущих элементов в правлиле трансформации
                    array_push($current_attributes, [$transformation_body[1][0], $transformation_body[1][1]]);

            // Если целевой элемент в правиле трансформации не "ProductionModel"
            if ($current_target_element != 'ProductionModel') {
                // Массив элементов связанных с текущим исходным элементом
                $associated_elements = array();
                // Обход по всем правилам трансформации
                foreach ($this->transformation_rules as $rule) {
                    // Если целевой элемент в текущем правиле трансформации "Fact",
                    // то все исходные элементы правила трансформации добавляются в массив связанных элементов
                    if ($current_target_element == 'Fact')
                        if ($rule[1] == 'FactTemplate')
                            $associated_elements['FactTemplate'] = $rule[0][0];
                    // Если целевой элемент в текущем правиле трансформации "Rule",
                    // то все исходные элементы правила трансформации добавляются в массив связанных элементов
                    if ($current_target_element == 'Rule')
                        if ($rule[1] == 'RuleTemplate')
                            $associated_elements['RuleTemplate'] = $rule[0][0];
                    // Если целевой элемент в текущем правиле трансформации "FactTemplateSlot",
                    // то все исходные элементы правила трансформации добавляются в массив связанных элементов
                    if ($current_target_element == 'FactTemplateSlot') {
                        if ($rule[1] == 'FactTemplate')
                            $associated_elements['FactTemplate'] = $rule[0][0];
                        if ($rule[1] == 'DataType')
                            $associated_elements['DataType'] = $rule[0][0];
                    }
                    // Если целевой элемент в текущем правиле трансформации "FactSlot",
                    // то все исходные элементы правила трансформации добавляются в массив связанных элементов
                    if ($current_target_element == 'FactSlot') {
                        if ($rule[1] == 'Fact')
                            $associated_elements['Fact'] = $rule[0][0];
                        if ($rule[1] == 'DataType')
                            $associated_elements['DataType'] = $rule[0][0];
                    }
                    // Если целевой элемент в текущем правиле трансформации "RuleTemplateCondition" или
                    // "RuleTemplateAction", то все исходные элементы правила трансформации
                    // добавляются в массив связанных элементов
                    if ($current_target_element == 'RuleTemplateCondition' ||
                        $current_target_element == 'RuleTemplateAction') {
                        if ($rule[1] == 'RuleTemplate')
                            $associated_elements['RuleTemplate'] = $rule[0][0];
                        if ($rule[1] == 'FactTemplate')
                            $associated_elements['FactTemplate'] = $rule[0][0];
                    }
                    // Если целевой элемент в текущем правиле трансформации "RuleCondition" или "RuleAction",
                    // то все исходные элементы правила трансформации добавляются в массив связанных элементов
                    if ($current_target_element == 'RuleCondition' || $current_target_element == 'RuleAction') {
                        if ($rule[1] == 'Rule')
                            $associated_elements['Rule'] = $rule[0][0];
                        if ($rule[1] == 'Fact')
                            $associated_elements['Fact'] = $rule[0][0];
                        $exist_rule = true;
                    }
                }

                $import_progress = $import_progress . "<b>Связанные элементы c (" .
                    json_encode($current_source_elements) . ", " . $current_target_element . "):</b><br />";
                $import_progress = $import_progress . json_encode($associated_elements);
                $import_progress = $import_progress . '<br /><br />';

                $this->association_number = 1;
                // Извлечение элементов из концептуальной модели путем рекурсивного поиска в глубину
                self::extractElements(
                    $xml_rows,
                    $current_source_elements,
                    $current_target_element,
                    $current_attributes,
                    $associated_elements,
                    $knowledge_base_id
                );

                $import_progress = $import_progress . "<b>Извлеченные элементы для " .
                    $current_target_element . ":</b><br />";
                $import_progress = $import_progress . json_encode($this->extraction_values);
                $import_progress = $import_progress . '<br /><br />';
            }
        }
        // Если существуют правила трансформации задающие создание продукционных правил с условием и действием
        if ($exist_rule) {
            // Создание сложных правил с несколькими условиями и разными операторами
            ProductionModelGenerator::addComplexRules($knowledge_base_id, $this->complex_rule_array);

            $import_progress = $import_progress . "<b>Извлеченные сложные правила:</b><br />";
            $import_progress = $import_progress . json_encode($this->complex_rule_array);
            $import_progress = $import_progress . '<br /><br />';
        }

        // Если анализируется XTM концепт-карта
        if ($this->xml_rows->getName() == 'topicMap') {
            // Извлечение и сохранение фактов
            self::extractCmapTopic($knowledge_base_id);
            $import_progress = $import_progress . "<b>Извлеченные элементы для topic:</b><br />";
            $import_progress = $import_progress . json_encode($this->cmap_topic_array);
            $import_progress = $import_progress . '<br /><br />';
            $import_progress = $import_progress . "<b>Извлеченные элементы для topic attribute:</b><br />";
            $import_progress = $import_progress . json_encode($this->cmap_topic_attribute_array);
            $import_progress = $import_progress . '<br /><br />';
            // Извлечение и сохранение слотов фактов и сложных правил
            self::extractCmapAssociation($knowledge_base_id);
            $import_progress = $import_progress . "<b>Извлеченные элементы для association:</b><br />";
            $import_progress = $import_progress . json_encode($this->complex_rule_array);
            $import_progress = $import_progress . '<br /><br />';
        }

        // Определение набора начальных фактов
        //ProductionModelGenerator::setInitialFacts($knowledge_base_id);

        return $import_progress;
    }

    /**
     * Создание онтологической модели на основе извлеченных элементах концептуальной модели.
     * @param $knowledge_base_id - идентификатор продукционной базы знаний
     * @param $software_component_id - идентификатор программного компонента
     * @param $xml_rows - набор строк XML-документа
     * @return string - текст хода выполнения импорта концептуальной модели (генерации элементов онтологии)
     */
    public function createOntology($knowledge_base_id, $software_component_id, $xml_rows)
    {
        // Получение всех строк в XML-документе концептуальной модели
        $this->xml_rows = $xml_rows;
        // Получение всех пространств имен объявленых в XML-документе концептуальной модели
        $this->namespaces = $xml_rows->getDocNamespaces(true);
        // Создание массива правил трансформации и их тел (атрибутов)
        self::createTransformationArrays($software_component_id);

        $import_progress = '<h2>Ход выполнения импорта</h2><br />';
        $import_progress = $import_progress . '<b>Правила трансформации:</b><br />';
        $import_progress = $import_progress . json_encode($this->transformation_rules);
        $import_progress = $import_progress . "<br /><br /><b>Тела правил трансформации:</b><br />";
        $import_progress = $import_progress . json_encode($this->transformation_bodies);
        $import_progress = $import_progress . "<br /><br />";

        // Цикл по всем правилам трансформации
        foreach ($this->transformation_rules as $tr_key => $transformation_rule) {
            // Текущий исходный элемент в правлиле трансформации
            $current_source_elements = $transformation_rule[0];
            // Текущий целевой элемент в правлиле трансформации
            $current_target_element = $transformation_rule[1];
            // Обнуление массива извлеченных значений текущего элемента
            $this->extraction_values = array();
            // Массив исходных и целевых атрибутов текущих элементов в правлиле трансформации
            $current_attributes = array();
            // Обход всех тел (атрибутов) правил трансформации
            foreach ($this->transformation_bodies as $transformation_body)
                // Если идентификатор правила трансформации совпадает
                if ($tr_key == $transformation_body[0])
                    // Формирование массива исходных и целевых атрибутов текущих элементов в правлиле трансформации
                    array_push($current_attributes, [$transformation_body[1][0], $transformation_body[1][1]]);

            // Если целевой элемент в правиле трансформации не "Ontology"
            if ($current_target_element != 'Ontology') {
                // Массив элементов связанных с текущим исходным элементом
                $associated_elements = array();
                // Обход по всем правилам трансформации
                foreach ($this->transformation_rules as $rule) {
                    // Если целевой элемент в текущем правиле трансформации "Object",
                    // то все исходные элементы правила трансформации добавляются в массив связанных элементов
                    if ($current_target_element == 'Object')
                        if ($rule[1] == 'Class')
                            $associated_elements['Class'] = $rule[0][0];
                    // Если целевой элемент в текущем правиле трансформации "Property",
                    // то все исходные элементы правила трансформации добавляются в массив связанных элементов
                    if ($current_target_element == 'Property') {
                        if ($rule[1] == 'Class')
                            $associated_elements['Class'] = $rule[0][0];
                        if ($rule[1] == 'DataType')
                            $associated_elements['DataType'] = $rule[0][0];
                    }
                    // Если целевой элемент в текущем правиле трансформации "PropertyValue",
                    // то все исходные элементы правила трансформации добавляются в массив связанных элементов
                    if ($current_target_element == 'PropertyValue') {
                        if ($rule[1] == 'Property')
                            $associated_elements['Property'] = $rule[0][0];
                        if ($rule[1] == 'Object')
                            $associated_elements['Object'] = $rule[0][0];
                    }
                    // Если целевой элемент в текущем правиле трансформации "RightHandSide" или "LeftHandSide",
                    // то все исходные элементы правила трансформации добавляются в массив связанных элементов
                    if ($current_target_element == 'RightHandSide' || $current_target_element == 'LeftHandSide') {
                        if ($rule[1] == 'Relationship')
                            $associated_elements['Relationship'] = $rule[0][0];
                        if ($rule[1] == 'Class')
                            $associated_elements['Class'] = $rule[0][0];
                    }
                    // Если целевой элемент в текущем правиле трансформации "ObjectRelationship"
                    // то все исходные элементы правила трансформации добавляются в массив связанных элементов
                    if ($current_target_element == 'ObjectRelationship') {
                        if ($rule[1] == 'Relationship')
                            $associated_elements['Relationship'] = $rule[0][0];
                        if ($rule[1] == 'Object')
                            $associated_elements['Object'] = $rule[0][0];
                    }
                }

                $import_progress = $import_progress . "<b>Связанные элементы c (" .
                    json_encode($current_source_elements) . ", " . $current_target_element . "):</b><br />";
                $import_progress = $import_progress . json_encode($associated_elements);
                $import_progress = $import_progress . '<br /><br />';

                $this->association_number = 1;
                // Извлечение элементов из концептуальной модели путем рекурсивного поиска в глубину
                self::extractElements(
                    $xml_rows,
                    $current_source_elements,
                    $current_target_element,
                    $current_attributes,
                    $associated_elements,
                    $knowledge_base_id
                );

                $import_progress = $import_progress . "<b>Извлеченные элементы для " .
                    $current_target_element . ":</b><br />";
                $import_progress = $import_progress . json_encode($this->extraction_values);
                $import_progress = $import_progress . '<br /><br />';
            }
        }

        return $import_progress;
    }
}