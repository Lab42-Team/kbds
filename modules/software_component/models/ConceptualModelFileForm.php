<?php

namespace app\modules\software_component\models;

use Yii;
use yii\base\Model;
use yii\base\ErrorException;

/**
 * ConceptualModelFileForm class.
 * ConceptualModelFileForm является структурой данных для импортирования концептуальных моделей в формате XML.
 * Используется в SoftwareComponentsController и MetamodelsController.
 */
class ConceptualModelFileForm extends Model
{
    public $conceptual_model_file; // переменная для импорта концептуальной модели в формате XML

    /**
     * Валидация загружаемого файла в формате XML.
     * @return bool|void
     */
    public function validationFile()
    {
        try {
            $file = $this->conceptual_model_file->tempName;
            simplexml_load_file($file);
        } catch (ErrorException $error) {
            // Получение короткого текста ошибки
            $error_text = $error->getMessage();
            $pos = strpos($error_text, '.tmp:') + 5;
            $error_text = substr($error_text, $pos);
            $pos = strpos($error_text, ':') + 1;
            $error_text = substr($error_text, $pos);
            // Вывод сообщения об ошибке импортирования
            Yii::$app->getSession()->setFlash('error',
                Yii::t('app', 'CONCEPTUAL_MODEL_FILE_FORM_MESSAGE_CONCEPTUAL_MODEL_FILE_FAIL'));
            return $this->addError(
                'conceptual_model_file',
                Yii::t('app', 'CONCEPTUAL_MODEL_FILE_FORM_MESSAGE_CONCEPTUAL_MODEL_FILE_INCORRECT') . $error_text
            );
        }

        return true;
    }

    /**
     * @return array the validation rules
     */
    public function rules()
    {
        return array(
            array(['conceptual_model_file'], 'required'),
            array(['conceptual_model_file'], 'file', 'extensions'=>'xml', 'checkExtensionByMimeType'=>false),
            array(['conceptual_model_file'], 'validationFile', 'on'=>'validation_file'),
        );
    }

    /**
     * @return array customized attribute labels
     */
    public function attributeLabels()
    {
        return array(
            'conceptual_model_file'=>Yii::t('app', 'CONCEPTUAL_MODEL_FILE_FORM_CONCEPTUAL_MODEL_FILE'),
        );
    }
}