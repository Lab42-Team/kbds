<?php

namespace app\components;

use Yii;
use yii\base\ErrorException;
use app\modules\knowledge_base\models\DataType;
use app\modules\knowledge_base\models\FactTemplate;
use app\modules\knowledge_base\models\FactTemplateSlot;
use app\modules\knowledge_base\models\Fact;
use app\modules\knowledge_base\models\FactSlot;
use app\modules\knowledge_base\models\RuleTemplate;
use app\modules\knowledge_base\models\RuleTemplateCondition;
use app\modules\knowledge_base\models\RuleTemplateAction;
use app\modules\knowledge_base\models\Rule;
use app\modules\knowledge_base\models\RuleCondition;
use app\modules\knowledge_base\models\RuleAction;

/**
 * ProductionModelGenerator.
 * Класс ProductionModelGenerator обеспечивает генерацию (создание) продукционной модели.
 */
class ProductionModelGenerator
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
     * Проверка существования сгенерированных элементов у продукционной модели.
     * @param $id - идентификатор базы знаний (продукционной модели)
     * @return bool - результат проверки
     */
    public static function existElements($id)
    {
        // Переменная результата проверки
        $is_exist = false;
        // Поиск всех шаблонов фактов принадлежащих данной продукционной модели
        $fact_templates = FactTemplate::find()->where(array('production_model' => $id))->all();
        // Поиск всех типов данных принадлежащих данной продукционной модели
        $rule_templates = RuleTemplate::find()->where(array('production_model' => $id))->all();
        // Поиск всех типов данных принадлежащих данной продукционной модели
        $data_types = DataType::find()->where(array('knowledge_base' => $id))->all();
        // Если выборка шаблонов фактов или шаблонов правил или типов данных не пустая,
        // то меняем переменную результата проверки
        if (!empty($fact_templates) || !empty($rule_templates) || !empty($data_types))
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
     * Определение наименования нового шаблона факта.
     * @param $knowledge_base_id - идентификатор базы знаний (продукционной модели)
     * @param $current_name - текущее наименование шаблона факта
     * @param $result_name - результирующее наименование шаблона факта
     * @param $index - порядковый номер (индекс) сгенерированного наименования шаблона факта
     * @return string - сгенерированное новое наименование шаблона факта
     */
    public static function createFactTemplateName($knowledge_base_id, $current_name, $result_name, $index)
    {
        // Поиск шаблонов фактов принадлежащих определенной базе знаний и имеющих определенные имена
        $fact_templates = FactTemplate::find()
            ->where(array('production_model' => $knowledge_base_id, 'name' => $current_name))
            ->all();
        // Генерация нового наименования для шаблона факта, если шаблон факта с таким наименованием уже есть
        if (!empty($fact_templates)) {
            $index++;
            $current_name = $result_name . '-' . $index;
            // Повторный вызов данного метода
            $result_name = self::createFactTemplateName($knowledge_base_id, $current_name, $result_name, $index);
        }
        else
            if ($index > 1)
                $result_name .= '-' . $index;

        return $result_name;
    }

    /**
     * Добавление нового шаблона факта.
     * @param $knowledge_base_id - идентификатор базы знаний (продукционной модели)
     * @param $attribute_values - извлеченные значения атрибутов элемента
     * @return int - id созданного шаблона факта
     */
    public static function addFactTemplate($knowledge_base_id, $attribute_values)
    {
        $fact_template = new FactTemplate();
        foreach ($attribute_values as $attribute_value) {
            if ($attribute_value[0] == 'name')
                // Вызов метода создания наименования для нового шаблона факта
                $fact_template->name = self::createFactTemplateName(
                    $knowledge_base_id,
                    self::decodeText($attribute_value[1]),
                    self::decodeText($attribute_value[1]),
                    1
                );
            if ($attribute_value[0] == 'description')
                $fact_template->description = self::decodeText($attribute_value[1]);
        }
        $fact_template->production_model = $knowledge_base_id;
        $fact_template->save();

        return $fact_template->id;
    }

    /**
     * Изменение шаблона факта.
     * @param $fact_template_id - идентификатор шаблона факта
     * @param $attribute_values - извлеченные значения атрибутов элемента
     */
    public static function editFactTemplate($fact_template_id, $attribute_values)
    {
        $fact_template = FactTemplate::findOne($fact_template_id);
        foreach ($attribute_values as $attribute_value) {
            if ($attribute_value[0] == 'name')
                $fact_template->name = self::decodeText($attribute_value[1]);
            if ($attribute_value[0] == 'description')
                $fact_template->description = self::decodeText($attribute_value[1]);
        }
        $fact_template->save();
    }

    /**
     * Определение наименования нового слота для шаблона факта.
     * @param $fact_template_id - идентификатор шаблона факта
     * @param $data_type_id - идентификатор типа данных
     * @param $current_name - текущее наименование слота шаблона факта
     * @param $result_name - результирующее наименование слота шаблона факта
     * @param $index - порядковый номер (индекс) сгенерированного наименования слота шаблона факта
     * @return string - сгенерированное новое наименование слота шаблона факта
     */
    public static function createFactTemplateSlotName($fact_template_id, $data_type_id, $current_name,
                                                      $result_name, $index)
    {
        // Поиск слотов принадлежащих определенному шаблону факту и типу данных, а также имеющих определенные имена
        $fact_template_slots = FactTemplateSlot::find()
            ->where(array('fact_template' => $fact_template_id, 'data_type' => $data_type_id, 'name' => $current_name))
            ->all();
        // Генерация нового наименования для слота шаблона факта, если слот шаблона факта с таким наименованием уже есть
        if (!empty($fact_template_slots)) {
            $index++;
            $current_name = $result_name . '-' . $index;
            // Повторный вызов данного метода
            $result_name = self::createFactTemplateSlotName(
                $fact_template_id,
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
     * Добавление нового слота для шаблона факта.
     * @param $knowledge_base_id - идентификатор базы знаний (продукционной модели)
     * @param $fact_template_id - идентификатор шаблона факта
     * @param $data_type_id - идентификатор типа данных
     * @param $attribute_values - извлеченные значения атрибутов элемента
     * @return int - id созданного слота шаблона факта
     */
    public static function addFactTemplateSlot($knowledge_base_id, $fact_template_id, $data_type_id, $attribute_values)
    {
        $fact_template_slot = new FactTemplateSlot();
        foreach ($attribute_values as $attribute_value) {
            if ($attribute_value[0] == 'name')
                // Вызов метода создания наименования для нового слота шаблона факта
                $fact_template_slot->name = self::createFactTemplateSlotName(
                    $fact_template_id,
                    $data_type_id,
                    self::decodeText($attribute_value[1]),
                    self::decodeText($attribute_value[1]),
                    1
                );
            if ($attribute_value[0] == 'defaultValue')
                $fact_template_slot->default_value = self::decodeText($attribute_value[1]);
            if ($attribute_value[0] == 'description')
                $fact_template_slot->description = self::decodeText($attribute_value[1]);
        }
        $fact_template_slot->fact_template = $fact_template_id;
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
                $fact_template_slot->data_type = $new_data_type->id;
            }
            else
                $fact_template_slot->data_type = $data_type->id;
        }
        else
            $fact_template_slot->data_type = $data_type_id;
        $fact_template_slot->save();

        return $fact_template_slot->id;
    }

    /**
     * Изменение слота шаблона факта.
     * @param $fact_template_slot_id - идентификатор слота шаблона факта
     * @param $attribute_values - извлеченные значения атрибутов элемента
     */
    public static function editFactTemplateSlot($fact_template_slot_id, $attribute_values)
    {
        $fact_template_slot = FactTemplateSlot::findOne($fact_template_slot_id);
        foreach ($attribute_values as $attribute_value) {
            if ($attribute_value[0] == 'name')
                $fact_template_slot->name = self::decodeText($attribute_value[1]);
            if ($attribute_value[0] == 'defaultValue')
                $fact_template_slot->default_value = self::decodeText($attribute_value[1]);
            if ($attribute_value[0] == 'description')
                $fact_template_slot->description = self::decodeText($attribute_value[1]);
        }
        $fact_template_slot->save();
    }

    /**
     * Определение наименования нового шаблона правила.
     * @param $knowledge_base_id - идентификатор базы знаний (продукционной модели)
     * @param $current_name - текущее наименование шаблона правила
     * @param $result_name - результирующее наименование шаблона правила
     * @param $index - порядковый номер (индекс) сгенерированного наименования шаблона правила
     * @return string - сгенерированное новое наименование шаблона правила
     */
    public static function createRuleTemplateName($knowledge_base_id, $current_name, $result_name, $index)
    {
        // Поиск шаблонов правил принадлежащих определенной базе знаний и имеющих определенные имена
        $rule_templates = RuleTemplate::find()
            ->where(array('production_model' => $knowledge_base_id, 'name' => $current_name))
            ->all();
        // Генерация нового наименования для шаблона правила, если шаблон правила с таким наименованием уже есть
        if (!empty($rule_templates)) {
            $index++;
            $current_name = $result_name . '-' . $index;
            // Повторный вызов данного метода
            $result_name = self::createRuleTemplateName($knowledge_base_id, $current_name, $result_name, $index);
        }
        else
            if ($index > 1)
                $result_name .= '-' . $index;

        return $result_name;
    }

    /**
     * Добавление нового шаблона правила.
     * @param $knowledge_base_id - идентификатор базы знаний (продукционной модели)
     * @param $attribute_values - извлеченные значения атрибутов элемента
     * @return int - id созданного шаблона правила
     */
    public static function addRuleTemplate($knowledge_base_id, $attribute_values)
    {
        $rule_template = new RuleTemplate();
        foreach ($attribute_values as $attribute_value) {
            if ($attribute_value[0] == 'name')
                // Вызов метода создания наименования для нового шаблона правила
                $rule_template->name = self::createRuleTemplateName(
                    $knowledge_base_id,
                    self::decodeText($attribute_value[1]),
                    self::decodeText($attribute_value[1]),
                    1
                );
            if ($attribute_value[0] == 'salience')
                $rule_template->salience = self::decodeText($attribute_value[1]);
            if ($attribute_value[0] == 'description')
                $rule_template->description = self::decodeText($attribute_value[1]);
        }
        // Приоритет шаблона правила равен нулю, если он не был задан
        if ($rule_template->salience == null)
            $rule_template->salience = 0;
        $rule_template->production_model = $knowledge_base_id;
        $rule_template->save();

        return $rule_template->id;
    }

    /**
     * Изменение шаблона правила.
     * @param $rule_template_id - идентификатор шаблона правила
     * @param $attribute_values - извлеченные значения атрибутов элемента
     */
    public static function editRuleTemplate($rule_template_id, $attribute_values)
    {
        $rule_template = RuleTemplate::findOne($rule_template_id);
        foreach ($attribute_values as $attribute_value) {
            if ($attribute_value[0] == 'name')
                $rule_template->name = self::decodeText($attribute_value[1]);
            if ($attribute_value[0] == 'description')
                $rule_template->description = self::decodeText($attribute_value[1]);
            if ($attribute_value[0] == 'salience')
                $rule_template->salience = self::decodeText($attribute_value[1]);
        }
        $rule_template->save();
    }

    /**
     * Добавление нового условия для шаблона правила.
     * @param $rule_template_id - идентификатор шаблона правила
     * @param $fact_template_id - идентификатор шаблона факта
     * @param $attribute_values - извлеченные значения атрибутов элемента
     * @return int - id созданного условия шаблона правила
     */
    public static function addRuleTemplateCondition($rule_template_id, $fact_template_id, $attribute_values)
    {
        $rule_template_condition = new RuleTemplateCondition();
        $rule_template_condition->operator = RuleTemplateCondition::OPERATOR_NONE;
        foreach ($attribute_values as $attribute_value) {
            if ($attribute_value[0] == 'operator')
                $rule_template_condition->operator = self::decodeText($attribute_value[1]);
        }
        $rule_template_condition->rule_template = $rule_template_id;
        $rule_template_condition->fact_template = $fact_template_id;
        $rule_template_condition->save();

        return $rule_template_condition->id;
    }

    /**
     * Изменение условия для шаблона правила.
     * @param $rule_template_condition_id - идентификатор условия шаблона правила
     * @param $attribute_values - извлеченные значения атрибутов элемента
     */
    public static function editRuleTemplateCondition($rule_template_condition_id, $attribute_values)
    {
        $rule_template_condition = RuleTemplateCondition::findOne($rule_template_condition_id);
        $rule_template_condition->operator = RuleTemplateCondition::OPERATOR_NONE;
        foreach ($attribute_values as $attribute_value) {
            if ($attribute_value[0] == 'operator')
                $rule_template_condition->operator = self::decodeText($attribute_value[1]);
        }
        $rule_template_condition->save();
    }

    /**
     * Добавление нового действия для шаблона правила.
     * @param $rule_template_id - идентификатор шаблона правила
     * @param $fact_template_id - идентификатор шаблона факта
     * @param $attribute_values - извлеченные значения атрибутов элемента
     * @return int - id созданного действия шаблона правила
     */
    public static function addRuleTemplateAction($rule_template_id, $fact_template_id, $attribute_values)
    {
        $rule_template_action = new RuleTemplateAction();
        $rule_template_action->function = RuleTemplateAction::FUNCTION_ASSERT;
        foreach ($attribute_values as $attribute_value) {
            if ($attribute_value[0] == 'function')
                $rule_template_action->function = self::decodeText($attribute_value[1]);
        }
        $rule_template_action->rule_template = $rule_template_id;
        $rule_template_action->fact_template = $fact_template_id;
        $rule_template_action->save();

        return $rule_template_action->id;
    }

    /**
     * Изменение действия для шаблона правила.
     * @param $rule_template_action_id - идентификатор действия шаблона правила
     * @param $attribute_values - извлеченные значения атрибутов элемента
     */
    public static function editRuleTemplateAction($rule_template_action_id, $attribute_values)
    {
        $rule_template_action = RuleTemplateAction::findOne($rule_template_action_id);
        $rule_template_action->function = RuleTemplateAction::FUNCTION_ASSERT;
        foreach ($attribute_values as $attribute_value) {
            if ($attribute_value[0] == 'function')
                $rule_template_action->function = self::decodeText($attribute_value[1]);
        }
        $rule_template_action->save();
    }

    /**
     * Определение наименования нового факта.
     * @param $knowledge_base_id - идентификатор базы знаний (продукционной модели)
     * @param $current_name - текущее наименование факта
     * @param $result_name - результирующее наименование факта
     * @param $index - порядковый номер (индекс) сгенерированного наименования факта
     * @return string - сгенерированное новое наименование факта
     */
    public static function createFactName($knowledge_base_id, $current_name, $result_name, $index)
    {
        // Поиск фактов принадлежащих определенной базе знаний и имеющих определенные имена
        $facts = Fact::find()
            ->where(array('production_model' => $knowledge_base_id, 'name' => $current_name))
            ->all();
        // Генерация нового наименования для факта, если факт с таким наименованием уже есть
        if (!empty($facts)) {
            $index++;
            $current_name = $result_name . '-' . $index;
            // Повторный вызов данного метода
            $result_name = self::createFactName($knowledge_base_id, $current_name, $result_name, $index);
        }
        else
            if ($index > 1)
                $result_name .= '-' . $index;

        return $result_name;
    }

    /**
     * Добавление нового факта.
     * @param $knowledge_base_id - идентификатор базы знаний (продукционной модели)
     * @param $fact_template_id - идентификатор шаблона факта
     * @param $attribute_values - извлеченные значения атрибутов элемента
     * @return int - id созданного факта
     */
    public static function addFact($knowledge_base_id, $fact_template_id, $attribute_values)
    {
        $fact = new Fact();
        foreach ($attribute_values as $attribute_value) {
            if ($attribute_value[0] == 'name')
                // Вызов метода создания наименования для нового факта
                $fact->name = self::createFactName(
                    $knowledge_base_id,
                    self::decodeText($attribute_value[1]),
                    self::decodeText($attribute_value[1]),
                    1
                );
            if ($attribute_value[0] == 'initial' && $attribute_value[1])
                $fact->initial = self::decodeText($attribute_value[1]);
            else
                $fact->initial = false;
            if ($attribute_value[0] == 'certaintyFactor')
                $fact->certainty_factor = self::decodeText($attribute_value[1]);
            if ($attribute_value[0] == 'description')
                $fact->description = self::decodeText($attribute_value[1]);
        }
        $fact->production_model = $knowledge_base_id;
        // Создание нового шаблона факта, если не задан его id
        if ($fact_template_id == 0) {
            $fact_template = new FactTemplate();
            foreach ($attribute_values as $attribute_value) {
                if ($attribute_value[0] == 'name')
                    // Вызов метода создания наименования для нового шаблона факта.
                    // Данное наименование дублирует наименование факта
                    $fact_template->name = self::createFactTemplateName(
                        $knowledge_base_id,
                        self::decodeText($attribute_value[1]),
                        self::decodeText($attribute_value[1]),
                        1
                    );
            }
            $fact_template->description = Yii::t('app',
                'FACT_TEMPLATE_MODEL_DESCRIPTION_FOR_AUTOMATICALLY_CREATED_FACT_TEMPLATE');
            $fact_template->production_model = $knowledge_base_id;
            $fact_template->save();
            $fact->fact_template = $fact_template->id;
        }
        else
            $fact->fact_template = $fact_template_id;
        $fact->save();

        return $fact->id;
    }

    /**
     * Изменение факта.
     * @param $fact_id - идентификатор факта
     * @param $attribute_values - извлеченные значения атрибутов элемента
     */
    public static function editFact($fact_id, $attribute_values)
    {
        $fact = Fact::findOne($fact_id);
        foreach ($attribute_values as $attribute_value) {
            if ($attribute_value[0] == 'name')
                $fact->name = self::decodeText($attribute_value[1]);
            if ($attribute_value[0] == 'initial')
                $fact->initial = self::decodeText($attribute_value[1]);
            if ($attribute_value[0] == 'certaintyFactor')
                $fact->certainty_factor = self::decodeText($attribute_value[1]);
            if ($attribute_value[0] == 'description')
                $fact->description = self::decodeText($attribute_value[1]);
        }
        $fact->save();
    }

    /**
     * Определение наименования нового слота для факта.
     * @param $fact_id - идентификатор факта
     * @param $data_type_id - идентификатор типа данных
     * @param $current_name - текущее наименование слота факта
     * @param $result_name - результирующее наименование слота факта
     * @param $index - порядковый номер (индекс) сгенерированного наименования слота факта
     * @return string - сгенерированное новое наименование слота факта
     */
    public static function createFactSlotName($fact_id, $data_type_id, $current_name, $result_name, $index)
    {
        // Поиск слотов принадлежащих определенному факту и типу данных, а также имеющих определенные имена
        $fact_slots = FactSlot::find()
            ->where(array('fact' => $fact_id, 'data_type' => $data_type_id, 'name' => $current_name))
            ->all();
        // Генерация нового наименования для слота факта, если слот факта с таким наименованием уже есть
        if (!empty($fact_slots)) {
            $index++;
            $current_name = $result_name . '-' . $index;
            // Повторный вызов данного метода
            $result_name = self::createFactSlotName(
                $fact_id,
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
     * Добавление нового слота для факта.
     * @param $knowledge_base_id - идентификатор базы знаний (продукционной модели)
     * @param $fact_id - идентификатор факта
     * @param $data_type_id - идентификатор типа данных
     * @param $attribute_values - извлеченные значения атрибутов элемента
     * @return int - id созданного слота факта
     */
    public static function addFactSlot($knowledge_base_id, $fact_id, $data_type_id, $attribute_values)
    {
        $fact_slot = new FactSlot();
        foreach ($attribute_values as $attribute_value) {
            if ($attribute_value[0] == 'name')
                // Вызов метода создания наименования для нового слота факта
                $fact_slot->name = self::createFactSlotName(
                    $fact_id,
                    $data_type_id,
                    self::decodeText($attribute_value[1]),
                    self::decodeText($attribute_value[1]),
                    1
                );
            if ($attribute_value[0] == 'value')
                $fact_slot->value = self::decodeText($attribute_value[1]);
            if ($attribute_value[0] == 'description')
                $fact_slot->description = self::decodeText($attribute_value[1]);
        }
        $fact_slot->fact = $fact_id;
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
                $fact_slot->data_type = $new_data_type->id;
            }
            else
                $fact_slot->data_type = $data_type->id;
        }
        else
            $fact_slot->data_type = $data_type_id;
        $fact_slot->save();
        // Поиск факта по его id
        $fact = Fact::findOne([$fact_id]);
        // Поиск шаблона факта, связанного с данным конкретным фактом, по его id
        $fact_template = FactTemplate::findOne($fact->fact_template);
        // Создание слота для найденного шаблона факта, если он был создан системой автоматически
        if ($fact_template->description == 'Данный шаблон факта был создан автоматически на основе конкретного факта.'
            ||
            $fact_template->description == 'This fact template was created automatically based on the concrete fact.') {
            $fact_template_slot = new FactTemplateSlot();
            $fact_template_slot->name = $fact_slot->name;
            $fact_template_slot->description = Yii::t('app',
                'FACT_TEMPLATE_SLOT_MODEL_DESCRIPTION_FOR_AUTOMATICALLY_CREATED_FACT_TEMPLATE_SLOT');
            $fact_template_slot->fact_template = $fact_template->id;
            $fact_template_slot->data_type = $fact_slot->data_type;
            $fact_template_slot->save();
        }

        return $fact_slot->id;
    }

    /**
     * Изменение слота факта.
     * @param $fact_slot_id - идентификатор слота факта
     * @param $attribute_values - извлеченные значения атрибутов элемента
     */
    public static function editFactSlot($fact_slot_id, $attribute_values)
    {
        $fact_slot = FactSlot::findOne($fact_slot_id);
        foreach ($attribute_values as $attribute_value) {
            if ($attribute_value[0] == 'name')
                $fact_slot->name = self::decodeText($attribute_value[1]);
            if ($attribute_value[0] == 'value')
                $fact_slot->value = self::decodeText($attribute_value[1]);
            if ($attribute_value[0] == 'description')
                $fact_slot->description = self::decodeText($attribute_value[1]);
        }
        $fact_slot->save();
    }

    /**
     * Определение наименования нового правила.
     * @param $knowledge_base_id - идентификатор базы знаний (продукционной модели)
     * @param $current_name - текущее наименование правила
     * @param $result_name - результирующее наименование правила
     * @param $index - порядковый номер (индекс) сгенерированного наименования правила
     * @return string - сгенерированное новое наименование правила
     */
    public static function createRuleName($knowledge_base_id, $current_name, $result_name, $index)
    {
        // Поиск правил принадлежащих определенной базе знаний и имеющих определенные имена
        $rules = Rule::find()
            ->where(array('production_model' => $knowledge_base_id, 'name' => $current_name))
            ->all();
        // Генерация нового наименования для правила, если правило с таким наименованием уже есть
        if (!empty($rules)) {
            $index++;
            $current_name = $result_name . '-' . $index;
            // Повторный вызов данного метода
            $result_name = self::createRuleName($knowledge_base_id, $current_name, $result_name, $index);
        }
        else
            if ($index > 1)
                $result_name .= '-' . $index;

        return $result_name;
    }

    /**
     * Добавление нового правила.
     * @param $knowledge_base_id - идентификатор базы знаний (продукционной модели)
     * @param $rule_template_id - идентификатор шаблона правила
     * @param $attribute_values - извлеченные значения атрибутов элемента
     * @return int - id созданного правила
     */
    public static function addRule($knowledge_base_id, $rule_template_id, $attribute_values)
    {
        $rule = new Rule();
        foreach ($attribute_values as $attribute_value) {
            if ($attribute_value[0] == 'name')
                // Вызов метода создания наименования для нового правила
                $rule->name = self::createRuleName(
                    $knowledge_base_id,
                    self::decodeText($attribute_value[1]),
                    self::decodeText($attribute_value[1]),
                    1
                );
            if ($attribute_value[0] == 'certaintyFactor')
                $rule->certainty_factor = self::decodeText($attribute_value[1]);
            if ($attribute_value[0] == 'salience')
                $rule->salience = self::decodeText($attribute_value[1]);
            if ($attribute_value[0] == 'description')
                $rule->description = self::decodeText($attribute_value[1]);
        }
        // Приоритет правила равен нулю, если он не был задан
        if ($rule->salience == null)
            $rule->salience = 0;
        $rule->production_model = $knowledge_base_id;
        // Создание нового шаблона правила, если не задан его id
        if ($rule_template_id == 0) {
            $rule_template = new RuleTemplate();
            foreach ($attribute_values as $attribute_value) {
                if ($attribute_value[0] == 'name')
                    // Вызов метода создания наименования для нового шаблона правила.
                    // Данное наименование дублирует наименование правила
                    $rule_template->name = self::createRuleTemplateName(
                        $knowledge_base_id,
                        self::decodeText($attribute_value[1]),
                        self::decodeText($attribute_value[1]),
                        1
                    );
            }
            $rule_template->description = Yii::t('app',
                'RULE_TEMPLATE_MODEL_DESCRIPTION_FOR_AUTOMATICALLY_CREATED_RULE_TEMPLATE');
            $rule_template->production_model = $knowledge_base_id;
            $rule_template->save();
            $rule->rule_template = $rule_template->id;
        }
        else
            $rule->rule_template = $rule_template_id;
        $rule->save();

        return $rule->id;
    }

    /**
     * Изменение правила.
     * @param $rule_id - идентификатор правила
     * @param $attribute_values - извлеченные значения атрибутов элемента
     */
    public static function editRule($rule_id, $attribute_values)
    {
        $rule = Rule::findOne($rule_id);
        foreach ($attribute_values as $attribute_value) {
            if ($attribute_value[0] == 'name')
                $rule->name = self::decodeText($attribute_value[1]);
            if ($attribute_value[0] == 'certaintyFactor')
                $rule->certainty_factor = self::decodeText($attribute_value[1]);
            if ($attribute_value[0] == 'salience')
                $rule->salience = self::decodeText($attribute_value[1]);
            if ($attribute_value[0] == 'description')
                $rule->description = self::decodeText($attribute_value[1]);
        }
        $rule->save();
    }

    /**
     * Добавление нового условия для правила.
     * @param $rule_id - идентификатор правила
     * @param $fact_id - идентификатор факта
     * @param $attribute_values - извлеченные значения атрибутов элемента
     * @return int - id созданного условия правила
     */
    public static function addRuleCondition($rule_id, $fact_id, $attribute_values)
    {
        $rule_condition = new RuleCondition();
        $rule_condition->operator = RuleCondition::OPERATOR_NONE;
        foreach ($attribute_values as $attribute_value) {
            if ($attribute_value[0] == 'operator')
                $rule_condition->operator = self::decodeText($attribute_value[1]);
        }
        $rule_condition->rule = $rule_id;
        $rule_condition->fact = $fact_id;
        $rule_condition->save();
        // Поиск факта по его id
        $fact = Fact::findOne([$fact_id]);
        // Поиск шаблона факта, связанного с данным конкретным фактом, по его id
        $fact_template = FactTemplate::findOne($fact->fact_template);
        // Поиск правила по его id
        $rule = Rule::findOne([$rule_id]);
        // Поиск шаблона правила, связанного с данным конкретным правилом, по его id
        $rule_template = RuleTemplate::findOne($rule->rule_template);
        // Создание условия для найденного шаблона правила, если он был создан системой автоматически
        if ($rule_template->description == 'Данный шаблон правила был создан автоматически на основе конкретного правила.'
            ||
            $rule_template->description == 'This rule template was created automatically based on the concrete rule.') {
            $rule_template_condition = new RuleTemplateCondition();
            $rule_template_condition->operator = $rule_condition->operator;
            $rule_template_condition->rule_template = $rule_template->id;
            $rule_template_condition->fact_template = $fact_template->id;
            $rule_template_condition->save();
        }

        return $rule_condition->id;
    }

    /**
     * Изменение условия для правила.
     * @param $rule_condition_id - идентификатор условия правила
     * @param $attribute_values - извлеченные значения атрибутов элемента
     */
    public static function editRuleCondition($rule_condition_id, $attribute_values)
    {
        $rule_condition = RuleCondition::findOne($rule_condition_id);
        $rule_condition->operator = RuleCondition::OPERATOR_NONE;
        foreach ($attribute_values as $attribute_value) {
            if ($attribute_value[0] == 'operator')
                $rule_condition->operator = self::decodeText($attribute_value[1]);
        }
        $rule_condition->save();
    }

    /**
     * Добавление нового действия для правила.
     * @param $rule_id - идентификатор правила
     * @param $fact_id - идентификатор факта
     * @param $attribute_values - извлеченные значения атрибутов элемента
     * @return int - id созданного действия правила
     */
    public static function addRuleAction($rule_id, $fact_id, $attribute_values)
    {
        $rule_action = new RuleAction();
        $rule_action->function = RuleAction::FUNCTION_ASSERT;
        foreach ($attribute_values as $attribute_value) {
            if ($attribute_value[0] == 'function')
                $rule_action->function = self::decodeText($attribute_value[1]);
        }
        $rule_action->rule = $rule_id;
        $rule_action->fact = $fact_id;
        $rule_action->save();
        // Поиск факта по его id
        $fact = Fact::findOne([$fact_id]);
        // Поиск шаблона факта, связанного с данным конкретным фактом, по его id
        $fact_template = FactTemplate::findOne($fact->fact_template);
        // Поиск правила по его id
        $rule = Rule::findOne([$rule_id]);
        // Поиск шаблона правила, связанного с данным конкретным правилом, по его id
        $rule_template = RuleTemplate::findOne($rule->rule_template);
        // Создание действия для найденного шаблона правила, если он был создан системой автоматически
        if ($rule_template->description == 'Данный шаблон правила был создан автоматически на основе конкретного правила.'
            ||
            $rule_template->description == 'This rule template was created automatically based on the concrete rule.') {
            $rule_template_action = new RuleTemplateAction();
            $rule_template_action->function = $rule_action->function;
            $rule_template_action->rule_template = $rule_template->id;
            $rule_template_action->fact_template = $fact_template->id;
            $rule_template_action->save();
        }

        return $rule_action->id;
    }

    /**
     * Изменение действия для правила.
     * @param $rule_action_id - идентификатор действия правила
     * @param $attribute_values - извлеченные значения атрибутов элемента
     */
    public static function editRuleAction($rule_action_id, $attribute_values)
    {
        $rule_action = RuleAction::findOne($rule_action_id);
        $rule_action->function = RuleAction::FUNCTION_ASSERT;
        foreach ($attribute_values as $attribute_value) {
            if ($attribute_value[0] == 'function')
                $rule_action->function = self::decodeText($attribute_value[1]);
        }
        $rule_action->save();
    }

    /**
     * Создание сложных правил с несколькими условиями и разными операторами.
     * @param $knowledge_base_id - идентификатор базы знаний (продукционной модели)
     * @param $complex_rule_array - массив извлеченных сложных продукционных правил из концептуальной модели
     */
    public static function addComplexRules($knowledge_base_id, $complex_rule_array)
    {
        // Приоритет правила
        $salience = 0;
        // Поиск всех простых правил принадлежащих данной базе знаний
        $rules = Rule::find()->where(array('production_model' => $knowledge_base_id))->all();
        // Обновление значения приоритета правила
        foreach ($rules as $rule)
            if ($rule->salience > $salience)
                $salience = $rule->salience;
        $salience += count($complex_rule_array);

        // Обход массива извлеченных сложных продукционных правил
        foreach ($complex_rule_array as $action_id => $conditions) {
            $rule_name = '';
            // Наименование факта действия
            $rule_action_name = Fact::findOne((int)$action_id)->name;
            // Обход всех условий с целью формирования наименования для данного правила
            foreach ($conditions as $condition) {
                // Наименование факта условия
                $rule_condition_name = Fact::findOne((int)$condition[0])->name;
                // Формирование наименования для сложного продукционного правила
                if ($rule_name == '')
                    $rule_name = $rule_condition_name;
                else
                    $rule_name .= '+' . $rule_condition_name;
            }
            $rule_name .= '->' . $rule_action_name;

            // Создание нового правила
            $rule = new Rule();
            // Вызов метода создания наименования для нового правила
            $rule->name = self::createRuleName($knowledge_base_id, $rule_name, $rule_name, 1);
            $rule->salience = $salience;
            $salience--;
            $rule->production_model = $knowledge_base_id;
            // Создание нового шаблона правила
            $rule_template = new RuleTemplate();
            // Вызов метода создания наименования для нового шаблона правила.
            // Данное наименование дублирует наименование правила
            $rule_template->name = self::createRuleTemplateName($knowledge_base_id, $rule_name, $rule_name, 1);
            $rule_template->description = Yii::t('app',
                'RULE_TEMPLATE_MODEL_DESCRIPTION_FOR_AUTOMATICALLY_CREATED_RULE_TEMPLATE');
            $rule_template->production_model = $knowledge_base_id;
            $rule_template->save();
            $rule->rule_template = $rule_template->id;
            $rule->save();

            // Обход всех условий с целью создания условий для данного правила
            foreach ($conditions as $condition) {
                $rule_condition = new RuleCondition();
                $rule_condition->operator = $condition[1];
                $rule_condition->rule = $rule->id;
                $rule_condition->fact = (int)$condition[0];
                $rule_condition->save();
                // Поиск факта по его id
                $fact = Fact::findOne($rule_condition->fact);
                // Поиск шаблона факта, связанного с данным конкретным фактом, по его id
                $fact_template = FactTemplate::findOne($fact->fact_template);
                // Создание условия для данного шаблона правила
                $rule_template_condition = new RuleTemplateCondition();
                $rule_template_condition->operator = $rule_condition->operator;
                $rule_template_condition->rule_template = $rule_template->id;
                $rule_template_condition->fact_template = $fact_template->id;
                $rule_template_condition->save();
            }

            // Создание действия для данного правила
            $rule_action = new RuleAction();
            $rule_action->function = RuleAction::FUNCTION_ASSERT;
            $rule_action->rule = $rule->id;
            $rule_action->fact = (int)$action_id;
            $rule_action->save();
            // Поиск факта по его id
            $fact = Fact::findOne($rule_action->fact);
            // Поиск шаблона факта, связанного с данным конкретным фактом, по его id
            $fact_template = FactTemplate::findOne($fact->fact_template);
            // Создание действия для данного шаблона правила
            $rule_template_action = new RuleTemplateAction();
            $rule_template_action->function = $rule_action->function;
            $rule_template_action->rule_template = $rule_template->id;
            $rule_template_action->fact_template = $fact_template->id;
            $rule_template_action->save();
        }
    }

    /**
     * Определение набора начальных фактов.
     * @param $knowledge_base_id - идентификатор базы знаний (продукционной модели)
     */
    public static function setInitialFacts($knowledge_base_id)
    {
        // Поиск всех фактов принадлежащих данной базе знаний
        $facts = Fact::find()->where(array('production_model' => $knowledge_base_id))->all();
        // Поиск всех правил принадлежащих данной базе знаний
        $rules = Rule::find()->where(array('production_model' => $knowledge_base_id))->all();
        // Поиск всех действий правил
        $rule_actions = RuleAction::find()->all();
        // Определение не начальных фактов
        foreach ($rules as $rule)
            foreach ($rule_actions as $rule_action)
                if ($rule_action->rule == $rule->id)
                    foreach ($facts as $fact)
                        if ($fact->id == $rule_action->fact) {
                            $current_fact = Fact::findOne($fact->id);
                            $current_fact->initial = false;
                            $current_fact->save();
                        }
    }
}