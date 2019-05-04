<?php

namespace app\modules\knowledge_base\models;

use Yii;
use yii\helpers\ArrayHelper;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%data_type}}".
 *
 * @property integer $id
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $name
 * @property string $description
 * @property integer $knowledge_base
 *
 * @property KnowledgeBase $knowledgeBase
 * @property PropertyValue[] $propertyValues
 */
class DataType extends \yii\db\ActiveRecord
{
    /**
     * @return string table name
     */
    public static function tableName()
    {
        return '{{%data_type}}';
    }

    /**
     * @return array the validation rules
     */
    public function rules()
    {
        return [
            [['name', 'knowledge_base'], 'required'],
            [['knowledge_base'], 'integer'],
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
            'id' => Yii::t('app', 'DATA_TYPE_MODEL_ID'),
            'created_at' => Yii::t('app', 'DATA_TYPE_MODEL_CREATED_AT'),
            'updated_at' => Yii::t('app', 'DATA_TYPE_MODEL_UPDATED_AT'),
            'name' => Yii::t('app', 'DATA_TYPE_MODEL_NAME'),
            'description' => Yii::t('app', 'DATA_TYPE_MODEL_DESCRIPTION'),
            'knowledge_base' => Yii::t('app', 'DATA_TYPE_MODEL_KNOWLEDGE_BASE'),
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
    public function getKbKnowledgeBase()
    {
        return $this->hasOne(KnowledgeBase::className(), ['id' => 'knowledge_base']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPropertyValues()
    {
        return $this->hasMany(PropertyValue::className(), ['data_type' => 'id']);
    }

    /**
     * Получение списка типов данных для конкретной базы знаний.
     * @param $id - id базы знаний
     * @return array - массив записей из таблицы data_type
     */
    public static function getDataTypesArray($id)
    {
        return ArrayHelper::map(self::find()->where(array('knowledge_base' => $id))->all(), 'id', 'name');
    }
}