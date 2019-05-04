<?php

namespace app\modules\knowledge_base\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%fact_template_slot}}".
 *
 * @property integer $id
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $name
 * @property string $default_value
 * @property string $description
 * @property integer $fact_template
 * @property integer $data_type
 *
 * @property DataType $dataType
 * @property FactTemplate $factTemplate
 */
class FactTemplateSlot extends \yii\db\ActiveRecord
{
    /**
     * @return string table name
     */
    public static function tableName()
    {
        return '{{%fact_template_slot}}';
    }

    /**
     * @return array the validation rules
     */
    public function rules()
    {
        return [
            [['name', 'fact_template', 'data_type'], 'required'],
            [['fact_template', 'data_type'], 'integer'],
            [['name', 'default_value'], 'string', 'max' => 250],
            ['name', 'unique', 'targetAttribute' => ['name', 'fact_template'],
                'message' => Yii::t('app', 'FACT_TEMPLATE_SLOT_MODEL_MESSAGE_NAME')],
            [['description'], 'string', 'max' => 500]
        ];
    }

    /**
     * @return array customized attribute labels
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'FACT_TEMPLATE_SLOT_MODEL_ID'),
            'created_at' => Yii::t('app', 'FACT_TEMPLATE_SLOT_MODEL_CREATED_AT'),
            'updated_at' => Yii::t('app', 'FACT_TEMPLATE_SLOT_MODEL_UPDATED_AT'),
            'name' => Yii::t('app', 'FACT_TEMPLATE_SLOT_MODEL_NAME'),
            'default_value' => Yii::t('app', 'FACT_TEMPLATE_SLOT_MODEL_DEFAULT_VALUE'),
            'description' => Yii::t('app', 'FACT_TEMPLATE_SLOT_MODEL_DESCRIPTION'),
            'fact_template' => Yii::t('app', 'FACT_TEMPLATE_SLOT_MODEL_FACT_TEMPLATE'),
            'data_type' => Yii::t('app', 'FACT_TEMPLATE_SLOT_MODEL_DATA_TYPE'),
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
    public function getFactTemplate()
    {
        return $this->hasOne(FactTemplate::className(), ['id' => 'fact_template']);
    }
}