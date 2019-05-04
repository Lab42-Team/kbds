<?php

namespace app\modules\knowledge_base\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\helpers\ArrayHelper;
use app\modules\user\models\User;
use app\modules\knowledge_base\models\KnowledgeBase;

/**
 * This is the model class for table "{{%subject_domain}}".
 *
 * @property integer $id
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $name
 * @property string $description
 * @property integer $author
 *
 * @property KnowledgeBase[] $knowledgeBases
 * @property User $kbAuthor
 */
class SubjectDomain extends \yii\db\ActiveRecord
{
    /**
     * @return string table name
     */
    public static function tableName()
    {
        return '{{%subject_domain}}';
    }

    /**
     * @return array the validation rules
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 200],
            ['name', 'unique', 'targetClass' => self::className(),
                'message' => Yii::t('app', 'SUBJECT_DOMAIN_MODEL_MESSAGE_NAME')],
            [['description'], 'string', 'max' => 600],
            [['author'], 'integer'],
        ];
    }

    /**
     * @return array customized attribute labels
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'SUBJECT_DOMAIN_MODEL_ID'),
            'created_at' => Yii::t('app', 'SUBJECT_DOMAIN_MODEL_CREATED_AT'),
            'updated_at' => Yii::t('app', 'SUBJECT_DOMAIN_MODEL_UPDATED_AT'),
            'name' => Yii::t('app', 'SUBJECT_DOMAIN_MODEL_NAME'),
            'description' => Yii::t('app', 'SUBJECT_DOMAIN_MODEL_DESCRIPTION'),
            'author' => Yii::t('app', 'SUBJECT_DOMAIN_MODEL_AUTHOR'),
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
    public function getKnowledgeBases()
    {
        return $this->hasMany(KnowledgeBase::className(), ['subject_domain' => 'id']);
    }

    /**
     * Получение списка предметных областей.
     * @return array - массив всех записей из таблицы subject_domain
     */
    public static function getAllSubjectDomainsArray()
    {
        return ArrayHelper::map(self::find()->all(), 'id', 'name');
    }

    /**
     * Получение имени автора предметной области.
     * @return \yii\db\ActiveQuery
     */
    public function getKbAuthor()
    {
        return $this->hasOne(User::className(), ['id' => 'author']);
    }
}