<?php

namespace app\modules\software_component\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%metaclass_visibility}}".
 *
 * @property integer $id
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $transformation_model
 * @property integer $metaclass
 * @property boolean $visibility
 *
 * @property TransformationModel $transformationModel
 * @property Metaclass $fkMetaclass
 */
class MetaclassVisibility extends \yii\db\ActiveRecord
{
    const VISIBLE = true; // Видимый метакласс
    const HIDDEN = false; // Скрытый метакласс

    /**
     * @return string table name
     */
    public static function tableName()
    {
        return '{{%metaclass_visibility}}';
    }

    /**
     * @return array the validation rules
     */
    public function rules()
    {
        return [
            [['transformation_model', 'metaclass'], 'required'],
            [['transformation_model', 'metaclass'], 'integer'],
            [['visibility'], 'boolean']
        ];
    }

    /**
     * @return array customized attribute labels
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'METACLASS_VISIBILITY_MODEL_ID'),
            'created_at' => Yii::t('app', 'METACLASS_VISIBILITY_MODEL_CREATED_AT'),
            'updated_at' => Yii::t('app', 'METACLASS_VISIBILITY_MODEL_UPDATED_AT'),
            'transformation_model' => Yii::t('app', 'METACLASS_VISIBILITY_MODEL_TRANSFORMATION_MODEL'),
            'metaclass' => Yii::t('app', 'METACLASS_VISIBILITY_MODEL_METACLASS'),
            'visibility' => Yii::t('app', 'METACLASS_VISIBILITY_MODEL_VISIBILITY'),
        ];
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    /**
     * Получение списка значений видимости метаклассов в модели трансформации.
     * @return array - массив всех возможных значений видимости метаклассов в модели трансформации
     */
    public static function getVisibilityArray()
    {
        return [
            self::VISIBLE => Yii::t('app', 'METACLASS_VISIBILITY_MODEL_VISIBLE'),
            self::HIDDEN => Yii::t('app', 'METACLASS_VISIBILITY_MODEL_HIDDEN'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTransformationModel()
    {
        return $this->hasOne(TransformationModel::className(), ['id' => 'transformation_model']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFkMetaclass()
    {
        return $this->hasOne(Metaclass::className(), ['id' => 'metaclass']);
    }
}