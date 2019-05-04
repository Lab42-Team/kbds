<?php

namespace app\components;

use Yii;
use yii\base\ErrorException;
use app\modules\knowledge_base\models\DataType;
use app\modules\knowledge_base\models\OntologyClass;
use app\modules\knowledge_base\models\Property;
use app\modules\knowledge_base\models\PropertyValue;
use app\modules\knowledge_base\models\Relationship;
use app\modules\knowledge_base\models\RightHandSide;
use app\modules\knowledge_base\models\LeftHandSide;
use app\modules\knowledge_base\models\Object;
use app\modules\knowledge_base\models\ObjectRelationship;

/**
 * OntologyGenerator.
 * Класс OntologyGenerator обеспечивает генерацию (создание) онтологии.
 */
class OntologyGenerator
{
    /**
     * Декодирование текста в кодировку UTF-8.
     * @param $source_string - строка текста в кодировке windows-1252
     * @return string - строка текста в кодировке UTF-8 (юникод)
     */
    public static function decodeText($source_string)
    {
        try {
            $string = iconv('UTF-8', 'CP1252', $source_string);
            $target_string = iconv('CP1251', 'UTF-8', $string);
        } catch (ErrorException $error) {
            $target_string = $source_string;
        }

        return $target_string;
    }

    /**
     * Проверка существования сгенерированных элементов у онтологии.
     * @param $id - идентификатор базы знаний (онтологии)
     * @return bool - результат проверки
     */
    public static function existElements($id)
    {
        // Переменная результата проверки
        $is_exist = false;
        // Поиск всех классов принадлежащих данной онтологии
        $ontology_classes = OntologyClass::find()->where(array('ontology' => $id))->all();
        // Поиск всех связей принадлежащих данной онтологии
        $relationships = Relationship::find()->where(array('ontology' => $id))->all();
        // Поиск всех типов данных принадлежащих данной онтологии
        $data_types = DataType::find()->where(array('knowledge_base' => $id))->all();
        // Если выборка классов или связей или типов данных не пустая, то меняем переменную результата проверки
        if (!empty($ontology_classes) || !empty($relationships) || !empty($data_types))
            $is_exist = true;

        return $is_exist;
    }

    /**
     * Определение наименования нового типа данных.
     * @param $knowledge_base_id - идентификатор базы знаний (продукционной модели)
     * @param $current_name - текущее наименование типа данных
     * @param $result_name - результирующее наименование типа данных
     * @param $index - порядковый номер (индекс) сгенерированного наименования типа данных
     * @return string - сгенерированное новое наименование типа данных
     */
    public static function createDataTypeName($knowledge_base_id, $current_name, $result_name, $index)
    {
        // Поиск типов данных принадлежащих определенной базе знаний и имеющих определенные имена
        $data_types = DataType::find()
            ->where(array('knowledge_base' => $knowledge_base_id, 'name' => $current_name))
            ->all();
        // Генерация нового наименования для типа данных, если тип данных с таким наименованием уже есть
        if (!empty($data_types)) {
            $index++;
            $current_name = $result_name . '-' . $index;
            // Повторный вызов данного метода
            $result_name = self::createDataTypeName($knowledge_base_id, $current_name, $result_name, $index);
        }
        else
            if ($index > 1)
                $result_name .= '-' . $index;

        return $result_name;
    }

    /**
     * Добавление нового типа данных.
     * @param $knowledge_base_id - идентификатор базы знаний (продукционной модели)
     * @param $attribute_values - извлеченные значения атрибутов элемента
     * @return int - id созданного типа данных
     */
    public static function addDataType($knowledge_base_id, $attribute_values)
    {
        $data_type = new DataType();
        foreach ($attribute_values as $attribute_value) {
            if ($attribute_value[0] == 'name')
                // Вызов метода создания наименования для нового типа данных
                $data_type->name = self::createDataTypeName(
                    $knowledge_base_id,
                    self::decodeText($attribute_value[1]),
                    self::decodeText($attribute_value[1]),
                    1
                );
            if ($attribute_value[0] == 'description')
                $data_type->description = self::decodeText($attribute_value[1]);
        }
        $data_type->knowledge_base = $knowledge_base_id;
        $data_type->save();

        return $data_type->id;
    }

    /**
     * Изменение типа данных.
     * @param $data_type_id - идентификатор типа данных
     * @param $attribute_values - извлеченные значения атрибутов элемента
     */
    public static function editDataType($data_type_id, $attribute_values)
    {
        $data_type = DataType::findOne($data_type_id);
        foreach ($attribute_values as $attribute_value) {
            if ($attribute_value[0] == 'name')
                $data_type->name = self::decodeText($attribute_value[1]);
            if ($attribute_value[0] == 'description')
                $data_type->description = self::decodeText($attribute_value[1]);
        }
        $data_type->save();
    }

    /**
     * Определение наименования нового класса онтологии.
     * @param $knowledge_base_id - идентификатор базы знаний (онтологической модели)
     * @param $current_name - текущее наименование класса
     * @param $result_name - результирующее наименование класса
     * @param $index - порядковый номер (индекс) сгенерированного наименования класса
     * @return string - сгенерированное новое наименование класса
     */
    public static function createClassName($knowledge_base_id, $current_name, $result_name, $index)
    {
        // Поиск классов принадлежащих определенной базе знаний и имеющих определенные имена
        $classes = OntologyClass::find()
            ->where(array('ontology' => $knowledge_base_id, 'name' => $current_name))
            ->all();
        // Генерация нового наименования для класса, если класс с таким наименованием уже есть
        if (!empty($classes)) {
            $index++;
            $current_name = $result_name . '-' . $index;
            // Повторный вызов данного метода
            $result_name = self::createClassName($knowledge_base_id, $current_name, $result_name, $index);
        }
        else
            if ($index > 1)
                $result_name .= '-' . $index;

        return $result_name;
    }

    /**
     * Добавление нового класса онтологии.
     * @param $knowledge_base_id - идентификатор базы знаний (онтологической модели)
     * @param $attribute_values - извлеченные значения атрибутов элемента
     * @return int - id созданного класса
     */
    public static function addClass($knowledge_base_id, $attribute_values)
    {
        $class = new OntologyClass();
        foreach ($attribute_values as $attribute_value) {
            if ($attribute_value[0] == 'name')
                // Вызов метода создания наименования для нового класса
                $class->name = self::createClassName(
                    $knowledge_base_id,
                    self::decodeText($attribute_value[1]),
                    self::decodeText($attribute_value[1]),
                    1
                );
            if ($attribute_value[0] == 'description')
                $class->description = self::decodeText($attribute_value[1]);
        }
        $class->ontology = $knowledge_base_id;
        $class->save();

        return $class->id;
    }

    /**
     * Изменение класса онтологии.
     * @param $class_id - идентификатор класса
     * @param $attribute_values - извлеченные значения атрибутов элемента
     */
    public static function editClass($class_id, $attribute_values)
    {
        $class = OntologyClass::findOne($class_id);
        foreach ($attribute_values as $attribute_value) {
            if ($attribute_value[0] == 'name')
                $class->name = self::decodeText($attribute_value[1]);
            if ($attribute_value[0] == 'description')
                $class->description = self::decodeText($attribute_value[1]);
        }
        $class->save();
    }

    /**
     * Определение наименования нового объекта онтологии.
     * @param $knowledge_base_id - идентификатор базы знаний (онтологической модели)
     * @param $current_name - текущее наименование объекта
     * @param $result_name - результирующее наименование объекта
     * @param $index - порядковый номер (индекс) сгенерированного наименования объекта
     * @return string - сгенерированное новое наименование объекта
     */
    public static function createObjectName($knowledge_base_id, $current_name, $result_name, $index)
    {
        // Поиск объектов принадлежащих определенной базе знаний и имеющих определенные имена
        $objects = Object::find()
            ->where(array('ontology' => $knowledge_base_id, 'name' => $current_name))
            ->all();
        // Генерация нового наименования для объекта, если объект с таким наименованием уже есть
        if (!empty($objects)) {
            $index++;
            $current_name = $result_name . '-' . $index;
            // Повторный вызов данного метода
            $result_name = self::createObjectName($knowledge_base_id, $current_name, $result_name, $index);
        }
        else
            if ($index > 1)
                $result_name .= '-' . $index;

        return $result_name;
    }

    /**
     * Добавление нового объекта онтологии.
     * @param $knowledge_base_id - идентификатор базы знаний (онтологической модели)
     * @param $class_id - идентификатор класса
     * @param $attribute_values - извлеченные значения атрибутов элемента
     * @return int - id созданного объекта
     */
    public static function addObject($knowledge_base_id, $class_id, $attribute_values)
    {
        $object = new Object();
        foreach ($attribute_values as $attribute_value) {
            if ($attribute_value[0] == 'name')
                // Вызов метода создания наименования для нового объекта
                $object->name = self::createObjectName(
                    $knowledge_base_id,
                    self::decodeText($attribute_value[1]),
                    self::decodeText($attribute_value[1]),
                    1
                );
            if ($attribute_value[0] == 'description')
                $object->description = self::decodeText($attribute_value[1]);
        }
        $object->ontology = $knowledge_base_id;
        // Создание нового класса, если не задан его id
        if ($class_id == 0) {
            $class = new OntologyClass();
            foreach ($attribute_values as $attribute_value) {
                if ($attribute_value[0] == 'name')
                    // Вызов метода создания наименования для нового класса.
                    // Данное наименование дублирует наименование объекта
                    $class->name = self::createClassName(
                        $knowledge_base_id,
                        self::decodeText($attribute_value[1]),
                        self::decodeText($attribute_value[1]),
                        1
                    );
            }
            $class->description = Yii::t('app',
                'CLASS_MODEL_DESCRIPTION_FOR_AUTOMATICALLY_CREATED_CLASS');
            $class->ontology = $knowledge_base_id;
            $class->save();
            $object->ontology_class = $class->id;
        }
        else
            $object->ontology_class = $class_id;
        $object->save();

        return $object->id;
    }

    /**
     * Изменение объекта онтологии.
     * @param $object_id - идентификатор объекта
     * @param $attribute_values - извлеченные значения атрибутов элемента
     */
    public static function editObject($object_id, $attribute_values)
    {
        $object = Object::findOne($object_id);
        foreach ($attribute_values as $attribute_value) {
            if ($attribute_value[0] == 'name')
                $object->name = self::decodeText($attribute_value[1]);
            if ($attribute_value[0] == 'description')
                $object->description = self::decodeText($attribute_value[1]);
        }
        $object->save();
    }

    /**
     * Определение наименования нового отношения.
     * @param $knowledge_base_id - идентификатор базы знаний (онтологической модели)
     * @param $current_name - текущее наименование отношения
     * @param $result_name - результирующее наименование отношения
     * @param $index - порядковый номер (индекс) сгенерированного наименования отношения
     * @return string - сгенерированное новое наименование отношения
     */
    public static function createRelationshipName($knowledge_base_id, $current_name, $result_name, $index)
    {
        // Поиск отношений принадлежащих определенной базе знаний и имеющих определенные имена
        $relationships = Relationship::find()
            ->where(array('ontology' => $knowledge_base_id, 'name' => $current_name))
            ->all();
        // Генерация нового наименования для отношения, если объект с таким наименованием уже есть
        if (!empty($relationships)) {
            $index++;
            $current_name = $result_name . '-' . $index;
            // Повторный вызов данного метода
            $result_name = self::createRelationshipName($knowledge_base_id, $current_name, $result_name, $index);
        }
        else
            if ($index > 1)
                $result_name .= '-' . $index;

        return $result_name;
    }

    /**
     * Добавление нового отношения.
     * @param $knowledge_base_id - идентификатор базы знаний (онтологической модели)
     * @param $attribute_values - извлеченные значения атрибутов элемента
     * @return int - id созданного отношения
     */
    public static function addRelationship($knowledge_base_id, $attribute_values)
    {
        $relationship = new Relationship();
        $relationship->is_association = true;
        $relationship->is_inheritance = false;
        $relationship->is_equivalence = false;
        foreach ($attribute_values as $attribute_value) {
            if ($attribute_value[0] == 'name')
                // Вызов метода создания наименования для нового отношения
                $relationship->name = self::createRelationshipName(
                    $knowledge_base_id,
                    self::decodeText($attribute_value[1]),
                    self::decodeText($attribute_value[1]),
                    1
                );
            if ($attribute_value[0] == 'description')
                $relationship->description = self::decodeText($attribute_value[1]);
            if ($attribute_value[0] == 'is_association')
                $relationship->is_association = self::decodeText($attribute_value[1]);
            if ($attribute_value[0] == 'is_inheritance')
                $relationship->is_inheritance = self::decodeText($attribute_value[1]);
            if ($attribute_value[0] == 'is_equivalence')
                $relationship->is_equivalence = self::decodeText($attribute_value[1]);
        }
        $relationship->ontology = $knowledge_base_id;
        $relationship->save();

        return $relationship->id;
    }

    /**
     * Изменение отношения.
     * @param $relationship_id - идентификатор отношения
     * @param $attribute_values - извлеченные значения атрибутов элемента
     */
    public static function editRelationship($relationship_id, $attribute_values)
    {
        $relationship = Relationship::findOne($relationship_id);
        $relationship->is_association = true;
        $relationship->is_inheritance = false;
        $relationship->is_equivalence = false;
        foreach ($attribute_values as $attribute_value) {
            if ($attribute_value[0] == 'name')
                $relationship->name = self::decodeText($attribute_value[1]);
            if ($attribute_value[0] == 'description')
                $relationship->description = self::decodeText($attribute_value[1]);
            if ($attribute_value[0] == 'is_association')
                $relationship->is_association = self::decodeText($attribute_value[1]);
            if ($attribute_value[0] == 'is_inheritance')
                $relationship->is_inheritance = self::decodeText($attribute_value[1]);
            if ($attribute_value[0] == 'is_equivalence')
                $relationship->is_equivalence = self::decodeText($attribute_value[1]);
        }
        $relationship->save();
    }

    /**
     * Определение наименования нового свойства для класса.
     * @param $class_id - идентификатор класса
     * @param $data_type_id - идентификатор типа данных
     * @param $current_name - текущее наименование свойства класса
     * @param $result_name - результирующее наименование свойства класса
     * @param $index - порядковый номер (индекс) сгенерированного наименования свойства класса
     * @return string - сгенерированное новое наименование свойства класса
     */
    public static function createPropertyName($class_id, $data_type_id, $current_name, $result_name, $index)
    {
        // Поиск свойств принадлежащих определенному классу и типу данных, а также имеющих определенные имена
        $properties = Property::find()
            ->where(array('ontology_class' => $class_id, 'data_type' => $data_type_id, 'name' => $current_name))
            ->all();
        // Генерация нового наименования для свойства класса, если свойство класса с таким наименованием уже есть
        if (!empty($properties)) {
            $index++;
            $current_name = $result_name . '-' . $index;
            // Повторный вызов данного метода
            $result_name = self::createPropertyName(
                $class_id,
                $data_type_id,
                $current_name,
                $result_name,
                $index
            );
        }
        else
            if ($index > 1)
                $result_name .= '-' . $index;

        return $result_name;
    }

    /**
     * Добавление нового свойства для класса.
     * @param $knowledge_base_id - идентификатор базы знаний (онтологической модели)
     * @param $class_id - идентификатор класса
     * @param $data_type_id - идентификатор типа данных
     * @param $attribute_values - извлеченные значения атрибутов элемента
     * @return int - id созданного свойства класса
     */
    public static function addProperty($knowledge_base_id, $class_id, $data_type_id, $attribute_values)
    {
        $property = new Property();
        foreach ($attribute_values as $attribute_value) {
            if ($attribute_value[0] == 'name')
                // Вызов метода создания наименования для нового свойства класса
                $property->name = self::createPropertyName(
                    $class_id,
                    $data_type_id,
                    self::decodeText($attribute_value[1]),
                    self::decodeText($attribute_value[1]),
                    1
                );
            if ($attribute_value[0] == 'description')
                $property->description = self::decodeText($attribute_value[1]);
        }
        $property->ontology_class = $class_id;
        // Создание нового типа данных, если не задан его id
        if ($data_type_id == 0) {
            // Поиск типа данных по умолчанию принадлежащий данной базе знаний
            $data_type = DataType::find()
                ->where(array('knowledge_base' => $knowledge_base_id, 'name' => 'Variable'))
                ->one();
            // Создание типа данных по умолчанию, если он не был найден
            if (empty($data_type)) {
                $new_data_type = new DataType();
                $new_data_type->name = 'Variable';
                $new_data_type->description = Yii::t('app', 'DATA_TYPE_MODEL_DEFAULT_DATA_TYPE_DESCRIPTION');
                $new_data_type->knowledge_base = $knowledge_base_id;
                $new_data_type->save();
                $property->data_type = $new_data_type->id;
            }
            else
                $property->data_type = $data_type->id;
        }
        else
            $property->data_type = $data_type_id;
        $property->save();

        return $property->id;
    }

    /**
     * Изменение свойства класса.
     * @param $property_id - идентификатор свойства класса
     * @param $attribute_values - извлеченные значения атрибутов элемента
     */
    public static function editProperty($property_id, $attribute_values)
    {
        $property = Property::findOne($property_id);
        foreach ($attribute_values as $attribute_value) {
            if ($attribute_value[0] == 'name')
                $property->name = self::decodeText($attribute_value[1]);
            if ($attribute_value[0] == 'description')
                $property->description = self::decodeText($attribute_value[1]);
        }
        $property->save();
    }

    /**
     * Определение наименования нового значения для свойства класса.
     * @param $property_id - идентификатор свойства класса
     * @param $object_id - идентификатор объекта
     * @param $current_name - текущее наименование значения свойства
     * @param $result_name - результирующее наименование значения свойства
     * @param $index - порядковый номер (индекс) сгенерированного наименования значения свойства
     * @return string - сгенерированное новое наименование значения свойства
     */
    public static function createPropertyValueName($property_id, $object_id, $current_name, $result_name, $index)
    {
        // Поиск значений свойств принадлежащих определенному свойству и объекту, а также имеющих определенные имена
        $property_values = PropertyValue::find()
            ->where(array('property' => $property_id, 'object' => $object_id, 'name' => $current_name))
            ->all();
        // Генерация нового наименования для значения свойства, если значение свойства с таким наименованием уже есть
        if (!empty($property_values)) {
            $index++;
            $current_name = $result_name . '-' . $index;
            // Повторный вызов данного метода
            $result_name = self::createPropertyValueName(
                $property_id,
                $object_id,
                $current_name,
                $result_name,
                $index
            );
        }
        else
            if ($index > 1)
                $result_name .= '-' . $index;

        return $result_name;
    }

    /**
     * Добавление нового значения для свойства класса.
     * @param $property_id - идентификатор свойства класса
     * @param $object_id - идентификатор объекта
     * @param $attribute_values - извлеченные значения атрибутов элемента
     * @return int - id созданного значения свойства
     */
    public static function addPropertyValue($property_id, $object_id, $attribute_values)
    {
        $property_value = new PropertyValue();
        foreach ($attribute_values as $attribute_value) {
            if ($attribute_value[0] == 'name')
                // Вызов метода создания наименования для нового значения свойства
                $property_value->name = self::createPropertyValueName(
                    $property_id,
                    $object_id,
                    self::decodeText($attribute_value[1]),
                    self::decodeText($attribute_value[1]),
                    1
                );
        }
        $property_value->property = $property_id;
        $property_value->object = $object_id;
        $property_value->save();

        return $property_value->id;
    }

    /**
     * Изменение значения свойства класса.
     * @param $property_value_id - идентификатор значения свойства класса
     * @param $attribute_values - извлеченные значения атрибутов элемента
     */
    public static function editPropertyValue($property_value_id, $attribute_values)
    {
        $property_value = PropertyValue::findOne($property_value_id);
        foreach ($attribute_values as $attribute_value) {
            if ($attribute_value[0] == 'name')
                $property_value->name = self::decodeText($attribute_value[1]);
        }
        $property_value->save();
    }

    /**
     * Добавление левой части отношения.
     * @param $relationship_id - идентификатор отношения
     * @param $class_id - идентификатор класса
     * @return int - id созданной левой части отношения
     */
    public static function addLeftHandSide($relationship_id, $class_id)
    {
        $left_hand_side = new LeftHandSide();
        $left_hand_side->relationship = $relationship_id;
        $left_hand_side->ontology_class = $class_id;
        $left_hand_side->save();

        return $left_hand_side->id;
    }

    /**
     * Изменение левой части отношения.
     * @param $left_hand_side_id - идентификатор левой части отношения
     * @param $relationship_id - идентификатор отношения
     * @param $class_id - идентификатор класса
     */
    public static function editLeftHandSide($left_hand_side_id, $relationship_id, $class_id)
    {
        $left_hand_side = LeftHandSide::findOne($left_hand_side_id);
        $left_hand_side->relationship = $relationship_id;
        $left_hand_side->ontology_class = $class_id;
        $left_hand_side->save();
    }

    /**
     * Добавление правой части отношения.
     * @param $relationship_id - идентификатор отношения
     * @param $class_id - идентификатор класса
     * @return int - id созданной правой части отношения
     */
    public static function addRightHandSide($relationship_id, $class_id)
    {
        $right_hand_side = new RightHandSide();
        $right_hand_side->relationship = $relationship_id;
        $right_hand_side->ontology_class = $class_id;
        $right_hand_side->save();

        return $right_hand_side->id;
    }

    /**
     * Изменение правой части отношения.
     * @param $right_hand_side_id - идентификатор правой части отношения
     * @param $relationship_id - идентификатор отношения
     * @param $class_id - идентификатор класса
     */
    public static function editRightHandSide($right_hand_side_id, $relationship_id, $class_id)
    {
        $right_hand_side = RightHandSide::findOne($right_hand_side_id);
        $right_hand_side->relationship = $relationship_id;
        $right_hand_side->ontology_class = $class_id;
        $right_hand_side->save();
    }

    /**
     * Определение наименования нового отношения объекта.
     * @param $relationship_id - идентификатор отношения классов
     * @param $object_id - идентификатор объекта
     * @param $current_name - текущее наименование отношения объекта
     * @param $result_name - результирующее наименование отношения объекта
     * @param $index - порядковый номер (индекс) сгенерированного наименования отношения объекта
     * @return string - сгенерированное новое наименование отношения объекта
     */
    public static function createObjectRelationshipName($relationship_id, $object_id, $current_name,
                                                        $result_name, $index)
    {
        // Поиск отношений объектов принадлежащих определенному отношенияю и объекту, и имеющих определенные имена
        $object_relationships = ObjectRelationship::find()
            ->where(array('relationship' => $relationship_id, 'object' => $object_id, 'name' => $current_name))
            ->all();
        // Генерация нового наименования для отношения объекта, если отношение объекта с таким наименованием уже есть
        if (!empty($object_relationships)) {
            $index++;
            $current_name = $result_name . '-' . $index;
            // Повторный вызов данного метода
            $result_name = self::createObjectRelationshipName($relationship_id, $object_id, $current_name,
                $result_name, $index);
        }
        else
            if ($index > 1)
                $result_name .= '-' . $index;

        return $result_name;
    }

    /**
     * Добавление нового отношения объекта.
     * @param $relationship_id - идентификатор отношения классов
     * @param $object_id - идентификатор объекта
     * @param $attribute_values - извлеченные значения атрибутов элемента
     * @return int - id созданного отношения объекта
     */
    public static function addObjectRelationship($relationship_id, $object_id, $attribute_values)
    {
        $object_relationship = new ObjectRelationship();
        foreach ($attribute_values as $attribute_value) {
            if ($attribute_value[0] == 'name')
                // Вызов метода создания наименования для нового класса
                $object_relationship->name = self::createObjectRelationshipName(
                    $relationship_id,
                    $object_id,
                    self::decodeText($attribute_value[1]),
                    self::decodeText($attribute_value[1]),
                    1
                );
        }
        $object_relationship->relationship = $relationship_id;
        $object_relationship->object = $object_id;
        $object_relationship->save();

        return $object_relationship->id;
    }

    /**
     * Изменение класса отношения объекта.
     * @param $object_relationship_id - идентификатор отношения объекта
     * @param $attribute_values - извлеченные значения атрибутов элемента
     */
    public static function editObjectRelationship($object_relationship_id, $attribute_values)
    {
        $object_relationship = ObjectRelationship::findOne($object_relationship_id);
        foreach ($attribute_values as $attribute_value) {
            if ($attribute_value[0] == 'name')
                $object_relationship->name = self::decodeText($attribute_value[1]);
        }
        $object_relationship->save();
    }
}