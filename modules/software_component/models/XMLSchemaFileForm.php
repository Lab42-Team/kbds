<?php

namespace app\modules\software_component\models;

use app\components\XSDFile;
use Yii;
use yii\base\Model;

/**
 * XMLSchemaFileForm class.
 * XMLSchemaFileForm является структурой данных для импортирования метамоделей в формате XML Schema (XSD).
 * Используется в SoftwareComponentsController и MetamodelsController.
 */
class XMLSchemaFileForm extends Model
{
    public $xml_schema_file; // переменная для импорта метамоделей в формате XML Schema (XSD)

    /**
     * Валидация загружаемого файла в формате XML Schema (XSD).
     * @return bool|void
     */
    public function validationFile()
    {
        // Получаем временно загруженный XSD-файл
        $file = $this->xml_schema_file->tempName;
        // Получаем XML-строки из XSD-файла
        $xml_rows = simplexml_load_file($file);
        // Создаем экземпляр класса XSDFile
        $xsd_file = new XSDFile();
        // Проверка корректности XML-схемы
        if(!$xsd_file->isXSD($xml_rows))
            return $this->addError(
                'xml_schema_file',
                Yii::t('app', 'XML_SCHEMA_FILE_FORM_MESSAGE_XML_SCHEMA_FILE_INCORRECT')
            );

        return true;
    }

    /**
     * @return array the validation rules
     */
    public function rules()
    {
        return array(
            array(['xml_schema_file'], 'required'),
            array(['xml_schema_file'], 'file', 'extensions'=>'xsd', 'checkExtensionByMimeType'=>false),
            array(['xml_schema_file'], 'validationFile', 'on'=>'validation_file'),
        );
    }

    /**
     * @return array customized attribute labels
     */
    public function attributeLabels()
    {
        return array(
            'xml_schema_file'=>Yii::t('app', 'XML_SCHEMA_FILE_FORM_XML_SCHEMA_FILE'),
        );
    }
}