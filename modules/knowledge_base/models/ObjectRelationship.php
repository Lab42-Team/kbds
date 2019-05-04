<?php

namespace app\modules\knowledge_base\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%object_relationship}}".
 *
 * @property integer $id
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $name
 * @property string $description
 * @property integer $relationship
 * @property integer $object
 *
 * @property Object $kbObject
 * @property Relationship $kbRelationship
 */
class ObjectRelationship extends \yii\db\ActiveRecord
{
    /**
     * @return string table name
     */
    public static function tableName()
    {
        return '{{%object_relationship}}';
    }

    /**
     * @return array the validation rules
     */
    public function rules()
    {
        return [
            [['created_at', 'updated_at', 'relationship', 'object'], 'required'],
            [['created_at', 'updated_at', 'relationship', 'object'], 'integer'],
            [['name', 'description'], 'string', 'max' => 255],
            [['object'], 'exist', 'skipOnError' => true, 'targetClass' => Object::className(),
                'targetAttribute' => ['object' => 'id']],
            [['relationship'], 'exist', 'skipOnError' => true, 'targetClass' => Relationship::className(),
                'targetAttribute' => ['relationship' => 'id']],
        ];
    }

    /**
     * @return array customized attribute labels
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'OBJECT_RELATIONSHIP_MODEL_ID'),
            'created_at' => Yii::t('app', 'OBJECT_RELATIONSHIP_MODEL_CREATED_AT'),
            'updated_at' => Yii::t('app', 'OBJECT_RELATIONSHIP_MODEL_UPDATED_AT'),
            'name' => Yii::t('app', 'OBJECT_RELATIONSHIP_MODEL_NAME'),
            'description' => Yii::t('app', 'OBJECT_RELATIONSHIP_MODEL_DESCRIPTION'),
            'relationship' => Yii::t('app', 'OBJECT_RELATIONSHIP_MODEL_RELATIONSHIP'),
            'object' => Yii::t('app', 'OBJECT_RELATIONSHIP_MODEL_OBJECT'),
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
    public function getKbObject()
    {
        return $this->hasOne(Object::className(), ['id' => 'object']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getKbRelationship()
    {
        return $this->hasOne(Relationship::className(), ['id' => 'relationship']);
    }
}