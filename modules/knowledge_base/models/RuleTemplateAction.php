<?php

namespace app\modules\knowledge_base\models;

use Yii;
use yii\helpers\ArrayHelper;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%rule_template_action}}".
 *
 * @property integer $id
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $function
 * @property integer $rule_template
 * @property integer $fact_template
 *
 * @property RuleTemplate $ruleTemplate
 * @property FactTemplate $factTemplate
 */
class RuleTemplateAction extends \yii\db\ActiveRecord
{
    // Функции действия шаблона правила
    const FUNCTION_NONE = 'none';           // Отсутствие функции
    const FUNCTION_ASSERT = 'assert';       // Функция добавления
    const FUNCTION_RETRACT = 'retract';     // Функция удаления
    const FUNCTION_MODIFY = 'modify';       // Функция модификации
    const FUNCTION_DUPLICATE = 'duplicate'; // Функция копирования

    /**
     * @return string table name
     */
    public static function tableName()
    {
        return '{{%rule_template_action}}';
    }

    /**
     * @return array the validation rules
     */
    public function rules()
    {
        return [
            [['rule_template', 'fact_template'], 'required'],
            [['rule_template', 'fact_template'], 'integer'],
            [['function'], 'string', 'max' => 9]
        ];
    }

    /**
     * @return array customized attribute labels
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'RULE_TEMPLATE_ACTION_MODEL_ID'),
            'created_at' => Yii::t('app', 'RULE_TEMPLATE_ACTION_MODEL_CREATED_AT'),
            'updated_at' => Yii::t('app', 'RULE_TEMPLATE_ACTION_MODEL_UPDATED_AT'),
            'function' => Yii::t('app', 'RULE_TEMPLATE_ACTION_MODEL_FUNCTION'),
            'rule_template' => Yii::t('app', 'RULE_TEMPLATE_ACTION_MODEL_RULE_TEMPLATE'),
            'fact_template' => Yii::t('app', 'RULE_TEMPLATE_ACTION_MODEL_FACT_TEMPLATE'),
        ];
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    /**
     * Получение списка функций действия шаблона правила.
     * @return array - массив всех возможных функций действия шаблона правила
     */
    public static function getFunctionsArray()
    {
        return [
            self::FUNCTION_NONE => Yii::t('app', 'RULE_TEMPLATE_ACTION_MODEL_FUNCTION_NONE'),
            self::FUNCTION_ASSERT => Yii::t('app', 'RULE_TEMPLATE_ACTION_MODEL_FUNCTION_ASSERT'),
            self::FUNCTION_RETRACT => Yii::t('app', 'RULE_TEMPLATE_ACTION_MODEL_FUNCTION_RETRACT'),
            self::FUNCTION_MODIFY => Yii::t('app', 'RULE_TEMPLATE_ACTION_MODEL_FUNCTION_MODIFY'),
            self::FUNCTION_DUPLICATE => Yii::t('app', 'RULE_TEMPLATE_ACTION_MODEL_FUNCTION_DUPLICATE'),
        ];
    }

    /**
     * Получение названия функции действия шаблона правила.
     * @return mixed
     */
    public function getFunctionName()
    {
        return ArrayHelper::getValue(self::getFunctionsArray(), $this->function);
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