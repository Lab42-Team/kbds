<?php

namespace app\modules\software_component\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use app\modules\user\models\User;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%software_component}}".
 *
 * @property integer $id
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $name
 * @property string $description
 * @property integer $type
 * @property integer $status
 * @property integer $author
 * @property string $file_name
 *
 * @property User $fkAuthor
 * @property array $metamodels
 */
class SoftwareComponent extends \yii\db\ActiveRecord
{
    // Типы
    const TYPE_INTEGRATED_ONT_ANALYSIS_COMPONENT = 0;       // CM-ONT
    const TYPE_INTEGRATED_RULE_ANALYSIS_COMPONENT = 1;      // CM-RULE
    const TYPE_INTEGRATED_OWL_GENERATION_COMPONENT = 2;     // ONT-OWL
    const TYPE_INTEGRATED_CLIPS_GENERATION_COMPONENT = 3;   // RULE-CLIPS
    const TYPE_AUTONOMOUS_OWL_GENERATION_COMPONENT = 4;     // CM-OWL
    const TYPE_AUTONOMOUS_CLIPS_GENERATION_COMPONENT = 5;   // CM-CLIPS
    // Статусы
    const STATUS_DESIGN = 0;    // Черновой
    const STATUS_GENERATED = 1; // Сгенерированный
    const STATUS_OUTDATED = 2;  // Устаревший

    /**
     * @return string table name
     */
    public static function tableName()
    {
        return '{{%software_component}}';
    }

    /**
     * @return array the validation rules
     */
    public function rules()
    {
        return [
            [['name', 'author'], 'required'],
            [['type', 'status', 'author'], 'integer'],
            [['name'], 'string', 'max' => 200],
            [['description', 'file_name'], 'string', 'max' => 600]
        ];
    }

    /**
     * @return array customized attribute labels
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'SOFTWARE_COMPONENT_MODEL_ID'),
            'created_at' => Yii::t('app', 'SOFTWARE_COMPONENT_MODEL_CREATED_AT'),
            'updated_at' => Yii::t('app', 'SOFTWARE_COMPONENT_MODEL_UPDATED_AT'),
            'name' => Yii::t('app', 'SOFTWARE_COMPONENT_MODEL_NAME'),
            'description' => Yii::t('app', 'SOFTWARE_COMPONENT_MODEL_DESCRIPTION'),
            'type' => Yii::t('app', 'SOFTWARE_COMPONENT_MODEL_TYPE'),
            'status' => Yii::t('app', 'SOFTWARE_COMPONENT_MODEL_STATUS'),
            'author' => Yii::t('app', 'SOFTWARE_COMPONENT_MODEL_AUTHOR'),
            'file_name' => Yii::t('app', 'SOFTWARE_COMPONENT_MODEL_FILE_NAME'),
        ];
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    /**
     * Получение ограниченного списка типов проектов программных компонентов.
     * @return array - массив ограниченного списка типов проекта программных компонентов
     */
    public static function getTypesArray()
    {
        return [
            self::TYPE_INTEGRATED_RULE_ANALYSIS_COMPONENT => Yii::t('app',
                'SOFTWARE_COMPONENT_MODEL_TYPE_INTEGRATED_RULE_ANALYSIS_COMPONENT'),
            self::TYPE_INTEGRATED_ONT_ANALYSIS_COMPONENT => Yii::t('app',
                'SOFTWARE_COMPONENT_MODEL_TYPE_INTEGRATED_ONT_ANALYSIS_COMPONENT'),
            self::TYPE_AUTONOMOUS_CLIPS_GENERATION_COMPONENT => Yii::t('app',
                'SOFTWARE_COMPONENT_MODEL_TYPE_AUTONOMOUS_CLIPS_GENERATION_COMPONENT'),
            self::TYPE_AUTONOMOUS_OWL_GENERATION_COMPONENT => Yii::t('app',
                'SOFTWARE_COMPONENT_MODEL_TYPE_AUTONOMOUS_OWL_GENERATION_COMPONENT'),
        ];
    }

    /**
     * Получение списка всех типов проектов программных компонентов.
     * @return array - массив всех возможных типов проекта программных компонентов
     */
    public static function getAllTypesArray()
    {
        return [
            self::TYPE_INTEGRATED_ONT_ANALYSIS_COMPONENT => Yii::t('app',
                'SOFTWARE_COMPONENT_MODEL_TYPE_INTEGRATED_ONT_ANALYSIS_COMPONENT'),
            self::TYPE_INTEGRATED_RULE_ANALYSIS_COMPONENT => Yii::t('app',
                'SOFTWARE_COMPONENT_MODEL_TYPE_INTEGRATED_RULE_ANALYSIS_COMPONENT'),
            self::TYPE_INTEGRATED_OWL_GENERATION_COMPONENT => Yii::t('app',
                'SOFTWARE_COMPONENT_MODEL_TYPE_INTEGRATED_OWL_GENERATION_COMPONENT'),
            self::TYPE_INTEGRATED_CLIPS_GENERATION_COMPONENT => Yii::t('app',
                'SOFTWARE_COMPONENT_MODEL_TYPE_INTEGRATED_CLIPS_GENERATION_COMPONENT'),
            self::TYPE_AUTONOMOUS_OWL_GENERATION_COMPONENT => Yii::t('app',
                'SOFTWARE_COMPONENT_MODEL_TYPE_AUTONOMOUS_OWL_GENERATION_COMPONENT'),
            self::TYPE_AUTONOMOUS_CLIPS_GENERATION_COMPONENT => Yii::t('app',
                'SOFTWARE_COMPONENT_MODEL_TYPE_AUTONOMOUS_CLIPS_GENERATION_COMPONENT'),
        ];
    }

    /**
     * Получение списка всех статусов проектов программных компонентов.
     * @return array - массив всех возможных статусов проекта программных компонентов
     */
    public static function getStatusesArray()
    {
        return [
            self::STATUS_DESIGN => Yii::t('app', 'SOFTWARE_COMPONENT_MODEL_STATUS_DESIGN'),
            self::STATUS_GENERATED => Yii::t('app', 'SOFTWARE_COMPONENT_MODEL_STATUS_GENERATED'),
            self::STATUS_OUTDATED => Yii::t('app', 'SOFTWARE_COMPONENT_MODEL_STATUS_OUTDATED'),
        ];
    }

    /**
     * Получение неполного списка статусов проектов программных компонентов.
     * @return array - массив статусов проекта программных компонентов
     */
    public static function getSomeStatusesArray()
    {
        return [
            self::STATUS_GENERATED => Yii::t('app', 'SOFTWARE_COMPONENT_MODEL_STATUS_GENERATED'),
            self::STATUS_OUTDATED => Yii::t('app', 'SOFTWARE_COMPONENT_MODEL_STATUS_OUTDATED'),
        ];
    }

    /**
     * Получение списка раскрашенных статусов проектов программных компонентов.
     * @return array - массив всех раскрашенных статусов проекта программных компонентов
     */
    public static function getColoredStatusesArray()
    {
        return [
            self::STATUS_DESIGN => '<span class="status-design">' .
                Yii::t('app', 'SOFTWARE_COMPONENT_MODEL_STATUS_DESIGN') . '</span>',
            self::STATUS_GENERATED => '<span class="status-generated">' .
                Yii::t('app', 'SOFTWARE_COMPONENT_MODEL_STATUS_GENERATED') . '</span>',
            self::STATUS_OUTDATED => '<span class="status-outdated">' .
                Yii::t('app', 'SOFTWARE_COMPONENT_MODEL_STATUS_OUTDATED') . '</span>',
        ];
    }

    /**
     * Получение названия типа проекта программного компонента.
     * @return mixed
     */
    public function getTypeName()
    {
        return ArrayHelper::getValue(self::getAllTypesArray(), $this->type);
    }

    /**
     * Получение названия статуса проекта программного компонента.
     * @return mixed
     */
    public function getStatusName()
    {
        return ArrayHelper::getValue(self::getColoredStatusesArray(), $this->status);
    }

    /**
     * Получение списка всех черновых программных компонентов.
     * @return array - массив всех черновых программных компонентов
     */
    public static function getAllDesignSoftwareComponentsArray()
    {
        return ArrayHelper::map(self::find()->where(array('status' => self::STATUS_DESIGN))->all(), 'id', 'name');
    }

    /**
     * Получение списка всех программных компонентов.
     * @return array - массив всех программных компонентов
     */
    public static function getAllSoftwareComponentsArray()
    {
        return ArrayHelper::map(self::find()->all(), 'id', 'name');
    }

    /**
     * Получение списка всех типов программных компонентов.
     * @return array - массив всех типов программных компонентов
     */
    public static function getAllSoftwareComponentTypesArray()
    {
        return ArrayHelper::map(self::find()->all(), 'id', 'type');
    }

    /**
     * Получение списка всех статусов программных компонентов.
     * @return array - массив всех статусов программных компонентов
     */
    public static function getAllSoftwareComponentStatusesArray()
    {
        return ArrayHelper::map(self::find()->all(), 'id', 'status');
    }

    /**
     * Получение имени автора проекта программного компонента.
     * @return \yii\db\ActiveQuery
     */
    public function getFkAuthor()
    {
        return $this->hasOne(User::className(), ['id' => 'author']);
    }
}