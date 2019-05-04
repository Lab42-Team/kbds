<?php

namespace app\modules\knowledge_base\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%ontology_class}}".
 *
 * @property integer $id
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $name
 * @property string $description
 * @property integer $ontology
 *
 * @property LeftHandSide[] $leftHandSides
 * @property Object[] $objects
 * @property KnowledgeBase $kbOntology
 * @property Property[] $properties
 * @property PropertyValue[] $propertyValues
 * @property RightHandSide[] $rightHandSides
 */
class OntologyClass extends \yii\db\ActiveRecord
{
    /**
     * @return string table name
     */
    public static function tableName()
    {
        return '{{%ontology_class}}';
    }

    /**
     * @return array the validation rules
     */
    public function rules()
    {
        return [
            [['name', 'ontology'], 'required'],
            [['ontology'], 'integer'],
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
            'id' => Yii::t('app', 'ONTOLOGY_CLASS_MODEL_ID'),
            'created_at' => Yii::t('app', 'ONTOLOGY_CLASS_MODEL_CREATED_AT'),
            'updated_at' => Yii::t('app', 'ONTOLOGY_CLASS_MODEL_UPDATED_AT'),
            'name' => Yii::t('app', 'ONTOLOGY_CLASS_MODEL_NAME'),
            'description' => Yii::t('app', 'ONTOLOGY_CLASS_MODEL_DESCRIPTION'),
            'ontology' => Yii::t('app', 'ONTOLOGY_CLASS_MODEL_ONTOLOGY'),
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
    public function getLeftHandSides()
    {
        return $this->hasMany(LeftHandSide::className(), ['ontology_class' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getObjects()
    {
        return $this->hasMany(Object::className(), ['ontology_class' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOntology0()
    {
        return $this->hasOne(KnowledgeBase::className(), ['id' => 'ontology']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProperties()
    {
        return $this->hasMany(Property::className(), ['ontology_class' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPropertyValues()
    {
        return $this->hasMany(PropertyValue::className(), ['ontology_class' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRightHandSides()
    {
        return $this->hasMany(RightHandSide::className(), ['ontology_class' => 'id']);
    }
}