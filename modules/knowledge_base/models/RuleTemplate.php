<?php

namespace app\modules\knowledge_base\models;

use Yii;
use yii\helpers\ArrayHelper;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%rule_template}}".
 *
 * @property integer $id
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $name
 * @property string $salience
 * @property string $description
 * @property integer $production_model
 *
 * @property RuleTemplateCondition[] $ruleTemplateConditions
 * @property RuleTemplateAction[] $ruleTemplateActions
 * @property Rule[] $rules
 * @property KnowledgeBase $productionModel
 */
class RuleTemplate extends \yii\db\ActiveRecord
{
    use \mootensai\relation\RelationTrait;

    /**
     * @return string table name
     */
    public static function tableName()
    {
        return '{{%rule_template}}';
    }

    /**
     * @return array the validation rules
     */
    public function rules()
    {
        return [
            [['name', 'production_model'], 'required'],
            [['salience', 'production_model'], 'integer'],
            [['name'], 'string', 'max' => 250],
            ['name', 'unique', 'targetAttribute' => ['name', 'production_model'],
                'message' => Yii::t('app', 'RULE_TEMPLATE_MODEL_MESSAGE_NAME')],
            [['description'], 'string', 'max' => 500]
        ];
    }

    /**
     * @return array customized attribute labels
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'RULE_TEMPLATE_MODEL_ID'),
            'created_at' => Yii::t('app', 'RULE_TEMPLATE_MODEL_CREATED_AT'),
            'updated_at' => Yii::t('app', 'RULE_TEMPLATE_MODEL_UPDATED_AT'),
            'name' => Yii::t('app', 'RULE_TEMPLATE_MODEL_NAME'),
            'salience' => Yii::t('app', 'RULE_TEMPLATE_MODEL_SALIENCE'),
            'description' => Yii::t('app', 'RULE_TEMPLATE_MODEL_DESCRIPTION'),
            'production_model' => Yii::t('app', 'RULE_TEMPLATE_MODEL_PRODUCTION_MODEL'),
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
    public function getRuleTemplateConditions()
    {
        return $this->hasMany(RuleTemplateCondition::className(), ['rule_template' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRuleTemplateActions()
    {
        return $this->hasMany(RuleTemplateAction::className(), ['rule_template' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRules()
    {
        return $this->hasMany(Rule::className(), ['rule_template' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductionModel()
    {
        return $this->hasOne(KnowledgeBase::className(), ['id' => 'production_model']);
    }

    /**
     * Получение списка шаблонов правил для конкретной базы знаний.
     * @param $id - id базы знаний
     * @return array - массив записей из таблицы rule_template
     */
    public static function getRuleTemplatesArray($id)
    {
        return ArrayHelper::map(self::find()->where(array('production_model' => $id))->all(), 'id', 'name');
    }
}