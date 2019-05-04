<?php

namespace app\modules\knowledge_base\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%object}}".
 *
 * @property integer $id
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $name
 * @property string $description
 * @property integer $ontology
 * @property integer $ontology_class
 *
 * @property KnowledgeBase $kbOntology
 * @property OntologyClass $ontologyClass
 */
class Object extends \yii\db\ActiveRecord
{
    /**
     * @return string table name
     */
    public static function tableName()
    {
        return '{{%object}}';
    }

    /**
     * @return array customized attribute labels
     */
    public function rules()
    {
        return [
            [['name', 'ontology', 'ontology_class'], 'required'],
            [['ontology', 'ontology_class'], 'integer'],
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
            'id' => Yii::t('app', 'OBJECT_MODEL_ID'),
            'created_at' => Yii::t('app', 'OBJECT_MODEL_CREATED_AT'),
            'updated_at' => Yii::t('app', 'OBJECT_MODEL_UPDATED_AT'),
            'name' => Yii::t('app', 'OBJECT_MODEL_NAME'),
            'description' => Yii::t('app', 'OBJECT_MODEL_DESCRIPTION'),
            'ontology' => Yii::t('app', 'OBJECT_MODEL_ONTOLOGY'),
            'ontology_class' => Yii::t('app', 'OBJECT_MODEL_ONTOLOGY_CLASS'),
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
    public function getKbOntology()
    {
        return $this->hasOne(KnowledgeBase::className(), ['id' => 'ontology']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOntologyClass()
    {
        return $this->hasOne(OntologyClass::className(), ['id' => 'ontology_class']);
    }
}