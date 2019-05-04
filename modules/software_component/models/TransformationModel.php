<?php

namespace app\modules\software_component\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%transformation_model}}".
 *
 * @property integer $id
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $name
 * @property string $description
 * @property integer $software_component
 * @property integer $source_metamodel
 * @property integer $target_metamodel
 *
 * @property Metamodel $sourceMetamodel
 * @property Metamodel $targetMetamodel
 * @property SoftwareComponent $softwareComponent
 * @property TransformationRule[] $transformationRules
 */
class TransformationModel extends \yii\db\ActiveRecord
{
    public $additional_field;      // Вычисляемое поле (список всех типов программных компонентов)
    public $source_metamodel_name; // Дополнительное поле для отображения исходной метамодели
    public $target_metamodel_name; // Дополнительное поле для отображения целевой метамодели

    /**
     * @return string table name
     */
    public static function tableName()
    {
        return '{{%transformation_model}}';
    }

    /**
     * @return array the validation rules
     */
    public function rules()
    {
        return [
            [['name', 'software_component', 'source_metamodel', 'target_metamodel'], 'required'],
            [['software_component', 'source_metamodel', 'target_metamodel'], 'integer'],
            [['name'], 'string', 'max' => 200],
            [['description'], 'string', 'max' => 600],
            [['software_component'], 'unique', 'targetClass' => self::className(),
                'message' => Yii::t('app', 'TRANSFORMATION_MODEL_MODEL_MESSAGE_SOFTWARE_COMPONENT')],
            [['additional_field', 'source_metamodel_name', 'target_metamodel_name'], 'safe'],
        ];
    }

    /**
     * @return array customized attribute labels
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'TRANSFORMATION_MODEL_MODEL_ID'),
            'created_at' => Yii::t('app', 'TRANSFORMATION_MODEL_MODEL_CREATED_AT'),
            'updated_at' => Yii::t('app', 'TRANSFORMATION_MODEL_MODEL_UPDATED_AT'),
            'name' => Yii::t('app', 'TRANSFORMATION_MODEL_MODEL_NAME'),
            'description' => Yii::t('app', 'TRANSFORMATION_MODEL_MODEL_DESCRIPTION'),
            'software_component' => Yii::t('app', 'TRANSFORMATION_MODEL_MODEL_SOFTWARE_COMPONENT'),
            'source_metamodel' => Yii::t('app', 'TRANSFORMATION_MODEL_MODEL_SOURCE_METAMODEL'),
            'target_metamodel' => Yii::t('app', 'TRANSFORMATION_MODEL_MODEL_TARGET_METAMODEL'),
            'source_metamodel_name' => Yii::t('app', 'TRANSFORMATION_MODEL_MODEL_SOURCE_METAMODEL'),
            'target_metamodel_name' => Yii::t('app', 'TRANSFORMATION_MODEL_MODEL_TARGET_METAMODEL')
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
    public function getSourceMetamodel()
    {
        return $this->hasOne(Metamodel::className(), ['id' => 'source_metamodel']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTargetMetamodel()
    {
        return $this->hasOne(Metamodel::className(), ['id' => 'target_metamodel']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSoftwareComponent()
    {
        return $this->hasOne(SoftwareComponent::className(), ['id' => 'software_component']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTransformationRules()
    {
        return $this->hasMany(TransformationRule::className(), ['transformation_model' => 'id']);
    }
}