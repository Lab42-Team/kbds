<?php

namespace app\modules\knowledge_base\models;

use Yii;
use yii\helpers\ArrayHelper;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%rule_action}}".
 *
 * @property integer $id
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $function
 * @property integer $rule
 * @property integer $fact
 *
 * @property Rule $fkRule
 * @property Fact $fkFact
 */
class RuleAction extends \yii\db\ActiveRecord
{
    // Функции действия правила
    const FUNCTION_NONE = 'none';           // Отсутствие функции
    const FUNCTION_ASSERT = 'assert';       // Функция добавления
    const FUNCTION_RETRACT = 'retract';     // Функция удаления
    const FUNCTION_MODIFY = 'modify';       // Функция модификации
    const FUNCTION_DUPLICATE = 'duplicate'; // Функция копирования

    public $fact_name; // Дополнительное поле для отображения наименования факта действия

    /**
     * @return string table name
     */
    public static function tableName()
    {
        return '{{%rule_action}}';
    }

    /**
     * @return array the validation rules
     */
    public function rules()
    {
        return [
            [['rule', 'fact'], 'required'],
            [['rule', 'fact'], 'integer'],
            [['function'], 'string', 'max' => 9]
        ];
    }

    /**
     * @return array customized attribute labels
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'RULE_ACTION_MODEL_ID'),
            'created_at' => Yii::t('app', 'RULE_ACTION_MODEL_CREATED_AT'),
            'updated_at' => Yii::t('app', 'RULE_ACTION_MODEL_UPDATED_AT'),
            'function' => Yii::t('app', 'RULE_ACTION_MODEL_FUNCTION'),
            'rule' => Yii::t('app', 'RULE_ACTION_MODEL_RULE'),
            'fact' => Yii::t('app', 'RULE_ACTION_MODEL_FACT'),
            'fact_name' => Yii::t('app', 'RULE_ACTION_MODEL_FACT'),
        ];
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    /**
     * Получение списка функций действия правила.
     * @return array - массив всех возможных функций действия правила
     */
    public static function getFunctionsArray()
    {
        return [
            self::FUNCTION_NONE => Yii::t('app', 'RULE_ACTION_MODEL_FUNCTION_NONE'),
            self::FUNCTION_ASSERT => Yii::t('app', 'RULE_ACTION_MODEL_FUNCTION_ASSERT'),
            self::FUNCTION_RETRACT => Yii::t('app', 'RULE_ACTION_MODEL_FUNCTION_RETRACT'),
            self::FUNCTION_MODIFY => Yii::t('app', 'RULE_ACTION_MODEL_FUNCTION_MODIFY'),
            self::FUNCTION_DUPLICATE => Yii::t('app', 'RULE_ACTION_MODEL_FUNCTION_DUPLICATE'),
        ];
    }

    /**
     * Получение названия атрибута действия правила.
     * @return mixed
     */
    public function getFunctionName()
    {
        return ArrayHelper::getValue(self::getFunctionsArray(), $this->function);
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