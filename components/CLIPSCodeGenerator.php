<?php

namespace app\components;

use Yii;
use app\modules\knowledge_base\models\DataType;
use app\modules\knowledge_base\models\FactTemplate;
use app\modules\knowledge_base\models\FactTemplateSlot;
use app\modules\knowledge_base\models\RuleTemplate;
use app\modules\knowledge_base\models\Fact;
use app\modules\knowledge_base\models\FactSlot;
use app\modules\knowledge_base\models\Rule;
use app\modules\knowledge_base\models\RuleCondition;
use app\modules\knowledge_base\models\RuleAction;

/**
 * CLIPSCodeGenerator.
 * Класс CLIPSCodeGenerator обеспечивает генерацию (создание) кода продукционной базы знаний в формате CLIPS.
 */
class CLIPSCodeGenerator
{
    /**
     * Транслитерация текста на русском языке.
     * @param $string - строка текста на русском языке
     * @return string - строка транслитерированного текста с заменой пробелов на нижнее подчеркивание
     */
    public function transliteration($string) {
        $replace = array(
            "а"=>"a", "А"=>"A",
            "б"=>"b", "Б"=>"B",
            "в"=>"v", "В"=>"V",
            "г"=>"g", "Г"=>"G",
            "д"=>"d", "Д"=>"D",
            "е"=>"e", "Е"=>"E",
            "ж"=>"zh", "Ж"=>"Zh",
            "з"=>"z", "З"=>"Z",
            "и"=>"i", "И"=>"I",
            "й"=>"y", "Й"=>"Y",
            "к"=>"k", "К"=>"K",
            "л"=>"l", "Л"=>"L",
            "м"=>"m", "М"=>"M",
            "н"=>"n", "Н"=>"N",
            "о"=>"o", "О"=>"O",
            "п"=>"p", "П"=>"P",
            "р"=>"r", "Р"=>"R",
            "с"=>"s", "С"=>"S",
            "т"=>"t", "Т"=>"T",
            "у"=>"u", "У"=>"U",
            "ф"=>"f", "Ф"=>"F",
            "х"=>"h", "Х"=>"H",
            "ц"=>"c", "Ц"=>"C",
            "ч"=>"ch", "Ч"=>"Ch",
            "ш"=>"sh", "Ш"=>"Sh",
            "щ"=>"sch", "Щ"=>"Sch",
            "ъ"=>"", "Ъ"=>"",
            "ы"=>"y", "Ы"=>"Y",
            "ь"=>"", "Ь"=>"",
            "э"=>"e", "Э"=>"E",
            "ю"=>"yu", "Ю"=>"Yu",
            "я"=>"ya", "Я"=>"Ya",
        );
        $str = iconv("UTF-8", "UTF-8//IGNORE", strtr($string, $replace));

        return str_replace(' ', '_', $str);
    }

    /**
     * Проверка существования сгенерированных элементов у продукционной модели.
     * @param $id - идентификатор базы знаний (продукционной модели)
     * @return bool - результат проверки
     */
    public function existElements($id)
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
     * Генерация кода описания шаблонов фактов (deftemplate).
     * @param $fact_templates - шаблоны фактов
     * @param $fact_template_slots - слоты шаблонов фактов
     * @param $content - текущий текст CLP-файла базы знаний
     * @return string - сформированный текст CLP-файла базы знаний включающий шаблоны фактов
     */
    public function generateDefTemplates($fact_templates, $fact_template_slots, $content)
    {
        // Запись шаблонов фактов
        $content .= ";*********************************** " . Yii::t('app', 'CLIPS_CODE_TEMPLATES')
            . " ***************************************\r\n";
        // Обход всех шаблонов фактов данной базы знаний
        foreach ($fact_templates as $fact_template) {
            // Формирование шаблона факта (начало)
            if ($fact_template->description == '')
                $content .= "(deftemplate " . self::transliteration($fact_template->name) . "\r\n";
            else
                $content .= "(deftemplate " . self::transliteration($fact_template->name) . " ;" .
                    str_replace(array("\r", "\n"), ' ', $fact_template->description) . "\r\n";
            // Обход всех слотов для данного шаблона факта
            foreach ($fact_template_slots as $fact_template_slot)
                if ($fact_template_slot->fact_template == $fact_template->id)
                    // Формирование слотов шаблона факта
                    if ($fact_template_slot->description == '')
                        $content .= "\t(slot " . self::transliteration($fact_template_slot->name) . " (default " . '"' .
                            self::transliteration($fact_template_slot->default_value) . '"' . "))\r\n";
                    else
                        $content .= "\t(slot " . self::transliteration($fact_template_slot->name) . " (default " . '"' .
                            self::transliteration($fact_template_slot->default_value) . '"' . ")) ;" .
                            str_replace(array("\r", "\n"), ' ', $fact_template_slot->description) . "\r\n";
            // Запись окончания шаблона факта
            $content .= ") \r\n\r\n";
        }

        return $content;
    }

    /**
     * Генерация кода описания слотов для факта.
     * @param $fact - факт
     * @param $fact_slots - слоты факта
     * @param $space - отступ для записи слота
     * @param $content - текущий текст CLP-файла базы знаний
     * @return string - сформированный текст CLP-файла базы знаний включающий слоты факта
     */
    public function generateFactSlots($fact, $fact_slots, $space, $content)
    {
        // Обход всех слотов для данного факта
        foreach ($fact_slots as $fact_slot)
            if ($fact_slot->fact == $fact->id)
                // Формирование слотов факта
                if ($fact_slot->description == '')
                    $content .= $space . "(" . self::transliteration($fact_slot->name) . ' "' .
                        self::transliteration($fact_slot->value) . '"' . ")\r\n";
                else
                    $content .= $space . "(" . self::transliteration($fact_slot->name) . ' "' .
                        self::transliteration($fact_slot->value) . '"' . ") ;" .
                        str_replace(array("\r", "\n"), ' ', $fact_slot->description) . "\r\n";

        return $content;
    }

    /**
     * Генерация кода описания начального набора фактов (deffacts).
     * @param $facts - факты
     * @param $fact_slots - слоты фактов
     * @param $content - текущий текст CLP-файла базы знаний
     * @return string - сформированный текст CLP-файла базы знаний включающий факты
     */
    public function generateFacts($facts, $fact_slots, $content)
    {
        // Запись фактов
        $content .= ";*********************************** " . Yii::t('app', 'CLIPS_CODE_FACTS')
            . " ***************************************\r\n";
        // Запись блока фактов
        $content .= "(deffacts initial-settings\r\n";
        // Обход всех фактов данной базы знаний
        foreach ($facts as $fact) {
            // Если факт является начальным
            if ($fact->initial) {
                // Формирование факта (начало)
                if ($fact->description == '')
                    $content .= "\t(" . self::transliteration($fact->name) . "\r\n";
                else
                    $content .= "\t(" . self::transliteration($fact->name) . " ;" .
                        str_replace(array("\r", "\n"), ' ', $fact->description) . "\r\n";
                // Генерация кода описания слотов факта
                $content = self::generateFactSlots($fact, $fact_slots, "\t\t", $content);
                // Запись окончания факта
                $content .= "\t) \r\n";
            }
        }
        // Запись окончания блока фактов
        $content .= ") \r\n\r\n";

        return $content;
    }

    /**
     * Генерация кода описания правил (defrule).
     * @param $rules - правила
     * @param $rule_conditions - условия правил
     * @param $rule_actions - действия правил
     * @param $facts - факты
     * @param $fact_slots - слоты фактов
     * @param $content - текущий текст CLP-файла базы знаний
     * @return string - сформированный текст CLP-файла базы знаний включающий правила
     */
    public function generateRules($rules, $rule_conditions, $rule_actions, $facts, $fact_slots, $content)
    {
        // Запись правил
        $content .= ";*********************************** " . Yii::t('app', 'CLIPS_CODE_RULES')
            . " ***************************************\r\n";
        // Обход всех правил данной базы знаний
        foreach ($rules as $rule) {
            // Формирование правила (начало)
            if ($rule->description == '')
                $content .= "(defrule " . self::transliteration($rule->name) . "\r\n";
            else
                $content .= "(defrule " . self::transliteration($rule->name) . ' "' .
                    str_replace(array("\r", "\n"), ' ', $rule->description) . '"' . "\r\n";
            // Запись приоритета правила
            if ($rule->salience != 0)
                $content .= "\t(declare (salience " . $rule->salience . "))\r\n";

            // Массив для хранения id фактов (условий) с оператором "ИЛИ"
            $special_condition_array = array();
            //
            $exist_operator = false;
            foreach ($rule_conditions as $rule_condition)
                if ($rule_condition->rule == $rule->id)
                    foreach ($facts as $fact)
                        if ($fact->id == $rule_condition->fact) {
                            // Формирование массива id фактов (условий) с оператором "ИЛИ"
                            if ($rule_condition->operator == RuleCondition::OPERATOR_OR)
                                array_push($special_condition_array, $fact->id);
                            //
                            if ($rule_condition->operator == RuleCondition::OPERATOR_NONE ||
                                $rule_condition->operator == RuleCondition::OPERATOR_AND ||
                                $rule_condition->operator == RuleCondition::OPERATOR_NOT)
                                $exist_operator = true;
                        }

            // Если кол-во id фактов (условий) с оператором "ИЛИ" в массив равно одному и
            // существуют факты (условия) с другими операторами
            if (count($special_condition_array) == 1 && $exist_operator) {
                // Запись условий правила с оператором "ИЛИ"
                $content .= "\t(or \r\n";
                // Запись факта образца (условия правила с оператором "ИЛИ")
                foreach ($special_condition_array as $rule_condition_id)
                    foreach ($facts as $fact)
                        if ($fact->id == $rule_condition_id) {
                            // Формирование факта образца
                            if ($fact->description == '')
                                $content .= "\t\t(" . self::transliteration($fact->name) . "\r\n";
                            else
                                $content .= "\t\t(" . self::transliteration($fact->name) . " ;" .
                                    str_replace(array("\r", "\n"), ' ', $fact->description) . "\r\n";
                            // Генерация кода описания слотов факта (условия)
                            $content = self::generateFactSlots($fact, $fact_slots, "\t\t\t", $content);
                            // Запись окончания факта образца
                            $content .= "\t\t) \r\n";
                        }
                // Запись условий правила с операторами "И" и "НЕ"
                $content .= "\t\t(and \r\n";
                foreach ($rule_conditions as $rule_condition)
                    if ($rule_condition->rule == $rule->id)
                        foreach ($facts as $fact)
                            if ($fact->id == $rule_condition->fact) {
                                // Если оператор условия "И"
                                if ($rule_condition->operator == RuleCondition::OPERATOR_NONE ||
                                    $rule_condition->operator == RuleCondition::OPERATOR_AND
                                ) {
                                    // Формирование фактов образцов
                                    if ($fact->description == '')
                                        $content .= "\t\t\t(" . self::transliteration($fact->name) . "\r\n";
                                    else
                                        $content .= "\t\t\t(" . self::transliteration($fact->name) . " ;" .
                                            str_replace(array("\r", "\n"), ' ', $fact->description) . "\r\n";
                                    // Генерация кода описания слотов факта (условия)
                                    $content = self::generateFactSlots($fact, $fact_slots, "\t\t\t\t", $content);
                                    // Запись окончания факта образца
                                    $content .= "\t\t\t) \r\n";
                                }
                                // Если оператор условия "НЕ"
                                if ($rule_condition->operator == RuleCondition::OPERATOR_NOT) {
                                    // Формирование фактов образцов
                                    if ($fact->description == '')
                                        $content .= "\t\t\t(not (" . self::transliteration($fact->name) . "\r\n";
                                    else
                                        $content .= "\t\t\t(not (" . self::transliteration($fact->name) . " ;" .
                                            str_replace(array("\r", "\n"), ' ', $fact->description) . "\r\n";
                                    // Генерация кода описания слотов факта (условия)
                                    $content = self::generateFactSlots($fact, $fact_slots, "\t\t\t\t", $content);
                                    // Запись окончания факта образца
                                    $content .= "\t\t\t)) \r\n";
                                }
                            }
                // Запись окончания условий правила с операторами "И" и "НЕ"
                $content .= "\t\t) \r\n";
                // Запись окончания условий правила с оператором "ИЛИ"
                $content .= "\t) \r\n";
            }

            // Если кол-во id фактов (условий) с оператором "ИЛИ" в массив равно одному и
            // нет фактов (условий) с другими операторами
            if (count($special_condition_array) == 1 && $exist_operator == false)
                // Запись факта образца
                foreach ($special_condition_array as $rule_condition_id)
                    foreach ($facts as $fact)
                        if ($fact->id == $rule_condition_id) {
                            // Формирование факта образца
                            if ($fact->description == '')
                                $content .= "\t(" . self::transliteration($fact->name) . "\r\n";
                            else
                                $content .= "\t(" . self::transliteration($fact->name) . " ;" .
                                    str_replace(array("\r", "\n"), ' ', $fact->description) . "\r\n";
                            // Генерация кода описания слотов факта (условия)
                            $content = self::generateFactSlots($fact, $fact_slots, "\t\t", $content);
                            // Запись окончания факта образца
                            $content .= "\t) \r\n";
                        }

            // Запись условий правила с операторами "И" и "НЕ",
            // если кол-во id фактов (условий) с оператором "ИЛИ" в массив не равно одному
            foreach ($rule_conditions as $rule_condition)
                if ($rule_condition->rule == $rule->id)
                    foreach ($facts as $fact)
                        if ($fact->id == $rule_condition->fact) {
                            // Если оператор условия "И"
                            if ($rule_condition->operator == RuleCondition::OPERATOR_NONE ||
                                $rule_condition->operator == RuleCondition::OPERATOR_AND &&
                                count($special_condition_array) != 1
                            ) {
                                // Формирование фактов образцов
                                if ($fact->description == '')
                                    $content .= "\t(" . self::transliteration($fact->name) . "\r\n";
                                else
                                    $content .= "\t(" . self::transliteration($fact->name) . " ;" .
                                        str_replace(array("\r", "\n"), ' ', $fact->description) . "\r\n";
                                // Генерация кода описания слотов факта (условия)
                                $content = self::generateFactSlots($fact, $fact_slots, "\t\t", $content);
                                // Запись окончания факта образца
                                $content .= "\t) \r\n";
                            }
                            // Если оператор условия "НЕ"
                            if ($rule_condition->operator == RuleCondition::OPERATOR_NOT &&
                                count($special_condition_array) > 1) {
                                // Формирование фактов образцов
                                if ($fact->description == '')
                                    $content .= "\t(not (" . self::transliteration($fact->name) . "\r\n";
                                else
                                    $content .= "\t(not (" . self::transliteration($fact->name) . " ;" .
                                        str_replace(array("\r", "\n"), ' ', $fact->description) . "\r\n";
                                // Генерация кода описания слотов факта (условия)
                                $content = self::generateFactSlots($fact, $fact_slots, "\t\t", $content);
                                // Запись окончания факта образца
                                $content .= "\t)) \r\n";
                            }
                        }
            // Если кол-во id фактов (условий) с оператором "ИЛИ" в массив больше одного
            if (count($special_condition_array) > 1) {
                // Запись условий правила с оператором "ИЛИ"
                $content .= "\t(or \r\n";
                foreach ($special_condition_array as $rule_condition_id)
                    foreach ($facts as $fact)
                        if ($fact->id == $rule_condition_id) {
                            // Формирование фактов образцов
                            if ($fact->description == '')
                                $content .= "\t\t(" . self::transliteration($fact->name) . "\r\n";
                            else
                                $content .= "\t\t(" . self::transliteration($fact->name) . " ;" .
                                    str_replace(array("\r", "\n"), ' ', $fact->description) . "\r\n";
                            // Генерация кода описания слотов факта (условия)
                            $content = self::generateFactSlots($fact, $fact_slots, "\t\t\t", $content);
                            // Запись окончания факта образца
                            $content .= "\t\t) \r\n";
                        }
                $content .= "\t) \r\n";
            }
            // Запись переходя от блока условий к блоку действий
            $content.= "\t=> \r\n";
            // Запись действий правила
            foreach ($rule_actions as $rule_action)
                if ($rule_action->rule == $rule->id)
                    foreach ($facts as $fact)
                        if ($fact->id == $rule_action->fact) {
                            // Формирование команды (функции) действия
                            if ($rule_action->function == RuleAction::FUNCTION_NONE)
                                $content .= "\t(assert\r\n";
                            else
                                $content .= "\t(" . $rule_action->function . "\r\n";
                            // Формирование фактов (действия)
                            if ($fact->description == '')
                                $content .= "\t\t(" . self::transliteration($fact->name) . "\r\n";
                            else
                                $content .= "\t\t(" . self::transliteration($fact->name) . " ;" .
                                    str_replace(array("\r", "\n"), ' ', $fact->description) . "\r\n";
                            // Генерация кода описания слотов факта (действия)
                            $content = self::generateFactSlots($fact, $fact_slots, "\t\t\t", $content);
                            // Запись окончания факта (действия)
                            $content .= "\t\t) \r\n";
                            // Запись окончания команды (функции) действий
                            $content .= "\t) \r\n";
                        }
            // Запись окончания правила
            $content.= ") \r\n\r\n";
        }

        return $content;
    }

    /**
     * Генерация кода базы знаний в формате CLIPS.
     * @param $knowledge_base - база знаний
     */
    public function generateCLIPSCode($knowledge_base)
    {
        // Поиск всех шаблонов фактов принадлежащие данной базе знаний
        $fact_templates = FactTemplate::find()->where(array('production_model' => $knowledge_base->id))->all();
        // Поиск всех фактов принадлежащие данной базе знаний
        $facts = Fact::find()->where(array('production_model' => $knowledge_base->id))->all();
        // Поиск всех правил принадлежащие данной базе знаний
        $rules = Rule::find()->where(array('production_model' => $knowledge_base->id))->all();
        // Поиск всех слотов шаблонов фактов
        $fact_template_slots = FactTemplateSlot::find()->all();
        // Поиск всех слотов фактов
        $fact_slots = FactSlot::find()->all();
        // Поиск всех условий правила
        $rule_conditions = RuleCondition::find()->all();
        // Поиск всех действий правила
        $rule_actions = RuleAction::find()->all();

        // Определение наименования файла
        $file = 'exported_knowledge_base.clp';
        // Создание и открытие данного файла на запись, если он не существует
        if (!file_exists($file))
            fopen($file, 'w');

        // Начальное описание файла базы знаний
        $content = ";***********************************************************************************\r\n";
        $content .= ";" . Yii::t('app', 'KNOWLEDGE_BASES_PAGE_KNOWLEDGE_BASE') . ":\r\n";
        $content .= ";***********************************************************************************\r\n";
        $content .= ";[" . Yii::t('app', 'KNOWLEDGE_BASE_MODEL_NAME') . "] - " . $knowledge_base->name . "\r\n";
        if ($knowledge_base->description != '')
            $content .= ";[" . Yii::t('app', 'KNOWLEDGE_BASE_MODEL_DESCRIPTION') . "] - " .
                str_replace(array("\r", "\n"), ' ', $knowledge_base->description) . "\r\n";
        $content .= ";***********************************************************************************\r\n";
        $content .= "\r\n";

        // Вызов метода генерации шаблонов фактов CLIPS, если шаблоны фактов существуют
        if (!empty($fact_templates))
            $content = self::generateDefTemplates($fact_templates, $fact_template_slots, $content);

        // Вызов метода генерации начального набора фактов CLIPS, если факты существуют
        if (!empty($facts))
            $content = self::generateFacts($facts, $fact_slots, $content);

        // Вызов метода генерации правил CLIPS, если правила существуют
        if (!empty($rules))
            $content = self::generateRules($rules, $rule_conditions, $rule_actions, $facts, $fact_slots, $content);

        // Выдача CLP-файла пользователю для скачивания
        header("Content-type: application/octet-stream");
        header('Content-Disposition: filename="'.$file.'"');
        echo $content;
        exit;
    }
}