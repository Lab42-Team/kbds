<?php

namespace app\components;

use Yii;

/**
 * XSDFile class.
 * Класс XSDFile обеспечивает обработку XML-схем (XSD) концептуальных моделей
 * (создание метамодели по XML-схеме концептуальных моделей).
 */
class XSDFile
{
    const XSD_NAMESPACES = 'http://www.w3.org/2001/XMLSchema';

    public $schema_prefix = '';

    /**
     * Поиск дочерних тегов внутри родителького тега.
     * @param $parent_tag - тег в котором будет происходить поиск
     * @param $namespace - пространство имен
     * @return mixed - все вложенные теги
     */
    public function getChildTags($parent_tag, $namespace)
    {
        $namespaces = $parent_tag->getNamespaces(true);
        $child_tags = $parent_tag->children($namespaces[$namespace]);

        return $child_tags;
    }

    /**
     * Проверка корректности импортированного XSD-файла.
     * @param $xml_rows - набор XML-строк XSD-файла
     * @return bool - результат проверки
     */
    public function isXSD($xml_rows)
    {
        // Переменная результата проверки
        $is_xsd = false;
        // Получаем только используемое пространство имен корневого узла.
        $namespaces = $xml_rows->getNamespaces(true);
        // Проверяем что данное пространство имен соответствует пространству имен XSD
        foreach ($namespaces as $key => $value)
            if ($value == self::XSD_NAMESPACES)
            {
                // Запоминаем префикс схемы
                $this->schema_prefix = $key;
                $is_xsd = true;
            }

        return $is_xsd;
    }

    /**
     * Импортирование элементов XML-схемы.
     * @param $xml_rows - набор XML-строк XSD-файла
     * @return string
     */
    public function importElements($xml_rows)
    {
        $foo = $this->schema_prefix;
        // Обходим все теги внутри XML-схемы
        foreach ($xml_rows->children($this->schema_prefix, true) as $child)
        {
            // Поиск глобальных элементов
            if($child->getName() == 'element')
                $foo = $foo .  ", " . $child->attributes()->name;
        }

        return $foo;
    }
}