<?php

namespace app\modules\software_component\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%metarelation}}".
 *
 * @property integer $id
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $name
 * @property string $description
 * @property integer $type
 * @property integer $metamodel
 * @property integer $left_metaclass
 * @property integer $right_metaclass
 *
 * @property Metamodel $fkMetamodel
 * @property Metaclass $leftMetaclass
 * @property Metaclass $rightMetaclass
 */
class Metarelation extends \yii\db\ActiveRecord
{
    const ASSOCIATION = 0; // Ассоциация (отношение по вложенности тегов)
    const REFERENCE = 1;   // Ссылка (отношение по идентификаторам)

    /**
     * @return string table name
     */
    public static function tableName()
    {
        return '{{%metarelation}}';
    }

    /**
     * @return array the validation rules
     */
    public function rules()
    {
        return [
            [['name', 'type', 'metamodel', 'left_metaclass', 'right_metaclass'], 'required'],
            [['type', 'metamodel', 'left_metaclass', 'right_metaclass'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['description'], 'string', 'max' => 500],
        ];
    }

    /**
     * @return array customized attribute labels
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'METARELATION_MODEL_ID'),
            'created_at' => Yii::t('app', 'METARELATION_MODEL_CREATED_AT'),
            'updated_at' => Yii::t('app', 'METARELATION_MODEL_UPDATED_AT'),
            'name' => Yii::t('app', 'METARELATION_MODEL_NAME'),
            'description' => Yii::t('app', 'METARELATION_MODEL_DESCRIPTION'),
            'type' => Yii::t('app', 'METARELATION_MODEL_TYPE'),
            'metamodel' => Yii::t('app', 'METARELATION_MODEL_METAMODEL'),
            'left_metaclass' => Yii::t('app', 'METARELATION_MODEL_LEFT_METACLASS'),
            'right_metaclass' => Yii::t('app', 'METARELATION_MODEL_RIGHT_METACLASS'),
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
    public function getFkMetamodel()
    {
        return $this->hasOne(Metamodel::className(), ['id' => 'metamodel']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLeftMetaclass()
    {
        return $this->hasOne(Metaclass::className(), ['id' => 'left_metaclass']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRightMetaclass()
    {
        return $this->hasOne(Metaclass::className(), ['id' => 'right_metaclass']);
    }
}