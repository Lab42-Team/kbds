<?php

namespace app\modules\software_component\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%transformation_body}}".
 *
 * @property integer $id
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $transformation_rule
 * @property integer $source_metaattribute
 * @property integer $target_metaattribute
 *
 * @property Metaattribute $sourceMetaattribute
 * @property Metaattribute $targetMetaattribute
 * @property TransformationRule $transformationRule
 */
class TransformationBody extends \yii\db\ActiveRecord
{
    /**
     * @return string table name
     */
    public static function tableName()
    {
        return '{{%transformation_body}}';
    }

    /**
     * @return array the validation rules
     */
    public function rules()
    {
        return [
            [['transformation_rule'], 'required'],
            [['transformation_rule', 'source_metaattribute', 'target_metaattribute'], 'integer']
        ];
    }

    /**
     * @return array customized attribute labels
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'TRANSFORMATION_BODY_MODEL_ID'),
            'created_at' => Yii::t('app', 'TRANSFORMATION_BODY_MODEL_CREATED_AT'),
            'updated_at' => Yii::t('app', 'TRANSFORMATION_BODY_MODEL_UPDATED_AT'),
            'transformation_rule' => Yii::t('app', 'TRANSFORMATION_BODY_MODEL_TRANSFORMATION_RULE'),
            'source_metaattribute' => Yii::t('app', 'TRANSFORMATION_BODY_MODEL_SOURCE_METAATTRIBUTE'),
            'target_metaattribute' => Yii::t('app', 'TRANSFORMATION_BODY_MODEL_TARGET_METAATTRIBUTE'),
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
    public function getSourceMetaattribute()
    {
        return $this->hasOne(Metaattribute::className(), ['id' => 'source_metaattribute']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTargetMetaattribute()
    {
        return $this->hasOne(Metaattribute::className(), ['id' => 'target_metaattribute']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTransformationRule()
    {
        return $this->hasOne(TransformationRule::className(), ['id' => 'transformation_rule']);
    }
}