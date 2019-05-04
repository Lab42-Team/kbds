<?php

namespace app\modules\software_component\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%transformation_rule}}".
 *
 * @property integer $id
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $type
 * @property integer $transformation_model
 * @property integer $source_metaclass
 * @property integer $target_metaclass
 * @property integer $priority
 *
 * @property Metaclass $sourceMetaclass
 * @property Metaclass $targetMetaclass
 * @property TransformationModel $transformationModel
 */
class TransformationRule extends \yii\db\ActiveRecord
{
    const TYPE_SIMPLE_RULE = 0;    // Простые правила определения
    const TYPE_REFERENCE_RULE = 1; // Правила связи (по идентификаторам)
    const TYPE_COMPOSITE_RULE = 2; // Составные правила определения

    /**
     * @return string table name
     */
    public static function tableName()
    {
        return '{{%transformation_rule}}';
    }

    /**
     * @return array the validation rules
     */
    public function rules()
    {
        return [
            [['type', 'transformation_model', 'source_metaclass', 'target_metaclass', 'priority'], 'required'],
            [['type', 'transformation_model', 'source_metaclass', 'target_metaclass', 'priority'], 'integer'],
            // Номер приоритета больше или равен 1
            [['priority'], 'compare', 'compareValue' => 1, 'operator' => '>='],
            // Проверка уникальности номера приоритета
            //[['priority'], 'unique', 'message' => Yii::t('app', 'TRANSFORMATION_RULE_MODEL_MESSAGE_PRIORITY')],
        ];
    }

    /**
     * @return array customized attribute labels
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'TRANSFORMATION_RULE_MODEL_ID'),
            'created_at' => Yii::t('app', 'TRANSFORMATION_RULE_MODEL_CREATED_AT'),
            'updated_at' => Yii::t('app', 'TRANSFORMATION_RULE_MODEL_UPDATED_AT'),
            'type' => Yii::t('app', 'TRANSFORMATION_RULE_MODEL_TYPE'),
            'transformation_model' => Yii::t('app', 'TRANSFORMATION_RULE_MODEL_TRANSFORMATION_MODEL'),
            'source_metaclass' => Yii::t('app', 'TRANSFORMATION_RULE_MODEL_SOURCE_METACLASS'),
            'target_metaclass' => Yii::t('app', 'TRANSFORMATION_RULE_MODEL_TARGET_METACLASS'),
            'priority' => Yii::t('app', 'TRANSFORMATION_RULE_MODEL_PRIORITY'),
        ];
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    /**
     * Получение списка типов правил трансформации.
     * @return array - массив всех возможных типов правил трансформации
     */
    public static function getTypesArray()
    {
        return [
            self::TYPE_SIMPLE_RULE => Yii::t('app', 'TRANSFORMATION_RULE_MODEL_TYPE_SIMPLE_RULE'),
            self::TYPE_REFERENCE_RULE => Yii::t('app', 'TRANSFORMATION_RULE_MODEL_TYPE_REFERENCE_RULE'),
            self::TYPE_COMPOSITE_RULE => Yii::t('app', 'TRANSFORMATION_RULE_MODEL_TYPE_COMPOSITE_RULE'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSourceMetaclass()
    {
        return $this->hasOne(Metaclass::className(), ['id' => 'source_metaclass']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTargetMetaclass()
    {
        return $this->hasOne(Metaclass::className(), ['id' => 'target_metaclass']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTransformationModel()
    {
        return $this->hasOne(TransformationModel::className(), ['id' => 'transformation_model']);
    }
}