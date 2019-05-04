<?php

namespace app\modules\knowledge_base\models;

use Yii;
use yii\helpers\ArrayHelper;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%fact}}".
 *
 * @property integer $id
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $name
 * @property boolean $initial
 * @property double $certainty_factor
 * @property string $description
 * @property integer $fact_template
 * @property integer $production_model
 *
 * @property RuleCondition[] $ruleConditions
 * @property RuleAction[] $ruleActions
 * @property FactSlot[] $factSlots
 * @property FactTemplate $factTemplate
 * @property KnowledgeBase $productionModel
 */
class Fact extends \yii\db\ActiveRecord
{
    const STATUS_INITIAL = true;      // Статус факта - начальный
    const STATUS_NOT_INITIAL = false; // Статус факта - не начальный (факт действия)

    public $fact_template_name; // Дополнительное поле для отображения наименования шаблона факта

    /**
     * @return string table name
     */
    public static function tableName()
    {
        return '{{%fact}}';
    }

    /**
     * @return array the validation rules
     */
    public function rules()
    {
        return [
            [['name', 'fact_template', 'production_model'], 'required'],
            [['fact_template', 'production_model'], 'integer'],
            [['certainty_factor'], 'double'],
            [['initial'], 'boolean'],
            [['name'], 'string', 'max' => 250],
            [['description'], 'string', 'max' => 500]
        ];
    }

    /**
     * @return array customized attribute labels
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'FACT_MODEL_ID'),
            'created_at' => Yii::t('app', 'FACT_MODEL_CREATED_AT'),
            'updated_at' => Yii::t('app', 'FACT_MODEL_UPDATED_AT'),
            'name' => Yii::t('app', 'FACT_MODEL_NAME'),
            'initial' => Yii::t('app', 'FACT_MODEL_INITIAL'),
            'certainty_factor' => Yii::t('app', 'FACT_MODEL_CERTAINTY_FACTOR'),
            'description' => Yii::t('app', 'FACT_MODEL_DESCRIPTION'),
            'fact_template' => Yii::t('app', 'FACT_MODEL_FACT_TEMPLATE'),
            'production_model' => Yii::t('app', 'FACT_MODEL_PRODUCTION_MODEL'),
            'fact_template_name' => Yii::t('app', 'FACT_MODEL_FACT_TEMPLATE')
        ];
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    /**
     * Получение списка возможных значений статусов фактов (начальный или не начальный).
     * @return array - массив всех возможных значений статусов фактов
     */
    public static function getInitialValueArray()
    {
        return [
            self::STATUS_INITIAL => Yii::t('app', 'FACT_MODEL_STATUS_INITIAL'),
            self::STATUS_NOT_INITIAL => Yii::t('app', 'FACT_MODEL_STATUS_NOT_INITIAL'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRuleConditions()
    {
        return $this->hasMany(RuleCondition::className(), ['fact' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRuleActions()
    {
        return $this->hasMany(RuleAction::className(), ['fact' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFactSlots()
    {
        return $this->hasMany(FactSlot::className(), ['fact' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFactTemplate()
    {
        return $this->hasOne(FactTemplate::className(), ['id' => 'fact_template']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductionModel()
    {
        return $this->hasOne(KnowledgeBase::className(), ['id' => 'production_model']);
    }

    /**
     * Получение списка фактов для конкретной базы знаний.
     * @param $id - id базы знаний
     * @return array - массив записей из таблицы fact
     */
    public static function getFactsArray($id)
    {
        return ArrayHelper::map(self::find()->where(array('production_model' => $id))->all(), 'id', 'name');
    }

    /**
     * Получение наименования факта по его id
     * @param $id - id факта
     * @return null|string - наименование факта
     */
    public static function getFactName($id)
    {
        if ($id == null)
            return null;

        return Fact::findOne($id)->name;
    }
}