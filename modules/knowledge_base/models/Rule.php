<?php

namespace app\modules\knowledge_base\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%rule}}".
 *
 * @property integer $id
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $name
 * @property double $certainty_factor
 * @property string $salience
 * @property string $description
 * @property integer $rule_template
 * @property integer $production_model
 *
 * @property RuleCondition[] $ruleConditions
 * @property RuleAction[] $ruleActions
 * @property RuleTemplate $ruleTemplate
 * @property KnowledgeBase $productionModel
 */
class Rule extends \yii\db\ActiveRecord
{
    /**
     * @return string table name
     */
    public static function tableName()
    {
        return '{{%rule}}';
    }

    /**
     * @return array the validation rules
     */
    public function rules()
    {
        return [
            [['name', 'rule_template', 'production_model'], 'required'],
            [['salience', 'rule_template', 'production_model'], 'integer'],
            [['certainty_factor'], 'double'],
            [['name'], 'string', 'max' => 250],
            ['name', 'unique', 'targetAttribute' => ['name', 'production_model'],
                'message' => Yii::t('app', 'RULE_MODEL_MESSAGE_NAME')],
            [['description'], 'string', 'max' => 500]
        ];
    }

    /**
     * @return array customized attribute labels
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'RULE_MODEL_ID'),
            'created_at' => Yii::t('app', 'RULE_MODEL_CREATED_AT'),
            'updated_at' => Yii::t('app', 'RULE_MODEL_UPDATED_AT'),
            'name' => Yii::t('app', 'RULE_MODEL_NAME'),
            'certainty_factor' => Yii::t('app', 'RULE_MODEL_CERTAINTY_FACTOR'),
            'salience' => Yii::t('app', 'RULE_MODEL_SALIENCE'),
            'description' => Yii::t('app', 'RULE_MODEL_DESCRIPTION'),
            'rule_template' => Yii::t('app', 'RULE_MODEL_RULE_TEMPLATE'),
            'production_model' => Yii::t('app', 'RULE_MODEL_PRODUCTION_MODEL'),
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
    public function getRuleConditions()
    {
        return $this->hasMany(RuleCondition::className(), ['rule' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRuleActions()
    {
        return $this->hasMany(RuleAction::className(), ['rule' => 'id']);
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
    public function getProductionModel()
    {
        return $this->hasOne(KnowledgeBase::className(), ['id' => 'production_model']);
    }
}