<?php

namespace app\modules\software_component\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\helpers\ArrayHelper;
use app\modules\user\models\User;

/**
 * This is the model class for table "{{%metamodel}}".
 *
 * @property integer $id
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $name
 * @property string $description
 * @property integer $type
 * @property integer $author
 *
 *  @property User $fkAuthor
 */
class Metamodel extends \yii\db\ActiveRecord
{
    const DEFAULT_TYPE = 0; // Метамодель по умолчанию (системная)
    const USER_TYPE = 1;    // Пользовательская метамодель

    /**
     * @return string table name
     */
    public static function tableName()
    {
        return '{{%metamodel}}';
    }

    /**
     * @return array the validation rules
     */
    public function rules()
    {
        return [
            [['name', 'author'], 'required'],
            [['type', 'author'], 'integer'],
            [['name'], 'string', 'max' => 200],
            [['description'], 'string', 'max' => 600],
        ];
    }

    /**
     * @return array customized attribute labels
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'METAMODEL_MODEL_ID'),
            'created_at' => Yii::t('app', 'METAMODEL_MODEL_CREATED_AT'),
            'updated_at' => Yii::t('app', 'METAMODEL_MODEL_UPDATED_AT'),
            'name' => Yii::t('app', 'METAMODEL_MODEL_NAME'),
            'description' => Yii::t('app', 'METAMODEL_MODEL_DESCRIPTION'),
            'type' => Yii::t('app', 'METAMODEL_MODEL_TYPE'),
            'author' => Yii::t('app', 'METAMODEL_MODEL_AUTHOR'),
        ];
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    /**
     * Получение списка типов метамоделей.
     * @return array - массив всех возможных типов метамоделей
     */
    public static function getTypesArray()
    {
        return [
            self::DEFAULT_TYPE => Yii::t('app', 'METAMODEL_MODEL_DEFAULT_TYPE'),
            self::USER_TYPE => Yii::t('app', 'METAMODEL_MODEL_USER_TYPE'),
        ];
    }

    /**
     * Получение названия типа метамодели.
     * @return mixed
     */
    public function getTypeName()
    {
        return ArrayHelper::getValue(self::getTypesArray(), $this->type);
    }

    /**
     * Получение списка всех метамоделей.
     * @return array - массив всех записей из таблицы metamodel
     */
    public static function getAllMetamodelsArray()
    {
        return ArrayHelper::map(self::find()->all(), 'id', 'name');
    }

    /**
     * Получение списка всех типов метамоделей.
     * @return array - массив всех типов метамоделей
     */
    public static function getAllMetamodelTypesArray()
    {
        return ArrayHelper::map(self::find()->all(), 'id', 'type');
    }

    /**
     * Получение имени автора метамодели.
     * @return \yii\db\ActiveQuery
     */
    public function getFkAuthor()
    {
        return $this->hasOne(User::className(), ['id' => 'author']);
    }
}