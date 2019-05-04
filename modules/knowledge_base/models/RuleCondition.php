<?php

namespace app\modules\knowledge_base\models;

use Yii;
use yii\helpers\ArrayHelper;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%rule_condition}}".
 *
 * @property integer $id
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $operator
 * @property integer $rule
 * @property integer $fact
 *
 * @property Rule $fkRule
 * @property Fact $fkFact
 */
class RuleCondition extends \yii\db\ActiveRecord
{
    // Операции условия правила
    const OPERATOR_NONE = 'NONE'; // Нет оператора
    const OPERATOR_NOT = 'NOT';   // Оператор "не"
    const OPERATOR_AND = 'AND';   // Оператор "и"
    const OPERATOR_OR = 'OR';     // Оператор "или"

    public $fact_name; // Дополнительное поле для отображения наименования факта условия

    /**
     * @return string table name
     */
    public static function tableName()
    {
        return '{{%rule_condition}}';
    }

    /**
     * @return array the validation rules
     */
    public function rules()
    {
        return [
            [['rule', 'fact'], 'required'],
            [['rule', 'fact'], 'integer'],
            [['operator'], 'string', 'max' => 4]
        ];
    }

    /**
     * @return array customized attribute labels
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'RULE_CONDITION_MODEL_ID'),
            'created_at' => Yii::t('app', 'RULE_CONDITION_MODEL_CREATED_AT'),
            'updated_at' => Yii::t('app', 'RULE_CONDITION_MODEL_UPDATED_AT'),
            'operator' => Yii::t('app', 'RULE_CONDITION_MODEL_OPERATOR'),
            'rule' => Yii::t('app', 'RULE_CONDITION_MODEL_RULE'),
            'fact' => Yii::t('app', 'RULE_CONDITION_MODEL_FACT'),
            'fact_name' => Yii::t('app', 'RULE_CONDITION_MODEL_FACT'),
        ];
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    /**
     * Получение списка операторов условия правила.
     * @return array - массив всех возможных операторов условия правила
     */
    public static function getOperatorsArray()
    {
        return [
            self::OPERATOR_NONE => Yii::t('app', 'RULE_CONDITION_MODEL_OPERATOR_NONE'),
            self::OPERATOR_NOT => Yii::t('app', 'RULE_CONDITION_MODEL_OPERATOR_NOT'),
            self::OPERATOR_AND => Yii::t('app', 'RULE_CONDITION_MODEL_OPERATOR_AND'),
            self::OPERATOR_OR => Yii::t('app', 'RULE_CONDITION_MODEL_OPERATOR_OR'),
        ];
    }

    /**
     * Получение названия оператора условия правила.
     * @return mixed
     */
    public function getOperatorName()
    {
        return ArrayHelper::getValue(self::getOperatorsArray(), $this->operator);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFkRule()
    {
        return $this->hasOne(Rule::className(), ['id' => 'rule']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFkFact()
    {
        return $this->hasOne(Fact::className(), ['id' => 'fact']);
    }
}