<?php

namespace app\modules\software_component\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%metaattribute}}".
 *
 * @property integer $id
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $name
 * @property string $description
 * @property string $type
 * @property string $value
 * @property integer $metaclass
 *
 * @property Metaclass $fkMetaclass
 */
class Metaattribute extends \yii\db\ActiveRecord
{
    /**
     * @return string table name
     */
    public static function tableName()
    {
        return '{{%metaattribute}}';
    }

    /**
     * @return array the validation rules
     */
    public function rules()
    {
        return [
            [['name', 'metaclass'], 'required'],
            [['metaclass'], 'integer'],
            [['name', 'type', 'value'], 'string', 'max' => 255],
            [['description'], 'string', 'max' => 500],
        ];
    }

    /**
     * @return array customized attribute labels
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'METAATTRIBUTE_MODEL_ID'),
            'created_at' => Yii::t('app', 'METAATTRIBUTE_MODEL_CREATED_AT'),
            'updated_at' => Yii::t('app', 'METAATTRIBUTE_MODEL_UPDATED_AT'),
            'name' => Yii::t('app', 'METAATTRIBUTE_MODEL_NAME'),
            'description' => Yii::t('app', 'METAATTRIBUTE_MODEL_DESCRIPTION'),
            'type' => Yii::t('app', 'METAATTRIBUTE_MODEL_TYPE'),
            'value' => Yii::t('app', 'METAATTRIBUTE_MODEL_VALUE'),
            'metaclass' => Yii::t('app', 'METAATTRIBUTE_MODEL_METACLASS'),
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
    public function getFkMetaclass()
    {
        return $this->hasOne(Metaclass::className(), ['id' => 'metaclass']);
    }
}