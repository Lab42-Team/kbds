<?php

namespace app\modules\knowledge_base\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%fact_slot}}".
 *
 * @property integer $id
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $name
 * @property string $value
 * @property string $description
 * @property integer $fact
 * @property integer $data_type
 *
 * @property DataType $dataType
 * @property Fact $fkFact
 */
class FactSlot extends \yii\db\ActiveRecord
{
    /**
     * @return string table name
     */
    public static function tableName()
    {
        return '{{%fact_slot}}';
    }

    /**
     * @return array the validation rules
     */
    public function rules()
    {
        return [
            [['name', 'fact', 'data_type'], 'required'],
            [['fact', 'data_type'], 'integer'],
            [['name', 'value'], 'string', 'max' => 250],
            [['description'], 'string', 'max' => 500]
        ];
    }

    /**
     * @return array customized attribute labels
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'FACT_SLOT_MODEL_ID'),
            'created_at' => Yii::t('app', 'FACT_SLOT_MODEL_CREATED_AT'),
            'updated_at' => Yii::t('app', 'FACT_SLOT_MODEL_UPDATED_AT'),
            'name' => Yii::t('app', 'FACT_SLOT_MODEL_NAME'),
            'value' => Yii::t('app', 'FACT_SLOT_MODEL_VALUE'),
            'description' => Yii::t('app', 'FACT_SLOT_MODEL_DESCRIPTION'),
            'fact' => Yii::t('app', 'FACT_SLOT_MODEL_FACT'),
            'data_type' => Yii::t('app', 'FACT_SLOT_MODEL_DATA_TYPE'),
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
    public function getDataType()
    {
        return $this->hasOne(DataType::className(), ['id' => 'data_type']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFkFact()
    {
        return $this->hasOne(Fact::className(), ['id' => 'fact']);
    }
}