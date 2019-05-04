<?php

namespace app\modules\knowledge_base\models;

use Yii;
use yii\helpers\ArrayHelper;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%rule_template_condition}}".
 *
 * @property integer $id
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $operator
 * @property integer $rule_template
 * @property integer $fact_template
 *
 * @property RuleTemplate $ruleTemplate
 * @property FactTemplate $factTemplate
 */
class RuleTemplateCondition extends \yii\db\ActiveRecord
{
    const OPERATOR_NONE = 'NONE'; // Нет оператора
    const OPERATOR_NOT = 'NOT';   // Оператор "не"
    const OPERATOR_AND = 'AND';   // Оператор "и"
    const OPERATOR_OR = 'OR';     // Оператор "или"

    /**
     * @return string table name
     */
    public static function tableName()
    {
        return '{{%rule_template_condition}}';
    }

    /**
     * @return array the validation rules
     */
    public function rules()
    {
        return [
            [['rule_template', 'fact_template'], 'required'],
            [['rule_template', 'fact_template'], 'integer'],
            [['operator'], 'string', 'max' => 4]
        ];
    }

    /**
     * @return array customized attribute labels
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'RULE_TEMPLATE_CONDITION_MODEL_ID'),
            'created_at' => Yii::t('app', 'RULE_TEMPLATE_CONDITION_MODEL_CREATED_AT'),
            'updated_at' => Yii::t('app', 'RULE_TEMPLATE_CONDITION_MODEL_UPDATED_AT'),
            'operator' => Yii::t('app', 'RULE_TEMPLATE_CONDITION_MODEL_OPERATOR'),
            'rule_template' => Yii::t('app', 'RULE_TEMPLATE_CONDITION_MODEL_RULE_TEMPLATE'),
            'fact_template' => Yii::t('app', 'RULE_TEMPLATE_CONDITION_MODEL_FACT_TEMPLATE'),
        ];
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    /**
     * Получение списка операторов условия шаблона правила.
     * @return array - массив всех возможных операторов условия шаблона правила
     */
    public static function getOperatorsArray()
    {
        return [
            self::OPERATOR_NONE => Yii::t('app', 'RULE_TEMPLATE_CONDITION_MODEL_OPERATOR_NONE'),
            self::OPERATOR_NOT => Yii::t('app', 'RULE_TEMPLATE_CONDITION_MODEL_OPERATOR_NOT'),
            self::OPERATOR_AND => Yii::t('app', 'RULE_TEMPLATE_CONDITION_MODEL_OPERATOR_AND'),
            self::OPERATOR_OR => Yii::t('app', 'RULE_TEMPLATE_CONDITION_MODEL_OPERATOR_OR'),
        ];
    }

    /**
     * Получение названия оператора условия.
     * @return mixed
     */
    public function getOperatorName()
    {
        return ArrayHelper::getValue(self::getOperatorsArray(), $this->operator);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRuleTemplate()
    {
        return $this->hasOne(RuleTemplate::className(), ['id' => 'rule_template']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFactTemplate()
    {
        return $this->hasOne(FactTemplate::className(), ['id' => 'fact_template']);
    }
}