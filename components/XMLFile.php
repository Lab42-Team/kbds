<?php

namespace app\components;

use Yii;
use app\modules\software_component\models\Metaclass;
use app\modules\software_component\models\Metaattribute;
use app\modules\software_component\models\Metarelation;

/**
 * XMLFile class.
 * Класс XMLFile обеспечивает обработку XML-файлов концептуальных моделей
 * (создание метамодели по исходной концептуальной модели).
 */
class XMLFile
{
    // Массив всех элементов XML-документа
    public $elements_array = array();
    // Массив всех атрибутов элементов XML-документа (элементом массива является одномерный массивов)
    public $attributes_array = array();
    // Массив всех бинарных связей между элементами XML-документа (элементом массива является двумерный массивов)
    public $relations_array = array();

    /**
     * Добавление XML-элемента в массив элементов (elements_array).
     * @param $element - наименование элемента
     */
    public function addElementInArray($element)
    {
        // Добавление элемента в массив элементов, если его там нет
        if (!in_array($element, $this->elements_array, true))
            array_push($this->elements_array, $element);
    }

    /**
     * Добавление атрибутов XML-элемента в массив атрибутов (attributes_array).
     * @param $element - элемент
     * @param $namespaces - пространство имен
     */
    public function addAttributesInArray($element, $namespaces)
    {
        // Формируем временный массив из набора атрибутов элемента без пространства имен
        $attributes_array = array();
        foreach ($element->attributes() as $attribute_name => $attribute_value)
            array_push($attributes_array, $attribute_name);
        // Формируем временный массив из набора атрибутов элемента с учетом пространства имен
        foreach ($namespaces as $prefix => $namespace)
            foreach ($element->attributes($prefix, true) as $attribute_name => $attribute_value)
                if (!in_array($attribute_name, $attributes_array, true))
                    array_push($attributes_array, $attribute_name);
        // Поиск элемента в массиве атрибутов (по ключу)
        $is_element = false;
        foreach ($this->attributes_array as $element_name => $current_attributes_element)
            // Если в массиве элементов уже существует элемент с каким то набором атрибутов
            if ($element_name == $element->getName()) {
                $is_element = true;
                // Поиск не добавленных атрибутов
                foreach ($attributes_array as $attribute_name) {
                    $is_attribute = false;
                    foreach ($current_attributes_element as $current_attribute)
                        if ($current_attribute == $attribute_name)
                            $is_attribute = true;
                    // Добавление атрибута в массив атрибутов, если его нет у данного элемента
                    if ($is_attribute == false)
                        array_push($this->attributes_array[$element->getName()], $attribute_name);
                }
            }
        // Добавление всего набора атрибутов в массив атрибутов, если такого элемента с атрибутами там нет
        if ($is_element == false)
            $this->attributes_array[$element->getName()] = $attributes_array;
    }

    /**
     * Добавление бинарной связи между XML-элементами в массив связей (relations_array).
     * @param $parent_element - родительский элемент (левый элемент связи)
     * @param $child_element - дочерний элемент (правый элемент связи)
     */
    public function addRelationInArray($parent_element, $child_element)
    {
        // Поиск бинарной связи между элементами в массиве связей
        $is_relation = false;
        foreach ($this->relations_array as list($left_element, $right_element))
            if ($left_element == $parent_element and $right_element == $child_element)
                $is_relation = true;
        // Добавление бинарной связи между элементами в массив связей, если ее там нет
        if ($is_relation == false)
            array_push($this->relations_array, array($parent_element, $child_element));
    }

    /**
     * Поиск всех элементов XML-документа, атрибутов и их бинарных связей (поиск в глубину) и создание
     * на их основе массивов элементов, атрибутов и связей метамодели.
     * @param $xml_rows - корневой элемент внутри которого производится поиск
     * @param $namespaces - пространство имен XML-документа
     */
    public function creationMetamodelArrays($xml_rows, $namespaces)
    {
        // Обходим все теги без пространства имен внутри корневого элемента
        foreach ($xml_rows->children() as $child) {
            // Добавление элемента в массив элементов
            self::addElementInArray($child->getName());
            // Добавление атрибутов элемента в массив атрибутов
            self::addAttributesInArray($child, $namespaces);
            // Добавление бинарной связи между элементами в массив связей
            self::addRelationInArray($xml_rows->getName(), $child->getName());
            // Поиск дочерних элементов (если дочернии элементы существуют, то повторно вызываем данную функцию)
            self::creationMetamodelArrays($child, $namespaces);
        }
        // Обходим все теги с пространством имен внутри корневого элемента
        foreach ($namespaces as $prefix => $namespace) {
            foreach ($xml_rows->children($namespaces[$prefix]) as $child) {
                // Добавление элемента в массив элементов
                self::addElementInArray($child->getName());
                // Добавление атрибутов элемента в массив атрибутов
                self::addAttributesInArray($child, $namespaces);
                // Добавление бинарной связи между элементами в массив связей
                self::addRelationInArray($xml_rows->getName(), $child->getName());
                // Поиск дочерних элементов (повторно вызываем данную функцию)
                self::creationMetamodelArrays($child, $namespaces);
            }
        }
    }

    /**
     * Извлечение элементов метамодели из XML-файла концептуальной модели.
     * @param $xml_rows - набор строк XML-документа
     */
    public function extractionMetamodelElements($xml_rows)
    {
        // Получаем все объявленные пространства имен у родительского узла и его дочерних элементов
        $namespaces = $xml_rows->getDocNamespaces(true);
        // Добавляем корневой элемент XML-документа в массив элементов
        array_push($this->elements_array, $xml_rows->getName());
        // Формируем временный массив атрибутов корневого элемента
        $attributes_array = array();
        foreach ($xml_rows->attributes() as $attribute_name => $attribute_value)
            array_push($attributes_array, $attribute_name);
        // Добавляем атрибуты корневого элемента в массив атрибутов
        $this->attributes_array[$xml_rows->getName()] = $attributes_array;
        // Создание массива элементов, атрибутов и связей метамоделей путем рекурсивного поиска в глубину
        self::creationMetamodelArrays($xml_rows, $namespaces);
    }

    /**
     * Сохранение всех извлеченных элементов метамодели в БД.
     * @param $metamodel_id - id метамодели
     */
    public function saveMetamodelElements($metamodel_id)
    {
        // Обход всех элементов метамодели (метаклассов) в массиве всех элементов
        foreach ($this->elements_array as $class_key => $class_value) {
            // Создание новой записи в таблице "Metaclass" (сохранение метакласса)
            $metaclass = new Metaclass();
            $metaclass->name = $class_value;
            $metaclass->metamodel = $metamodel_id;
            $metaclass->save();
            // Обход всех атрибутов элементов метамодели в массиве всех атрибутов элементов
            foreach ($this->attributes_array as $attr_key => $attributes_element)
                foreach ($attributes_element as $attribute)
                    if ($class_value == $attr_key) {
                        // Создание новой записи в таблице "Metaattribute" (сохранение метаатрибута)
                        $metaattribute = new Metaattribute();
                        $metaattribute->name = $attribute;
                        $metaattribute->metaclass = $metaclass->id;
                        $metaattribute->save();
                    }
        }
        // Обход всех связей между элементами метамодели в массиве всех бинарных связей между элементами
        foreach ($this->relations_array as list($left_element, $right_element)) {
            // Поиск левого метакласса принадлежащего данной метамодели
            $left_metaclass = Metaclass::find()
                ->where(array('name' => $left_element, 'metamodel' => $metamodel_id))
                ->one();
            // Поиск правого метакласса принадлежащего данной метамодели
            $right_metaclass = Metaclass::find()
                ->where(array('name' => $right_element, 'metamodel' => $metamodel_id))
                ->one();
            // Создание новой записи в таблице "Metarelation" (сохранение метасвязи между метаклассами)
            $relation = new Metarelation();
            $relation->name = $left_element . '-to-' . $right_element;
            $relation->type = Metarelation::ASSOCIATION;
            $relation->metamodel = $metamodel_id;
            $relation->left_metaclass = $left_metaclass->id;
            $relation->right_metaclass = $right_metaclass->id;
            $relation->save();
        }
    }

    /**
     * Поиск и добавление элемента в цепочку (массив) элементов.
     * @param $elements_chain - текущая цепочка (массив) элементов
     * @param $relations_array - массива пар связей
     * @param $current_element - текущий элемент цепочки
     * @param $last_element - последний элемент цепочки
     * @return mixed
     */
    public function addElementInChain($elements_chain, $relations_array, $current_element, $last_element)
    {
        // Если текущий и последний элемент цепочки не равны
        if ($current_element != $last_element) {
            $exist_element = false;
            // Обход всех пар связей
            foreach ($relations_array as list($left_element, $right_element)) {
                // Если текущий элемент цепочки равен левому элементу из связи
                if ($current_element == $left_element) {
                    // Текущим элементом становится правый элемент из связей
                    $current_element = $right_element;
                    // Добавление текущего элемента в цепочку (массив) элементов
                    array_push($elements_chain, $current_element);
                    $exist_element = true;
                    break;
                }
            }
            // Если при обходе всех пар связей не был найден элемент
            if ($exist_element == false) {
                // Удаление из цепочки (массива) элементов последнего элемента
                $removed_element = array_pop($elements_chain);
                // Если цепочка (массив) элементов не пуста, то текущим элементом становиться ее последний элемент
                if (!empty($elements_chain))
                    $current_element = end($elements_chain);
                // Удаление из цепочки (массива) элементов тупиковой пары связей
                foreach ($relations_array as $key => list($left_element, $right_element))
                    if ($left_element == $current_element && $right_element == $removed_element) {
                        unset($relations_array[$key]);
                        break;
                    }
            }
            // Если цепочка (массив) элементов не пуста, то повторно вызываем данную функцию
            if (!empty($elements_chain))
                $elements_chain = self::addElementInChain($elements_chain, $relations_array,
                    $current_element, $last_element);
        }

        return $elements_chain;
    }

    /**
     * Создание цепочки (массива) элементов от заданных первого и последнего элемента цепочки.
     * @param $current_element - текущий (первый) элемент цепочки
     * @param $last_element - последний элемент цепочки
     * @return array
     */
    public function getElementsChain($current_element, $last_element)
    {
        // Массив (цепочка) элементов
        $elements_chain = array();
        // Добавление текущего, первого элемента в цепочку
        array_push($elements_chain, $current_element);
        // Создание массива пар связей для поиска в нем элементов цепочки
        $relations_array = $this->relations_array;
        // Создание массива (цепочки) элементов путем рекурсивного поиска в глубину
        $elements_chain = self::addElementInChain($elements_chain, $relations_array, $current_element, $last_element);

        return $elements_chain;
    }

    /**
     * Вывод извлеченных элементов, их атрибутов и связей.
     * @return string
     */
    public function inputElements()
    {
        $text = '';
        foreach ($this->elements_array as $key => $value) {
            $text = $text . $value;
            foreach ($this->attributes_array as $attr_key => $attributes_element)
                foreach ($attributes_element as $attribute)
                    if ($value == $attr_key)
                        $text = $text . " [" . $attribute . "],";
            $text = $text . "<br />";
        }
        $text = $text . "<br />";
        foreach ($this->relations_array as list($left_element, $right_element))
            $text = $text . "(" . $left_element . ", " . $right_element . "); ";

        return $text;
    }

    /**
     * Получение массива всех извлеченных элементов.
     * @return array
     */
    public function getElementsArray() {
        return $this->elements_array;
    }

    /**
     * Получение массива всех извлеченных атрибутов элементов.
     * @return array
     */
    public function getAttributesArray() {
        return $this->attributes_array;
    }

    /**
     * Получение массива всех извлеченных связей между элементами.
     * @return array
     */
    public function getRelationsArray() {
        return $this->relations_array;
    }
}