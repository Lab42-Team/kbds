<?php

namespace app\modules\software_component\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%metaclass}}".
 *
 * @property integer $id
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $name
 * @property string $description
 * @property integer $metamodel
 *
 * @property Metamodel $fkMetamodel
 * @property Metaattribute[] $metaattributes
 * @property Metarelation[] $leftMetarelations
 * @property Metarelation[] $rightMetarelations
 */
class Metaclass extends \yii\db\ActiveRecord
{
    /**
     * @return string table name
     */
    public static function tableName()
    {
        return '{{%metaclass}}';
    }

    /**
     * @return array the validation rules
     */
    public function rules()
    {
        return [
            [['name', 'metamodel'], 'required'],
            [['metamodel'], 'integer'],
            [['name'], 'string', 'max' => 200],
            [['description'], 'string', 'max' => 500],
        ];
    }

    /**
     * @return array customized attribute labels
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'METACLASS_MODEL_ID'),
            'created_at' => Yii::t('app', 'METACLASS_MODEL_CREATED_AT'),
            'updated_at' => Yii::t('app', 'METACLASS_MODEL_UPDATED_AT'),
            'name' => Yii::t('app', 'METACLASS_MODEL_NAME'),
            'description' => Yii::t('app', 'METACLASS_MODEL_DESCRIPTION'),
            'metamodel' => Yii::t('app', 'METACLASS_MODEL_METAMODEL'),
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
    public function getMetaattributes()
    {
        return $this->hasMany(Metaattribute::className(), ['metaclass' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLeftMetarelations()
    {
        return $this->hasMany(Metarelation::className(), ['left_metaclass' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRightMetarelations()
    {
        return $this->hasMany(Metarelation::className(), ['right_metaclass' => 'id']);
    }
}