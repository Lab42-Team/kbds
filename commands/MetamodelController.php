<?php

namespace app\commands;

use app\modules\software_component\models\Metamodel;
use app\modules\software_component\models\Metaclass;
use app\modules\software_component\models\Metaattribute;
use app\modules\software_component\models\Metarelation;
use yii\console\Controller;
use yii\helpers\Console;
use yii\console\Exception;

/**
 * MetamodelController реализует консольные команды для работы с метамоделями.
 * Создание метамоделей (онтология, продукций, CLIPS, OWL).
 */
class MetamodelController extends Controller
{
    /**
     * Инициализация команд.
     */
    public function actionIndex()
    {
        echo 'yii metamodel/create' . PHP_EOL;
        echo 'yii metamodel/remove' . PHP_EOL;
        echo 'yii metamodel/all-remove' . PHP_EOL;
    }

    /**
     * Команда создания метамоделей (онтология, продукций, CLIPS, OWL,
     * а также концептуальная модель XMI UML и концепт-карта в формате XTM).
     */
    public function actionCreate()
    {
        $ontology_metamodel = new Metamodel();
        // Если не создана еще ни одна метамодель
        if ($ontology_metamodel->find()->count() == 0) {
            // Создание метамодели онтологии
            $ontology_metamodel->name = 'Метамодель онтологии';
            $ontology_metamodel->description = 'Внутреннее представление знаний системы KBDS в виде онтологии.';
            $ontology_metamodel->type = Metamodel::DEFAULT_TYPE;
            $ontology_metamodel->author = 1;
            $this->log($ontology_metamodel->save());

            // Создание метакласса "Онтология"
            $ontology = new Metaclass();
            $ontology->name = 'Ontology';
            $ontology->metamodel = $ontology_metamodel->id;
            $this->log($ontology->save());
            // Создание метаатрибутов для метакласса "Онтология"
            $ontology_attribute_one = new Metaattribute();
            $ontology_attribute_one->name = 'name';
            $ontology_attribute_one->metaclass = $ontology->id;
            $this->log($ontology_attribute_one->save());
            $ontology_attribute_two = new Metaattribute();
            $ontology_attribute_two->name = 'description';
            $ontology_attribute_two->metaclass = $ontology->id;
            $this->log($ontology_attribute_two->save());

            // Создание метакласса "Тип данных"
            $data_type = new Metaclass();
            $data_type->name = 'DataType';
            $data_type->metamodel = $ontology_metamodel->id;
            $this->log($data_type->save());
            // Создание метаатрибутов для метакласса "Тип данных"
            $data_type_attribute_one = new Metaattribute();
            $data_type_attribute_one->name = 'name';
            $data_type_attribute_one->metaclass = $data_type->id;
            $this->log($data_type_attribute_one->save());
            $data_type_attribute_two = new Metaattribute();
            $data_type_attribute_two->name = 'description';
            $data_type_attribute_two->metaclass = $data_type->id;
            $this->log($data_type_attribute_two->save());

            // Создание метакласса "Класс"
            $class = new Metaclass();
            $class->name = 'Class';
            $class->metamodel = $ontology_metamodel->id;
            $this->log($class->save());
            // Создание метаатрибутов для метакласса "Класс"
            $class_attribute_one = new Metaattribute();
            $class_attribute_one->name = 'name';
            $class_attribute_one->metaclass = $class->id;
            $this->log($class_attribute_one->save());
            $class_attribute_two = new Metaattribute();
            $class_attribute_two->name = 'description';
            $class_attribute_two->metaclass = $class->id;
            $this->log($class_attribute_two->save());

            // Создание метакласса "Объект"
            $object = new Metaclass();
            $object->name = 'Object';
            $object->metamodel = $ontology_metamodel->id;
            $this->log($object->save());
            // Создание метаатрибутов для метакласса "Объект"
            $object_attribute_one = new Metaattribute();
            $object_attribute_one->name = 'name';
            $object_attribute_one->metaclass = $object->id;
            $this->log($object_attribute_one->save());
            $object_attribute_two = new Metaattribute();
            $object_attribute_two->name = 'description';
            $object_attribute_two->metaclass = $object->id;
            $this->log($object_attribute_two->save());

            // Создание метакласса "Отношение"
            $relationship = new Metaclass();
            $relationship->name = 'Relationship';
            $relationship->metamodel = $ontology_metamodel->id;
            $this->log($relationship->save());
            // Создание метаатрибутов для метакласса "Отношение"
            $relationship_attribute_one = new Metaattribute();
            $relationship_attribute_one->name = 'name';
            $relationship_attribute_one->metaclass = $relationship->id;
            $this->log($relationship_attribute_one->save());
            $relationship_attribute_two = new Metaattribute();
            $relationship_attribute_two->name = 'description';
            $relationship_attribute_two->metaclass = $relationship->id;
            $this->log($relationship_attribute_two->save());
            $relationship_attribute_three = new Metaattribute();
            $relationship_attribute_three->name = 'isAssociation';
            $relationship_attribute_three->type = 'boolean';
            $relationship_attribute_three->value = 'false';
            $relationship_attribute_three->metaclass = $relationship->id;
            $this->log($relationship_attribute_three->save());
            $relationship_attribute_four = new Metaattribute();
            $relationship_attribute_four->name = 'isInheritance';
            $relationship_attribute_four->type = 'boolean';
            $relationship_attribute_four->value = 'false';
            $relationship_attribute_four->metaclass = $relationship->id;
            $this->log($relationship_attribute_four->save());
            $relationship_attribute_five = new Metaattribute();
            $relationship_attribute_five->name = 'isEquivalence';
            $relationship_attribute_five->type = 'boolean';
            $relationship_attribute_five->value = 'false';
            $relationship_attribute_five->metaclass = $relationship->id;
            $this->log($relationship_attribute_five->save());

            // Создание метакласса "Левый класс отношения"
            $left_hand_side = new Metaclass();
            $left_hand_side->name = 'LeftHandSide';
            $left_hand_side->metamodel = $ontology_metamodel->id;
            $this->log($left_hand_side->save());

            // Создание метакласса "Правый класс отношения"
            $right_hand_side = new Metaclass();
            $right_hand_side->name = 'RightHandSide';
            $right_hand_side->metamodel = $ontology_metamodel->id;
            $this->log($right_hand_side->save());

            // Создание метакласса "Свойство"
            $property = new Metaclass();
            $property->name = 'Property';
            $property->metamodel = $ontology_metamodel->id;
            $this->log($property->save());
            // Создание метаатрибутов для метакласса "Свойство"
            $property_attribute_one = new Metaattribute();
            $property_attribute_one->name = 'name';
            $property_attribute_one->metaclass = $property->id;
            $this->log($property_attribute_one->save());
            $property_attribute_two = new Metaattribute();
            $property_attribute_two->name = 'description';
            $property_attribute_two->metaclass = $property->id;
            $this->log($property_attribute_two->save());

            // Создание метакласса "Значение свойства"
            $property_value = new Metaclass();
            $property_value->name = 'PropertyValue';
            $property_value->metamodel = $ontology_metamodel->id;
            $this->log($property_value->save());
            // Создание метаатрибутов для метакласса "Значение свойства"
            $property_value_attribute = new Metaattribute();
            $property_value_attribute->name = 'name';
            $property_value_attribute->metaclass = $property_value->id;
            $this->log($property_value_attribute->save());

            // Создание метакласса "Отношение объекта"
            $object_relationship = new Metaclass();
            $object_relationship->name = 'ObjectRelationship';
            $object_relationship->metamodel = $ontology_metamodel->id;
            $this->log($object_relationship->save());
            // Создание метаатрибутов для метакласса "Отношение объекта"
            $object_relationship_attribute_one = new Metaattribute();
            $object_relationship_attribute_one->name = 'name';
            $object_relationship_attribute_one->metaclass = $object_relationship->id;
            $this->log($object_relationship_attribute_one->save());
            $object_relationship_attribute_two = new Metaattribute();
            $object_relationship_attribute_two->name = 'description';
            $object_relationship_attribute_two->metaclass = $object_relationship->id;
            $this->log($object_relationship_attribute_two->save());

            // Создание метасвязи между метаклассами "Онтология" и "Класс"
            $ontology_to_class = new Metarelation();
            $ontology_to_class->name = 'Ontology-to-Class';
            $ontology_to_class->type = Metarelation::ASSOCIATION;
            $ontology_to_class->metamodel = $ontology_metamodel->id;
            $ontology_to_class->left_metaclass = $ontology->id;
            $ontology_to_class->right_metaclass = $class->id;
            $this->log($ontology_to_class->save());
            // Создание метасвязи между метаклассами "Онтология" и "Объект"
            $ontology_to_object = new Metarelation();
            $ontology_to_object->name = 'Ontology-to-Object';
            $ontology_to_object->type = Metarelation::ASSOCIATION;
            $ontology_to_object->metamodel = $ontology_metamodel->id;
            $ontology_to_object->left_metaclass = $ontology->id;
            $ontology_to_object->right_metaclass = $object->id;
            $this->log($ontology_to_object->save());
            // Создание метасвязи между метаклассами "Онтология" и "Отношение"
            $object_to_relationship = new Metarelation();
            $object_to_relationship->name = 'Ontology-to-Relationship';
            $object_to_relationship->type = Metarelation::ASSOCIATION;
            $object_to_relationship->metamodel = $ontology_metamodel->id;
            $object_to_relationship->left_metaclass = $ontology->id;
            $object_to_relationship->right_metaclass = $relationship->id;
            $this->log($object_to_relationship->save());
            // Создание метасвязи между метаклассами "Онтология" и "Тип данных"
            $ontology_to_data_type = new Metarelation();
            $ontology_to_data_type->name = 'Ontology-to-DataType';
            $ontology_to_data_type->type = Metarelation::ASSOCIATION;
            $ontology_to_data_type->metamodel = $ontology_metamodel->id;
            $ontology_to_data_type->left_metaclass = $ontology->id;
            $ontology_to_data_type->right_metaclass = $data_type->id;
            $this->log($ontology_to_data_type->save());
            // Создание метасвязи между метаклассами "Класс" и "Объект"
            $class_to_object = new Metarelation();
            $class_to_object->name = 'Class-to-Object';
            $class_to_object->type = Metarelation::ASSOCIATION;
            $class_to_object->metamodel = $ontology_metamodel->id;
            $class_to_object->left_metaclass = $class->id;
            $class_to_object->right_metaclass = $object->id;
            $this->log($class_to_object->save());
            // Создание метасвязи между метаклассами "Отношение" и "Левый класс отношения"
            $relationship_to_left_hand_side = new Metarelation();
            $relationship_to_left_hand_side->name = 'Relationship-to-LeftHandSide';
            $relationship_to_left_hand_side->type = Metarelation::ASSOCIATION;
            $relationship_to_left_hand_side->metamodel = $ontology_metamodel->id;
            $relationship_to_left_hand_side->left_metaclass = $relationship->id;
            $relationship_to_left_hand_side->right_metaclass = $left_hand_side->id;
            $this->log($relationship_to_left_hand_side->save());
            // Создание метасвязи между метаклассами "Отношение" и "Правый класс отношения"
            $relationship_to_right_hand_side = new Metarelation();
            $relationship_to_right_hand_side->name = 'Relationship-to-RightHandSide';
            $relationship_to_right_hand_side->type = Metarelation::ASSOCIATION;
            $relationship_to_right_hand_side->metamodel = $ontology_metamodel->id;
            $relationship_to_right_hand_side->left_metaclass = $relationship->id;
            $relationship_to_right_hand_side->right_metaclass = $right_hand_side->id;
            $this->log($relationship_to_right_hand_side->save());
            // Создание метасвязи между метаклассами "Класс" и "Левый класс отношения"
            $class_to_left_hand_side = new Metarelation();
            $class_to_left_hand_side->name = 'Class-to-LeftHandSide';
            $class_to_left_hand_side->type = Metarelation::ASSOCIATION;
            $class_to_left_hand_side->metamodel = $ontology_metamodel->id;
            $class_to_left_hand_side->left_metaclass = $class->id;
            $class_to_left_hand_side->right_metaclass = $left_hand_side->id;
            $this->log($class_to_left_hand_side->save());
            // Создание метасвязи между метаклассами "Класс" и "Правый класс отношения"
            $class_to_right_hand_side = new Metarelation();
            $class_to_right_hand_side->name = 'Class-to-RightHandSide';
            $class_to_right_hand_side->type = Metarelation::ASSOCIATION;
            $class_to_right_hand_side->metamodel = $ontology_metamodel->id;
            $class_to_right_hand_side->left_metaclass = $class->id;
            $class_to_right_hand_side->right_metaclass = $right_hand_side->id;
            $this->log($class_to_right_hand_side->save());
            // Создание метасвязи между метаклассами "Класс" и "Свойство"
            $class_to_property = new Metarelation();
            $class_to_property->name = 'Class-to-Property';
            $class_to_property->type = Metarelation::ASSOCIATION;
            $class_to_property->metamodel = $ontology_metamodel->id;
            $class_to_property->left_metaclass = $class->id;
            $class_to_property->right_metaclass = $property->id;
            $this->log($class_to_property->save());
            // Создание метасвязи между метаклассами "Тип данных" и "Свойство"
            $data_type_to_property = new Metarelation();
            $data_type_to_property->name = 'DataType-to-Property';
            $data_type_to_property->type = Metarelation::ASSOCIATION;
            $data_type_to_property->metamodel = $ontology_metamodel->id;
            $data_type_to_property->left_metaclass = $data_type->id;
            $data_type_to_property->right_metaclass = $property->id;
            $this->log($data_type_to_property->save());
            // Создание метасвязи между метаклассами "Свойство" и "Значение свойства"
            $property_to_property_value = new Metarelation();
            $property_to_property_value->name = 'Property-to-PropertyValue';
            $property_to_property_value->type = Metarelation::ASSOCIATION;
            $property_to_property_value->metamodel = $ontology_metamodel->id;
            $property_to_property_value->left_metaclass = $property->id;
            $property_to_property_value->right_metaclass = $property_value->id;
            $this->log($property_to_property_value->save());
            // Создание метасвязи между метаклассами "Объект" и "Значение свойства"
            $object_to_property_value = new Metarelation();
            $object_to_property_value->name = 'Object-to-PropertyValue';
            $object_to_property_value->type = Metarelation::ASSOCIATION;
            $object_to_property_value->metamodel = $ontology_metamodel->id;
            $object_to_property_value->left_metaclass = $object->id;
            $object_to_property_value->right_metaclass = $property_value->id;
            $this->log($object_to_property_value->save());
            // Создание метасвязи между метаклассами "Отношение" и "Отношение объекта"
            $relationship_to_object_relationship = new Metarelation();
            $relationship_to_object_relationship->name = 'Relationship-to-ObjectRelationship';
            $relationship_to_object_relationship->type = Metarelation::ASSOCIATION;
            $relationship_to_object_relationship->metamodel = $ontology_metamodel->id;
            $relationship_to_object_relationship->left_metaclass = $relationship->id;
            $relationship_to_object_relationship->right_metaclass = $object_relationship->id;
            $this->log($relationship_to_object_relationship->save());
            // Создание метасвязи между метаклассами "Объект" и "Отношение объекта"
            $object_to_object_relationship = new Metarelation();
            $object_to_object_relationship->name = 'Object-to-ObjectRelationship';
            $object_to_object_relationship->type = Metarelation::ASSOCIATION;
            $object_to_object_relationship->metamodel = $ontology_metamodel->id;
            $object_to_object_relationship->left_metaclass = $object->id;
            $object_to_object_relationship->right_metaclass = $object_relationship->id;
            $this->log($object_to_object_relationship->save());

            // Создание метамодели продукций
            $rules_metamodel = new Metamodel();
            $rules_metamodel->name = 'Метамодель продукций';
            $rules_metamodel->description = 'Внутреннее представление знаний системы KBDS в виде продукций.';
            $rules_metamodel->type = Metamodel::DEFAULT_TYPE;
            $rules_metamodel->author = 1;
            $this->log($rules_metamodel->save());

            // Создание метакласса "Продукционная модель"
            $production_model = new Metaclass();
            $production_model->name = 'ProductionModel';
            $production_model->metamodel = $rules_metamodel->id;
            $this->log($production_model->save());
            // Создание метаатрибутов для метакласса "Продукционная модель"
            $production_model_attribute_one = new Metaattribute();
            $production_model_attribute_one->name = 'name';
            $production_model_attribute_one->metaclass = $production_model->id;
            $this->log($production_model_attribute_one->save());
            $production_model_attribute_two = new Metaattribute();
            $production_model_attribute_two->name = 'description';
            $production_model_attribute_two->metaclass = $production_model->id;
            $this->log($production_model_attribute_two->save());

            // Создание метакласса "Тип данных"
            $data_type = new Metaclass();
            $data_type->name = 'DataType';
            $data_type->metamodel = $rules_metamodel->id;
            $this->log($data_type->save());
            // Создание метаатрибутов для метакласса "Тип данных"
            $data_type_attribute_one = new Metaattribute();
            $data_type_attribute_one->name = 'name';
            $data_type_attribute_one->metaclass = $data_type->id;
            $this->log($data_type_attribute_one->save());
            $data_type_attribute_two = new Metaattribute();
            $data_type_attribute_two->name = 'description';
            $data_type_attribute_two->metaclass = $data_type->id;
            $this->log($data_type_attribute_two->save());

            // Создание метакласса "Шаблон факта"
            $fact_template = new Metaclass();
            $fact_template->name = 'FactTemplate';
            $fact_template->metamodel = $rules_metamodel->id;
            $this->log($fact_template->save());
            // Создание метаатрибутов для метакласса "Шаблон факта"
            $fact_template_attribute_one = new Metaattribute();
            $fact_template_attribute_one->name = 'name';
            $fact_template_attribute_one->metaclass = $fact_template->id;
            $this->log($fact_template_attribute_one->save());
            $fact_template_attribute_two = new Metaattribute();
            $fact_template_attribute_two->name = 'description';
            $fact_template_attribute_two->metaclass = $fact_template->id;
            $this->log($fact_template_attribute_two->save());

            // Создание метакласса "Слот шаблона факта"
            $fact_template_slot = new Metaclass();
            $fact_template_slot->name = 'FactTemplateSlot';
            $fact_template_slot->metamodel = $rules_metamodel->id;
            $this->log($fact_template_slot->save());
            // Создание метаатрибутов для метакласса "Слот шаблона факта"
            $fact_template_slot_attribute_one = new Metaattribute();
            $fact_template_slot_attribute_one->name = 'name';
            $fact_template_slot_attribute_one->metaclass = $fact_template_slot->id;
            $this->log($fact_template_slot_attribute_one->save());
            $fact_template_slot_attribute_two = new Metaattribute();
            $fact_template_slot_attribute_two->name = 'defaultValue';
            $fact_template_slot_attribute_two->metaclass = $fact_template_slot->id;
            $this->log($fact_template_slot_attribute_two->save());
            $fact_template_slot_attribute_three = new Metaattribute();
            $fact_template_slot_attribute_three->name = 'description';
            $fact_template_slot_attribute_three->metaclass = $fact_template_slot->id;
            $this->log($fact_template_slot_attribute_three->save());

            // Создание метакласса "Факт"
            $fact = new Metaclass();
            $fact->name = 'Fact';
            $fact->metamodel = $rules_metamodel->id;
            $this->log($fact->save());
            // Создание метаатрибутов для метакласса "Факт"
            $fact_attribute_one = new Metaattribute();
            $fact_attribute_one->name = 'name';
            $fact_attribute_one->metaclass = $fact->id;
            $this->log($fact_attribute_one->save());
            $fact_attribute_two = new Metaattribute();
            $fact_attribute_two->name = 'initial';
            $fact_attribute_two->metaclass = $fact->id;
            $this->log($fact_attribute_two->save());
            $fact_attribute_three = new Metaattribute();
            $fact_attribute_three->name = 'certaintyFactor';
            $fact_attribute_three->metaclass = $fact->id;
            $this->log($fact_attribute_three->save());
            $fact_attribute_four = new Metaattribute();
            $fact_attribute_four->name = 'description';
            $fact_attribute_four->metaclass = $fact->id;
            $this->log($fact_attribute_four->save());

            // Создание метакласса "Слот факта"
            $fact_slot = new Metaclass();
            $fact_slot->name = 'FactSlot';
            $fact_slot->metamodel = $rules_metamodel->id;
            $this->log($fact_slot->save());
            // Создание метаатрибутов для метакласса "Слот факта"
            $fact_slot_attribute_one = new Metaattribute();
            $fact_slot_attribute_one->name = 'name';
            $fact_slot_attribute_one->metaclass = $fact_slot->id;
            $this->log($fact_slot_attribute_one->save());
            $fact_slot_attribute_two = new Metaattribute();
            $fact_slot_attribute_two->name = 'value';
            $fact_slot_attribute_two->metaclass = $fact_slot->id;
            $this->log($fact_slot_attribute_two->save());
            $fact_slot_attribute_three = new Metaattribute();
            $fact_slot_attribute_three->name = 'description';
            $fact_slot_attribute_three->metaclass = $fact_slot->id;
            $this->log($fact_slot_attribute_three->save());

            // Создание метакласса "Шаблон правила"
            $rule_template = new Metaclass();
            $rule_template->name = 'RuleTemplate';
            $rule_template->metamodel = $rules_metamodel->id;
            $this->log($rule_template->save());
            // Создание метаатрибутов для метакласса "Шаблон правила"
            $rule_template_attribute_one = new Metaattribute();
            $rule_template_attribute_one->name = 'name';
            $rule_template_attribute_one->metaclass = $rule_template->id;
            $this->log($rule_template_attribute_one->save());
            $rule_template_attribute_two = new Metaattribute();
            $rule_template_attribute_two->name = 'salience';
            $rule_template_attribute_two->metaclass = $rule_template->id;
            $this->log($rule_template_attribute_two->save());
            $rule_template_attribute_three = new Metaattribute();
            $rule_template_attribute_three->name = 'description';
            $rule_template_attribute_three->metaclass = $rule_template->id;
            $this->log($rule_template_attribute_three->save());

            // Создание метакласса "Условие шаблона правила"
            $rule_template_condition = new Metaclass();
            $rule_template_condition->name = 'RuleTemplateCondition';
            $rule_template_condition->metamodel = $rules_metamodel->id;
            $this->log($rule_template_condition->save());
            // Создание метаатрибутов для метакласса "Условие шаблона правила"
            $rule_template_condition_attribute = new Metaattribute();
            $rule_template_condition_attribute->name = 'operator';
            $rule_template_condition_attribute->type = 'operatorValue';
            $rule_template_condition_attribute->value = 'none';
            $rule_template_condition_attribute->metaclass = $rule_template_condition->id;
            $this->log($rule_template_condition_attribute->save());

            // Создание метакласса "Действие шаблона правила"
            $rule_template_action = new Metaclass();
            $rule_template_action->name = 'RuleTemplateAction';
            $rule_template_action->metamodel = $rules_metamodel->id;
            $this->log($rule_template_action->save());
            // Создание метаатрибутов для метакласса "Действие шаблона правила"
            $rule_template_action_attribute = new Metaattribute();
            $rule_template_action_attribute->name = 'function';
            $rule_template_action_attribute->type = 'functionName';
            $rule_template_action_attribute->value = 'none';
            $rule_template_action_attribute->metaclass = $rule_template_action->id;
            $this->log($rule_template_action_attribute->save());

            // Создание метакласса "Правило"
            $rule = new Metaclass();
            $rule->name = 'Rule';
            $rule->metamodel = $rules_metamodel->id;
            $this->log($rule->save());
            // Создание метаатрибутов для метакласса "Правило"
            $rule_attribute_one = new Metaattribute();
            $rule_attribute_one->name = 'name';
            $rule_attribute_one->metaclass = $rule->id;
            $this->log($rule_attribute_one->save());
            $rule_attribute_two= new Metaattribute();
            $rule_attribute_two->name = 'certaintyFactor';
            $rule_attribute_two->metaclass = $rule->id;
            $this->log($rule_attribute_two->save());
            $rule_attribute_three = new Metaattribute();
            $rule_attribute_three->name = 'salience';
            $rule_attribute_three->metaclass = $rule->id;
            $this->log($rule_attribute_three->save());
            $rule_attribute_four = new Metaattribute();
            $rule_attribute_four->name = 'description';
            $rule_attribute_four->metaclass = $rule->id;
            $this->log($rule_attribute_four->save());

            // Создание метакласса "Условие правила"
            $rule_condition = new Metaclass();
            $rule_condition->name = 'RuleCondition';
            $rule_condition->metamodel = $rules_metamodel->id;
            $this->log($rule_condition->save());
            // Создание метаатрибутов для метакласса "Условие правила"
            $rule_condition_attribute = new Metaattribute();
            $rule_condition_attribute->name = 'operator';
            $rule_condition_attribute->type = 'operatorValue';
            $rule_condition_attribute->value = 'none';
            $rule_condition_attribute->metaclass = $rule_condition->id;
            $this->log($rule_condition_attribute->save());

            // Создание метакласса "Действие правила"
            $rule_action = new Metaclass();
            $rule_action->name = 'RuleAction';
            $rule_action->metamodel = $rules_metamodel->id;
            $this->log($rule_action->save());
            // Создание метаатрибутов для метакласса "Действие правила"
            $rule_action_attribute = new Metaattribute();
            $rule_action_attribute->name = 'function';
            $rule_action_attribute->type = 'functionValue';
            $rule_action_attribute->value = 'none';
            $rule_action_attribute->metaclass = $rule_action->id;
            $this->log($rule_action_attribute->save());

            // Создание метасвязи между метаклассами "Продукционная модель" и "Шаблон факта"
            $production_model_to_fact_template = new Metarelation();
            $production_model_to_fact_template->name = 'ProductionModel-to-FactTemplate';
            $production_model_to_fact_template->type = Metarelation::ASSOCIATION;
            $production_model_to_fact_template->metamodel = $rules_metamodel->id;
            $production_model_to_fact_template->left_metaclass = $production_model->id;
            $production_model_to_fact_template->right_metaclass = $fact_template->id;
            $this->log($production_model_to_fact_template->save());
            // Создание метасвязи между метаклассами "Продукционная модель" и "Факт"
            $production_model_to_fact = new Metarelation();
            $production_model_to_fact->name = 'ProductionModel-to-Fact';
            $production_model_to_fact->type = Metarelation::ASSOCIATION;
            $production_model_to_fact->metamodel = $rules_metamodel->id;
            $production_model_to_fact->left_metaclass = $production_model->id;
            $production_model_to_fact->right_metaclass = $fact->id;
            $this->log($production_model_to_fact->save());
            // Создание метасвязи между метаклассами "Продукционная модель" и "Шаблон правила"
            $production_model_to_rule_template = new Metarelation();
            $production_model_to_rule_template->name = 'ProductionModel-to-RuleTemplate';
            $production_model_to_rule_template->type = Metarelation::ASSOCIATION;
            $production_model_to_rule_template->metamodel = $rules_metamodel->id;
            $production_model_to_rule_template->left_metaclass = $production_model->id;
            $production_model_to_rule_template->right_metaclass = $rule_template->id;
            $this->log($production_model_to_rule_template->save());
            // Создание метасвязи между метаклассами "Продукционная модель" и "Правило"
            $production_model_to_rule = new Metarelation();
            $production_model_to_rule->name = 'ProductionModel-to-Rule';
            $production_model_to_rule->type = Metarelation::ASSOCIATION;
            $production_model_to_rule->metamodel = $rules_metamodel->id;
            $production_model_to_rule->left_metaclass = $production_model->id;
            $production_model_to_rule->right_metaclass = $rule->id;
            $this->log($production_model_to_rule->save());
            // Создание метасвязи между метаклассами "Шаблон факта" и "Слот шаблона факта"
            $fact_template_to_fact_template_slot = new Metarelation();
            $fact_template_to_fact_template_slot->name = 'FactTemplate-to-FactTemplateSlot';
            $fact_template_to_fact_template_slot->type = Metarelation::ASSOCIATION;
            $fact_template_to_fact_template_slot->metamodel = $rules_metamodel->id;
            $fact_template_to_fact_template_slot->left_metaclass = $fact_template->id;
            $fact_template_to_fact_template_slot->right_metaclass = $fact_template_slot->id;
            $this->log($fact_template_to_fact_template_slot->save());
            // Создание метасвязи между метаклассами "Тип данных" и "Слот шаблона факта"
            $data_type_to_fact_template_slot = new Metarelation();
            $data_type_to_fact_template_slot->name = 'DataType-to-FactTemplateSlot';
            $data_type_to_fact_template_slot->type = Metarelation::ASSOCIATION;
            $data_type_to_fact_template_slot->metamodel = $rules_metamodel->id;
            $data_type_to_fact_template_slot->left_metaclass = $data_type->id;
            $data_type_to_fact_template_slot->right_metaclass = $fact_template_slot->id;
            $this->log($data_type_to_fact_template_slot->save());
            // Создание метасвязи между метаклассами "Факт" и "Слот факта"
            $fact_to_fact_slot = new Metarelation();
            $fact_to_fact_slot->name = 'Fact-to-FactSlot';
            $fact_to_fact_slot->type = Metarelation::ASSOCIATION;
            $fact_to_fact_slot->metamodel = $rules_metamodel->id;
            $fact_to_fact_slot->left_metaclass = $fact->id;
            $fact_to_fact_slot->right_metaclass = $fact_slot->id;
            $this->log($fact_to_fact_slot->save());
            // Создание метасвязи между метаклассами "Тип данных" и "Слот факта"
            $data_type_to_fact_slot = new Metarelation();
            $data_type_to_fact_slot->name = 'DataType-to-FactSlot';
            $data_type_to_fact_slot->type = Metarelation::ASSOCIATION;
            $data_type_to_fact_slot->metamodel = $rules_metamodel->id;
            $data_type_to_fact_slot->left_metaclass = $data_type->id;
            $data_type_to_fact_slot->right_metaclass = $fact_slot->id;
            $this->log($data_type_to_fact_slot->save());
            // Создание метасвязи между метаклассами "Шаблон факта" и "Факт"
            $fact_template_to_fact = new Metarelation();
            $fact_template_to_fact->name = 'FactTemplate-to-Fact';
            $fact_template_to_fact->type = Metarelation::ASSOCIATION;
            $fact_template_to_fact->metamodel = $rules_metamodel->id;
            $fact_template_to_fact->left_metaclass = $fact_template->id;
            $fact_template_to_fact->right_metaclass = $fact->id;
            $this->log($fact_template_to_fact->save());
            // Создание метасвязи между метаклассами "Шаблон правила" и "Правило"
            $rule_template_to_rule = new Metarelation();
            $rule_template_to_rule->name = 'RuleTemplate-to-Rule';
            $rule_template_to_rule->type = Metarelation::ASSOCIATION;
            $rule_template_to_rule->metamodel = $rules_metamodel->id;
            $rule_template_to_rule->left_metaclass = $rule_template->id;
            $rule_template_to_rule->right_metaclass = $rule->id;
            $this->log($rule_template_to_rule->save());
            // Создание метасвязи между метаклассами "Шаблон правила" и "Условие шаблона правила"
            $rule_template_to_rule_template_condition = new Metarelation();
            $rule_template_to_rule_template_condition->name = 'RuleTemplate-to-RuleTemplateCondition';
            $rule_template_to_rule_template_condition->type = Metarelation::ASSOCIATION;
            $rule_template_to_rule_template_condition->metamodel = $rules_metamodel->id;
            $rule_template_to_rule_template_condition->left_metaclass = $rule_template->id;
            $rule_template_to_rule_template_condition->right_metaclass = $rule_template_condition->id;
            $this->log($rule_template_to_rule_template_condition->save());
            // Создание метасвязи между метаклассами "Шаблон правила" и "Действие шаблона правила"
            $rule_template_to_rule_template_action = new Metarelation();
            $rule_template_to_rule_template_action->name = 'RuleTemplate-to-RuleTemplateAction';
            $rule_template_to_rule_template_action->type = Metarelation::ASSOCIATION;
            $rule_template_to_rule_template_action->metamodel = $rules_metamodel->id;
            $rule_template_to_rule_template_action->left_metaclass = $rule_template->id;
            $rule_template_to_rule_template_action->right_metaclass = $rule_template_action->id;
            $this->log($rule_template_to_rule_template_action->save());
            // Создание метасвязи между метаклассами "Шаблон факта" и "Условие шаблона правила"
            $fact_template_to_rule_template_condition = new Metarelation();
            $fact_template_to_rule_template_condition->name = 'FactTemplate-to-RuleTemplateCondition';
            $fact_template_to_rule_template_condition->type = Metarelation::ASSOCIATION;
            $fact_template_to_rule_template_condition->metamodel = $rules_metamodel->id;
            $fact_template_to_rule_template_condition->left_metaclass = $fact_template->id;
            $fact_template_to_rule_template_condition->right_metaclass = $rule_template_condition->id;
            $this->log($fact_template_to_rule_template_condition->save());
            // Создание метасвязи между метаклассами "Шаблон факта" и "Действие шаблона правила"
            $fact_template_to_rule_template_action = new Metarelation();
            $fact_template_to_rule_template_action->name = 'FactTemplate-to-RuleTemplateAction';
            $fact_template_to_rule_template_action->type = Metarelation::ASSOCIATION;
            $fact_template_to_rule_template_action->metamodel = $rules_metamodel->id;
            $fact_template_to_rule_template_action->left_metaclass = $fact_template->id;
            $fact_template_to_rule_template_action->right_metaclass = $rule_template_action->id;
            $this->log($fact_template_to_rule_template_action->save());
            // Создание метасвязи между метаклассами "Правило" и "Условие правила"
            $rule_to_rule_condition = new Metarelation();
            $rule_to_rule_condition->name = 'Rule-to-RuleCondition';
            $rule_to_rule_condition->type = Metarelation::ASSOCIATION;
            $rule_to_rule_condition->metamodel = $rules_metamodel->id;
            $rule_to_rule_condition->left_metaclass = $rule->id;
            $rule_to_rule_condition->right_metaclass = $rule_condition->id;
            $this->log($rule_to_rule_condition->save());
            // Создание метасвязи между метаклассами "Правило" и "Действие правила"
            $rule_to_rule_action = new Metarelation();
            $rule_to_rule_action->name = 'Rule-to-RuleAction';
            $rule_to_rule_action->type = Metarelation::ASSOCIATION;
            $rule_to_rule_action->metamodel = $rules_metamodel->id;
            $rule_to_rule_action->left_metaclass = $rule->id;
            $rule_to_rule_action->right_metaclass = $rule_action->id;
            $this->log($rule_to_rule_action->save());
            // Создание метасвязи между метаклассами "Факт" и "Условие правила"
            $fact_to_rule_condition = new Metarelation();
            $fact_to_rule_condition->name = 'Fact-to-RuleCondition';
            $fact_to_rule_condition->type = Metarelation::ASSOCIATION;
            $fact_to_rule_condition->metamodel = $rules_metamodel->id;
            $fact_to_rule_condition->left_metaclass = $fact->id;
            $fact_to_rule_condition->right_metaclass = $rule_condition->id;
            $this->log($fact_to_rule_condition->save());
            // Создание метасвязи между метаклассами "Факт" и "Действие правила"
            $fact_to_rule_action = new Metarelation();
            $fact_to_rule_action->name = 'Fact-to-RuleAction';
            $fact_to_rule_action->type = Metarelation::ASSOCIATION;
            $fact_to_rule_action->metamodel = $rules_metamodel->id;
            $fact_to_rule_action->left_metaclass = $fact->id;
            $fact_to_rule_action->right_metaclass = $rule_action->id;
            $this->log($fact_to_rule_action->save());

            // Создание метамодели CLIPS
            $clips_metamodel = new Metamodel();
            $clips_metamodel->name = 'Метамодель CLIPS';
            $clips_metamodel->description = 'Метамодель CLIPS.';
            $clips_metamodel->type = Metamodel::DEFAULT_TYPE;
            $clips_metamodel->author = 1;
            $this->log($clips_metamodel->save());

            // Создание метамодели OWL
            $owl_metamodel = new Metamodel();
            $owl_metamodel->name = 'Метамодель OWL';
            $owl_metamodel->description = 'Метамодель OWL.';
            $owl_metamodel->type = Metamodel::DEFAULT_TYPE;
            $owl_metamodel->author = 1;
            $this->log($owl_metamodel->save());

            // Создание метамодели концептуальной модели XMI UML
            $xmi_uml_metamodel = new Metamodel();
            $xmi_uml_metamodel->name = 'Метамодель концептуальных моделей XMI UML';
            $xmi_uml_metamodel->description = 'Метамодель концептуальных моделей XMI UML.';
            $xmi_uml_metamodel->type = Metamodel::USER_TYPE;
            $xmi_uml_metamodel->author = 1;
            $this->log($xmi_uml_metamodel->save());

            // Создание метамодели концепт-карты CmapTools (XTM)
            $xtm_metamodel = new Metamodel();
            $xtm_metamodel->name = 'Метамодель концепт-карт CmapTools (XTM)';
            $xtm_metamodel->description = 'Метамодель концепт-карт CmapTools, в формате XML Topic Maps (XTM 1.0).';
            $xtm_metamodel->type = Metamodel::USER_TYPE;
            $xtm_metamodel->author = 1;
            $this->log($xtm_metamodel->save());
        } else
            $this->stdout('Metamodels (ontology, rules, CLIPS, OWL and XMI UML, XTM CmapTools) are created!',
                Console::FG_GREEN, Console::BOLD);
    }

    /**
     * Команда удаления метамодели по наименованию.
     */
    public function actionRemove()
    {
        $name = $this->prompt('Name:', ['required' => true]);
        $model = $this->findModel($name);
        $this->log($model->delete());
    }

    /**
     * Команда удаления всех метамоделей.
     */
    public function actionAllRemove()
    {
        $model = new Metamodel();
        $this->log($model->deleteAll());
    }

    /**
     * Поиск метамодели по наименованию.
     * @param string $name
     * @throws \yii\console\Exception
     * @return Metamodel the loaded model
     */
    private function findModel($name)
    {
        if (!$model = Metamodel::findOne(['name' => $name])) {
            throw new Exception('Ontology metamodel not found!');
        }
        return $model;
    }

    /**
     * Вывод сообщений на экран (консоль)
     * @param bool $success
     */
    private function log($success)
    {
        if ($success) {
            $this->stdout('Success!', Console::FG_GREEN, Console::BOLD);
        } else {
            $this->stderr('Error!', Console::FG_RED, Console::BOLD);
        }
        echo PHP_EOL;
    }
}