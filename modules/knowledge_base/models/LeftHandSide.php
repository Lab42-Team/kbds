<?php

namespace app\modules\knowledge_base\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%left_hand_side}}".
 *
 * @property integer $id
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $ontology_class
 * @property integer $relationship
 *
 * @property OntologyClass $ontologyClass
 * @property Relationship $kbRelationship
 */
class LeftHandSide extends \yii\db\ActiveRecord
{
    /**
     * @return string table name
     */
    public static function tableName()
    {
        return '{{%left_hand_side}}';
    }

    /**
     * @return array the validation rules
     */
    public function rules()
    {
        return [
            [['ontology_class', 'relationship'], 'required'],
            [['ontology_class', 'relationship'], 'integer']
        ];
    }

    /**
     * @return array customized attribute labels
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'LEFT_HAND_SIDE_MODEL_ID'),
            'created_at' => Yii::t('app', 'LEFT_HAND_SIDE_MODEL_CREATED_AT'),
            'updated_at' => Yii::t('app', 'LEFT_HAND_SIDE_MODEL_UPDATED_AT'),
            'ontology_class' => Yii::t('app', 'LEFT_HAND_SIDE_MODEL_ONTOLOGY_CLASS'),
            'relationship' => Yii::t('app', 'LEFT_HAND_SIDE_MODEL_RELATIONSHIP'),
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
    public function getKbRelationship()
    {
        return $this->hasOne(Relationship::className(), ['id' => 'relationship']);
    }
}