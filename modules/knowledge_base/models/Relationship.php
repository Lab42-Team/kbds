<?php

namespace app\modules\knowledge_base\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%relationship}}".
 *
 * @property integer $id
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $name
 * @property boolean $is_association
 * @property boolean $is_inheritance
 * @property boolean is_equivalence
 * @property string $description
 * @property integer $ontology
 *
 * @property LeftHandSide[] $leftHandSides
 * @property KnowledgeBase $kbOntology
 * @property RightHandSide[] $rightHandSides
 */
class Relationship extends \yii\db\ActiveRecord
{
    /**
     * @return string table name
     */
    public static function tableName()
    {
        return '{{%relationship}}';
    }

    /**
     * @return array the validation rules
     */
    public function rules()
    {
        return [
            [['name', 'ontology'], 'required'],
            [['ontology'], 'integer'],
            [['is_association', 'is_inheritance', 'is_equivalence'], 'boolean'],
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
            'id' => Yii::t('app', 'RELATIONSHIP_MODEL_ID'),
            'created_at' => Yii::t('app', 'RELATIONSHIP_MODEL_CREATED_AT'),
            'updated_at' => Yii::t('app', 'RELATIONSHIP_MODEL_UPDATED_AT'),
            'name' => Yii::t('app', 'RELATIONSHIP_MODEL_NAME'),
            'is_association' => Yii::t('app', 'RELATIONSHIP_MODEL_IS_ASSOCIATION'),
            'is_inheritance' => Yii::t('app', 'RELATIONSHIP_MODEL_IS_INHERITANCE'),
            'is_equivalence' => Yii::t('app', 'RELATIONSHIP_MODEL_IS_EQUIVALENCE'),
            'description' => Yii::t('app', 'RELATIONSHIP_MODEL_DESCRIPTION'),
            'ontology' => Yii::t('app', 'RELATIONSHIP_MODEL_ONTOLOGY'),
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
        return $this->hasMany(LeftHandSide::className(), ['relationship' => 'id']);
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
    public function getRightHandSides()
    {
        return $this->hasMany(RightHandSide::className(), ['relationship' => 'id']);
    }
}