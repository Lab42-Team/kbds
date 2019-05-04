<?php

namespace app\modules\knowledge_base\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%property}}".
 *
 * @property integer $id
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $name
 * @property string $description
 * @property integer $ontology_class
 * @property integer $data_type
 *
 * @property OntologyClass $ontologyClass
 * @property DataType $dataType
 * @property PropertyValue[] $propertyValues
 */
class Property extends \yii\db\ActiveRecord
{
    /**
     * @return string table name
     */
    public static function tableName()
    {
        return '{{%property}}';
    }

    /**
     * @return array the validation rules
     */
    public function rules()
    {
        return [
            [['name', 'ontology_class', 'data_type'], 'required'],
            [['ontology_class', 'data_type'], 'integer'],
            [['name'], 'string', 'max' => 250],
            [['description'], 'string', 'max' => 500]
        ];
    }

    /**
     * @return array customized attribute labels
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'PROPERTY_MODEL_ID'),
            'created_at' => Yii::t('app', 'PROPERTY_MODEL_CREATED_AT'),
            'updated_at' => Yii::t('app', 'PROPERTY_MODEL_UPDATED_AT'),
            'name' => Yii::t('app', 'PROPERTY_MODEL_NAME'),
            'description' => Yii::t('app', 'PROPERTY_MODEL_DESCRIPTION'),
            'ontology_class' => Yii::t('app', 'PROPERTY_MODEL_ONTOLOGY_CLASS'),
            'data_type' => Yii::t('app', 'PROPERTY_MODEL_DATA_TYPE'),
        ];
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOntologyClass()
    {
        return $this->hasOne(OntologyClass::className(), ['id' => 'ontology_class']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDataType()
    {
        return $this->hasOne(DataType::className(), ['id' => 'data_type']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPropertyValues()
    {
        return $this->hasMany(PropertyValue::className(), ['property' => 'id']);
    }
}