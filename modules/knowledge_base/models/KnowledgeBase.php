<?php

namespace app\modules\knowledge_base\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use app\modules\user\models\User;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%knowledge_base}}".
 *
 * @property integer $id
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $name
 * @property string $description
 * @property integer $type
 * @property integer $status
 * @property integer $author
 * @property integer $subject_domain
 *
 * @property SubjectDomain $subjectDomain
 * @property User $kbAuthor
 */
class KnowledgeBase extends \yii\db\ActiveRecord
{
    const TYPE_ONTOLOGY = 0;  // Онтологическая БЗ
    const TYPE_RULES = 1;     // Продукционная БЗ
    const STATUS_PUBLIC = 0;  // Открыты проект БЗ
    const STATUS_PRIVATE = 1; // Закрытый проект БЗ

    /**
     * @return string table name
     */
    public static function tableName()
    {
        return '{{%knowledge_base}}';
    }

    /**
     * @return array the validation rules
     */
    public function rules()
    {
        return [
            [['name', 'author', 'subject_domain'], 'required'],
            [['type', 'status', 'author', 'subject_domain'], 'integer'],
            [['name'], 'string', 'max' => 200],
            [['description'], 'string', 'max' => 600]
        ];
    }

    /**
     * @return array customized attribute labels
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'KNOWLEDGE_BASE_MODEL_ID'),
            'created_at' => Yii::t('app', 'KNOWLEDGE_BASE_MODEL_CREATED_AT'),
            'updated_at' => Yii::t('app', 'KNOWLEDGE_BASE_MODEL_UPDATED_AT'),
            'name' => Yii::t('app', 'KNOWLEDGE_BASE_MODEL_NAME'),
            'description' => Yii::t('app', 'KNOWLEDGE_BASE_MODEL_DESCRIPTION'),
            'type' => Yii::t('app', 'KNOWLEDGE_BASE_MODEL_TYPE'),
            'status' => Yii::t('app', 'KNOWLEDGE_BASE_MODEL_STATUS'),
            'author' => Yii::t('app', 'KNOWLEDGE_BASE_MODEL_AUTHOR'),
            'subject_domain' => Yii::t('app', 'KNOWLEDGE_BASE_MODEL_SUBJECT_DOMAIN'),
        ];
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    /**
     * Получение списка типов БЗ.
     * @return array - массив всех возможных типов БЗ
     */
    public static function getTypesArray()
    {
        return [
            self::TYPE_ONTOLOGY => Yii::t('app', 'KNOWLEDGE_BASE_MODEL_TYPE_ONTOLOGY'),
            self::TYPE_RULES => Yii::t('app', 'KNOWLEDGE_BASE_MODEL_TYPE_RULES'),
        ];
    }

    /**
     * Получение названия типа БЗ.
     * @return mixed
     */
    public function getTypeName()
    {
        return ArrayHelper::getValue(self::getTypesArray(), $this->type);
    }

    /**
     * Получение списка статусов проектов БЗ.
     * @return array - массив всех возможных статусов проектов БЗ
     */
    public static function getStatusesArray()
    {
        return [
            self::STATUS_PUBLIC => Yii::t('app', 'KNOWLEDGE_BASE_MODEL_STATUS_PUBLIC'),
            self::STATUS_PRIVATE => Yii::t('app', 'KNOWLEDGE_BASE_MODEL_STATUS_PRIVATE'),
        ];
    }

    /**
     * Получение списка раскрашенных статусов проектов БЗ.
     * @return array - массив всех раскрашенных статусов проектов БЗ.
     */
    public static function getColoredStatusesArray()
    {
        return [
            self::STATUS_PUBLIC => Html::img('@web/images/unlock.png', ['class' => 'kb-status-image']) .
                '<span class="status-public">' . Yii::t('app', 'KNOWLEDGE_BASE_MODEL_STATUS_PUBLIC') . '</span>',
            self::STATUS_PRIVATE => Html::img('@web/images/lock.png', ['class' => 'kb-status-image']) .
                '<span class="status-private">' . Yii::t('app', 'KNOWLEDGE_BASE_MODEL_STATUS_PRIVATE') . '</span>',
        ];
    }

    /**
     * Получение названия статуса проекта БЗ.
     * @return mixed
     */
    public function getStatusName()
    {
        return ArrayHelper::getValue(self::getColoredStatusesArray(), $this->status);
    }

    /**
     * Получение названия предметной области.
     * @return \yii\db\ActiveQuery
     */
    public function getSubjectDomain()
    {
        return $this->hasOne(SubjectDomain::className(), ['id' => 'subject_domain']);
    }

    /**
     * Получение имени автора проекта БЗ.
     * @return \yii\db\ActiveQuery
     */
    public function getKbAuthor()
    {
        return $this->hasOne(User::className(), ['id' => 'author']);
    }
}