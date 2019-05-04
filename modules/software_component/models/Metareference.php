<?php

namespace app\modules\software_component\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%metareference}}".
 *
 * @property integer $id
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $metarelation
 * @property integer $left_metaattribute
 * @property integer $right_metaattribute
 *
 * @property Metaattribute $leftMetaattribute
 * @property Metaattribute $rightMetaattribute
 * @property Metarelation $fkMetarelation
 */
class Metareference extends \yii\db\ActiveRecord
{
    /**
     * @return string table name
     */
    public static function tableName()
    {
        return '{{%metareference}}';
    }

    /**
     * @return array the validation rules
     */
    public function rules()
    {
        return [
            [['left_metaattribute', 'right_metaattribute'], 'required'],
            [['metarelation', 'left_metaattribute', 'right_metaattribute'], 'integer'],
            ['left_metaattribute', 'unique', 'targetAttribute' => ['left_metaattribute', 'right_metaattribute'],
                'message' => Yii::t('app', 'METAMODEL_MODEL_MESSAGE_EXIST_RELATION_BETWEEN_ATTRIBUTES')]
        ];
    }

    /**
     * @return array customized attribute labels
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'METAREFERENCE_MODEL_ID'),
            'created_at' => Yii::t('app', 'METAREFERENCE_MODEL_CREATED_AT'),
            'updated_at' => Yii::t('app', 'METAREFERENCE_MODEL_UPDATED_AT'),
            'metarelation' => Yii::t('app', 'METAREFERENCE_MODEL_METAMODEL'),
            'left_metaattribute' => Yii::t('app', 'METAREFERENCE_MODEL_LEFT_METAATTRIBUTE'),
            'right_metaattribute' => Yii::t('app', 'METAREFERENCE_MODEL_RIGHT_METAATTRIBUTE'),
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
    public function getLeftMetaattribute()
    {
        return $this->hasOne(Metaattribute::className(), ['id' => 'left_metaattribute']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRightMetaattribute()
    {
        return $this->hasOne(Metaattribute::className(), ['id' => 'right_metaattribute']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFkMetarelation()
    {
        return $this->hasOne(Metarelation::className(), ['id' => 'metarelation']);
    }
}