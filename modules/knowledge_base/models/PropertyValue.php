<?php

namespace app\modules\knowledge_base\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%property_value}}".
 *
 * @property integer $id
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $name
 * @property integer $property
 * @property integer $object
 *
 * @property Property $kbProperty
 * @property Property $kbObject
 */
class PropertyValue extends \yii\db\ActiveRecord
{
    /**
     * @return string table name
     */
    public static function tableName()
    {
        return '{{%property_value}}';
    }

    /**
     * @return array the validation rules
     */
    public function rules()
    {
        return [
            [['name', 'property', 'object'], 'required'],
            [['property', 'object'], 'integer'],
            [['name'], 'string', 'max' => 250]
        ];
    }

    /**
     * @return array customized attribute labels
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'PROPERTY_VALUE_MODEL_ID'),
            'created_at' => Yii::t('app', 'PROPERTY_VALUE_MODEL_CREATED_AT'),
            'updated_at' => Yii::t('app', 'PROPERTY_VALUE_MODEL_UPDATED_AT'),
            'name' => Yii::t('app', 'PROPERTY_VALUE_MODEL_NAME'),
            'property' => Yii::t('app', 'PROPERTY_VALUE_MODEL_PROPERTY'),
            'object' => Yii::t('app', 'PROPERTY_VALUE_MODEL_OBJECT'),
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
    public function getKbProperty()
    {
        return $this->hasOne(Property::className(), ['id' => 'property']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getKbObject()
    {
        return $this->hasOne(Object::className(), ['id' => 'object']);
    }
}