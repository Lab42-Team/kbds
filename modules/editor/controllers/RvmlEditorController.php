<?php

namespace app\modules\editor\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\bootstrap\ActiveForm;
use app\modules\knowledge_base\models\KnowledgeBase;
use app\modules\knowledge_base\models\DataType;
use app\modules\knowledge_base\models\FactTemplate;
use app\modules\knowledge_base\models\FactTemplateSlot;
use app\modules\knowledge_base\models\RuleTemplate;
use app\modules\knowledge_base\models\RuleTemplateCondition;
use app\modules\knowledge_base\models\RuleTemplateAction;
use app\modules\knowledge_base\models\Fact;
use app\modules\knowledge_base\models\FactSlot;
use app\modules\knowledge_base\models\Rule;
use app\modules\knowledge_base\models\RuleCondition;
use app\modules\knowledge_base\models\RuleAction;
use app\components\CLIPSCodeGenerator;

/**
 * RvmlEditorController implements the actions for RVML editor.
 */
class RvmlEditorController extends Controller
{
    public $layout = "/column1";

    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['developer']
                    ],
                ],
            ],
        ];
    }

    /**
     * Displays a single knowledge base (rules model) in graphical form (RVML schema).
     * @param integer $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionIndex($id)
    {
        // Деактивация правого меню
        $this->layout = "/rvml-editor";
        // Получение базы знаний по ее id
        $model = $this->findModel($id);
        // Формирование модели шаблона факта
        $fact_template_model = new FactTemplate();
        // Формирование модели слота шаблона факта
        $fact_template_slot_model = new FactTemplateSlot();
        // Формирование модели факта
        $fact_model = new Fact();
        // Формирование модели слота факта
        $fact_slot_model = new FactSlot();
        // Формирование модели шаблона правила
        $rule_template_model = new RuleTemplate();
        // Формирование моделей условий шаблона правила
        $rule_template_condition_models = [new RuleTemplateCondition];
        // Формирование моделей действий шаблона правила
        $rule_template_action_models = [new RuleTemplateAction];
        // Формирование модели правила
        $rule_model = new Rule();
        // Формирование моделей условий правила
        $rule_condition_models = [new RuleCondition];
        // Формирование моделей действий правила
        $rule_action_models = [new RuleAction];

        // Поиск всех шаблонов фактов принадлежащие данной базе знаний
        $fact_templates = FactTemplate::find()->where(array('production_model' => $model->id))->asArray()->all();
        // Поиск всех шаблонов правил принадлежащие данной базе знаний
        $rule_templates = RuleTemplate::find()->where(array('production_model' => $model->id))->asArray()->all();
        // Поиск всех типов данных принадлежащие данной базе знаний
        $data_types = DataType::find()->where(array('knowledge_base' => $model->id))->asArray()->all();
        // Поиск всех фактов принадлежащие данной базе знаний
        $facts = Fact::find()->where(array('production_model' => $model->id))->asArray()->all();
        // Поиск всех правил принадлежащие данной базе знаний
        $rules = Rule::find()->where(array('production_model' => $model->id))->asArray()->all();
        // Поиск всех слотов шаблонов фактов
        $fact_template_slots = FactTemplateSlot::find()->asArray()->all();
        // Поиск всех слотов фактов
        $fact_slots = FactSlot::find()->asArray()->all();
        // Поиск всех условий шаблонов правил
        $rule_template_conditions = RuleTemplateCondition::find()->asArray()->all();
        // Поиск всех действий шаблонов правил
        $rule_template_actions = RuleTemplateAction::find()->asArray()->all();
        // Поиск всех условий правил
        $rule_conditions = RuleCondition::find()->asArray()->all();
        // Поиск всех действий правил
        $rule_actions = RuleAction::find()->asArray()->all();

        // Массивы хранения элементов продукционной модели для дерева элементов БЗ
        $fact_template_array = array();
        $fact_array = array();
        $rule_template_array = array();
        $rule_array = array();
        // Формирование массива шаблонов фактов для дерева элементов БЗ
        foreach ($fact_templates as $fact_template) {
            // Формирование массива слотов шаблонов фактов
            $fact_template_slot_array = array();
            foreach ($fact_template_slots as $fact_template_slot)
                if ($fact_template_slot['fact_template'] == $fact_template['id'])
                    array_push($fact_template_slot_array,
                        [
                            'id' => $fact_template_slot['id'],
                            'text' => $fact_template_slot['name'],
                            'dataType' => DataType::findOne($fact_template_slot['data_type'])->name,
                            'defaultValue' => $fact_template_slot['default_value'],
                            'description' => $fact_template_slot['description'],
                            'selectedIcon' => "glyphicon glyphicon-arrow-right",
                            'icon' => "glyphicon glyphicon-tag"
                        ]);
            array_push($fact_template_array,
                [
                    'id' => $fact_template['id'],
                    'text' => $fact_template['name'],
                    'description' => $fact_template['description'],
                    'icon' => "glyphicon glyphicon-unchecked",
                    'selectedIcon' => "glyphicon glyphicon-arrow-right",
                    'state' => [
                        'expanded' => empty($fact_template_slot_array) ? true : false
                    ],
                    'nodes' => $fact_template_slot_array
                ]);
        }
        // Формирование массива начальных фактов для дерева элементов БЗ
        foreach ($facts as $fact)
            if ($fact['initial'] == true) {
                // Формирование массива слотов фактов
                $fact_slot_array = array();
                foreach ($fact_slots as $fact_slot)
                    if ($fact_slot['fact'] == $fact['id'])
                        array_push($fact_slot_array,
                            [
                                'id' => $fact_slot['id'],
                                'text' => $fact_slot['name'],
                                'dataType' => DataType::findOne($fact_slot['data_type'])->name,
                                'value' => $fact_slot['value'],
                                'description' => $fact_slot['description'],
                                'selectedIcon' => "glyphicon glyphicon-arrow-right",
                                'icon' => "glyphicon glyphicon-tag"
                            ]);
                foreach ($fact_template_array as $fact_template)
                    if ($fact['fact_template'] == $fact_template['id'])
                        array_push($fact_array,
                            [
                                'id' => $fact['id'],
                                'factTemplateId' => $fact['fact_template'],
                                'text' => $fact['name'],
                                'certaintyFactor' => $fact['certainty_factor'],
                                'description' => $fact['description'],
                                'icon' => "glyphicon glyphicon-stop",
                                'selectedIcon' => "glyphicon glyphicon-arrow-right",
                                'state' => [
                                    'expanded' => empty($fact_slot_array) ? true : false
                                ],
                                'nodes' => $fact_slot_array
                            ]);
            }
        // Формирование массива шаблонов правил для дерева элементов БЗ
        foreach ($rule_templates as $rule_template) {
            // Формирование массива условий для данного шаблона правила
            $rule_template_condition_array = array();
            foreach ($rule_template_conditions as $rule_template_condition)
                if ($rule_template_condition['rule_template'] == $rule_template['id'])
                    foreach ($fact_templates as $fact_template)
                        if ($rule_template_condition['fact_template'] == $fact_template['id']) {
                            // Формирование массива слотов для шаблонов фактов
                            $fact_template_slot_array = array();
                            foreach ($fact_template_slots as $fact_template_slot)
                                if ($fact_template_slot['fact_template'] == $fact_template['id'])
                                    array_push($fact_template_slot_array,
                                        [
                                            'id' => $fact_template_slot['id'],
                                            'text' => $fact_template_slot['name'],
                                            'dataType' => DataType::findOne($fact_template_slot['data_type'])->name,
                                            'defaultValue' => $fact_template_slot['default_value'],
                                            'description' => $fact_template_slot['description'],
                                            'selectedIcon' => "glyphicon glyphicon-arrow-right",
                                            'icon' => "glyphicon glyphicon-tag"
                                        ]);
                            array_push($rule_template_condition_array,
                                [
                                    'id' => $fact_template['id'],
                                    'ruleTemplateConditionId' => $rule_template_condition['id'],
                                    'operator' => $rule_template_condition['operator'],
                                    'text' => $fact_template['name'],
                                    'description' => $fact_template['description'],
                                    'icon' => "glyphicon glyphicon-unchecked",
                                    'selectedIcon' => "glyphicon glyphicon-arrow-right",
                                    'state' => [
                                        'expanded' => empty($fact_template_slot_array) ? true : false
                                    ],
                                    'nodes' => $fact_template_slot_array
                                ]);
                        }
            // Формирование массива действий для данного шаблона правила
            $rule_template_action_array = array();
            foreach ($rule_template_actions as $rule_template_action)
                if ($rule_template_action['rule_template'] == $rule_template['id'])
                    foreach ($fact_templates as $fact_template)
                        if ($rule_template_action['fact_template'] == $fact_template['id']) {
                            // Формирование массива слотов для шаблонов фактов
                            $fact_template_slot_array = array();
                            foreach ($fact_template_slots as $fact_template_slot)
                                if ($fact_template_slot['fact_template'] == $fact_template['id'])
                                    array_push($fact_template_slot_array,
                                        [
                                            'id' => $fact_template_slot['id'],
                                            'text' => $fact_template_slot['name'],
                                            'dataType' => DataType::findOne($fact_template_slot['data_type'])->name,
                                            'defaultValue' => $fact_template_slot['default_value'],
                                            'description' => $fact_template_slot['description'],
                                            'selectedIcon' => "glyphicon glyphicon-arrow-right",
                                            'icon' => "glyphicon glyphicon-tag"
                                        ]);
                            array_push($rule_template_action_array,
                                [
                                    'id' => $fact_template['id'],
                                    'ruleTemplateActionId' => $rule_template_action['id'],
                                    'function' => $rule_template_action['function'],
                                    'text' => $fact_template['name'],
                                    'description' => $fact_template['description'],
                                    'icon' => "glyphicon glyphicon-unchecked",
                                    'selectedIcon' => "glyphicon glyphicon-arrow-right",
                                    'state' => [
                                        'expanded' => empty($fact_template_slot_array) ? true : false
                                    ],
                                    'nodes' => $fact_template_slot_array
                                ]);
                        }
            array_push($rule_template_array,
                [
                    'id' => $rule_template['id'],
                    'salience' => $rule_template['salience'],
                    'text' => $rule_template['name'],
                    'description' => $rule_template['description'],
                    'icon' => "glyphicon glyphicon-registration-mark",
                    'selectedIcon' => "glyphicon glyphicon-arrow-right",
                    'nodes' => [
                        [
                            'text' => Yii::t('app', 'RVML_EDITOR_PAGE_CONDITIONS'),
                            'color' => "#428bca",
                            'selectable' => false,
                            'icon' => "glyphicon glyphicon-registration-mark",
                            'tags' => [count($rule_template_condition_array)],
                            'state' => [
                                'expanded' => empty($rule_template_condition_array) ? true : false
                            ],
                            'nodes' => $rule_template_condition_array
                        ],
                        [
                            'text' => Yii::t('app', 'RVML_EDITOR_PAGE_ACTIONS'),
                            'color' => "#428bca",
                            'selectable' => false,
                            'icon' => "glyphicon glyphicon-registration-mark",
                            'tags' => [count($rule_template_action_array)],
                            'state' => [
                                'expanded' => empty($rule_template_action_array) ? true : false
                            ],
                            'nodes' => $rule_template_action_array
                        ],
                    ]
                ]);
        }
        // Формирование массива правил для дерева элементов БЗ
        foreach ($rules as $rule) {
            // Формирование массива условий для данного правила
            $rule_condition_array = array();
            foreach ($rule_conditions as $rule_condition)
                if ($rule_condition['rule'] == $rule['id'])
                    foreach ($facts as $fact)
                        if ($rule_condition['fact'] == $fact['id']) {
                            // Формирование массива слотов фактов
                            $fact_slot_array = array();
                            foreach ($fact_slots as $fact_slot)
                                if ($fact_slot['fact'] == $fact['id'])
                                    array_push($fact_slot_array,
                                        [
                                            'id' => $fact_slot['id'],
                                            'text' => $fact_slot['name'],
                                            'dataType' => DataType::findOne($fact_slot['data_type'])->name,
                                            'value' => $fact_slot['value'],
                                            'description' => $fact_slot['description'],
                                            'selectedIcon' => "glyphicon glyphicon-arrow-right",
                                            'icon' => "glyphicon glyphicon-tag"
                                        ]);
                            foreach ($fact_template_array as $fact_template)
                                if ($fact['fact_template'] == $fact_template['id'])
                                    array_push($rule_condition_array,
                                        [
                                            'id' => $fact['id'],
                                            'factTemplateId' => $fact['fact_template'],
                                            'ruleConditionId' => $rule_condition['id'],
                                            'operator' => $rule_condition['operator'],
                                            'text' => $fact['name'],
                                            'certaintyFactor' => $fact['certainty_factor'],
                                            'description' => $fact['description'],
                                            'icon' => "glyphicon glyphicon-stop",
                                            'selectedIcon' => "glyphicon glyphicon-arrow-right",
                                            'state' => [
                                                'expanded' => empty($fact_slot_array) ? true : false
                                            ],
                                            'nodes' => $fact_slot_array
                                        ]);
                        }
            // Формирование массива действий для данного правила
            $rule_action_array = array();
            foreach ($rule_actions as $rule_action)
                if ($rule_action['rule'] == $rule['id'])
                    foreach ($facts as $fact)
                        if ($rule_action['fact'] == $fact['id']) {
                            // Формирование массива слотов фактов
                            $fact_slot_array = array();
                            foreach ($fact_slots as $fact_slot)
                                if ($fact_slot['fact'] == $fact['id'])
                                    array_push($fact_slot_array,
                                        [
                                            'id' => $fact_slot['id'],
                                            'text' => $fact_slot['name'],
                                            'dataType' => DataType::findOne($fact_slot['data_type'])->name,
                                            'value' => $fact_slot['value'],
                                            'description' => $fact_slot['description'],
                                            'selectedIcon' => "glyphicon glyphicon-arrow-right",
                                            'icon' => "glyphicon glyphicon-tag"
                                        ]);
                            foreach ($fact_template_array as $fact_template)
                                if ($fact['fact_template'] == $fact_template['id'])
                                    array_push($rule_action_array,
                                        [
                                            'id' => $fact['id'],
                                            'factTemplateId' => $fact['fact_template'],
                                            'ruleActionId' => $rule_action['id'],
                                            'function' => $rule_action['function'],
                                            'text' => $fact['name'],
                                            'certaintyFactor' => $fact['certainty_factor'],
                                            'description' => $fact['description'],
                                            'icon' => "glyphicon glyphicon-stop",
                                            'selectedIcon' => "glyphicon glyphicon-arrow-right",
                                            'state' => [
                                                'expanded' => empty($fact_slot_array) ? true : false
                                            ],
                                            'nodes' => $fact_slot_array
                                        ]);
                        }
            array_push($rule_array,
                [
                    'id' => $rule['id'],
                    'ruleTemplateId' => $rule['rule_template'],
                    'certaintyFactor' => $rule['certainty_factor'],
                    'salience' => $rule['salience'],
                    'text' => $rule['name'],
                    'description' => $rule['description'],
                    'icon' => "glyphicon glyphicon-record",
                    'selectedIcon' => "glyphicon glyphicon-arrow-right",
                    'nodes' => [
                        [
                            'text' => Yii::t('app', 'RVML_EDITOR_PAGE_CONDITIONS'),
                            'color' => "#428bca",
                            'selectable' => false,
                            'icon' => "glyphicon glyphicon-record",
                            'tags' => [count($rule_condition_array)],
                            'nodes' => $rule_condition_array
                        ],
                        [
                            'text' => Yii::t('app', 'RVML_EDITOR_PAGE_ACTIONS'),
                            'color' => "#428bca",
                            'selectable' => false,
                            'icon' => "glyphicon glyphicon-record",
                            'tags' => [count($rule_action_array)],
                            'nodes' => $rule_action_array
                        ],
                    ]
                ]);
        }
        // Формирование массива дерева элементов БЗ
        $data = [
            [
                'id' => 'fact-templates-node',
                'text' => Yii::t('app', 'RVML_EDITOR_PAGE_FACT_TEMPLATES'),
                'color' => "#428bca",
                'selectable' => false,
                'icon' => "glyphicon glyphicon-unchecked",
                'tags' => [count($fact_template_array)],
                'state' => ['expanded' => true],
                'nodes' => $fact_template_array
            ],
            [
                'id' => 'initial-facts-node',
                'text' => Yii::t('app', 'RVML_EDITOR_PAGE_INITIAL_FACTS'),
                'color' => "#428bca",
                'selectable' => false,
                'icon' => "glyphicon glyphicon-stop",
                'tags' => [count($fact_array)],
                'state' => ['expanded' => true],
                'nodes' => $fact_array
            ],
            [
                'id' => 'rule-templates-node',
                'text' => Yii::t('app', 'RVML_EDITOR_PAGE_RULE_TEMPLATES'),
                'color' => "#428bca",
                'selectable' => false,
                'icon' => "glyphicon glyphicon-registration-mark",
                'tags' => [count($rule_template_array)],
                'state' => ['expanded' => true],
                'nodes' => $rule_template_array
            ],
            [
                'id' => 'rules-node',
                'text' => Yii::t('app', 'RVML_EDITOR_PAGE_RULES'),
                'color' => "#428bca",
                'selectable' => false,
                'icon' => "glyphicon glyphicon-record",
                'tags' => [count($rule_array)],
                'state' => ['expanded' => true],
                'nodes' => $rule_array
            ]
        ];
        // Pjax-запрос
        if (Yii::$app->request->isAjax) {
            // Формирование моделей условий шаблона правила
            $rule_template_condition_models = RuleTemplateCondition::find()
                ->where(array('rule_template' => Yii::$app->request->post('rule-template-id')))
                ->all();
            // Формирование моделей действий шаблона правила
            $rule_template_action_models = RuleTemplateAction::find()
                ->where(array('rule_template' => Yii::$app->request->post('rule-template-id')))
                ->all();
            // Формирование моделей условий правила
            $rule_condition_models = RuleCondition::find()
                ->where(array('rule' => Yii::$app->request->post('rule-id')))
                ->all();
            // Формирование моделей действий правила
            $rule_action_models = RuleAction::find()
                ->where(array('rule' => Yii::$app->request->post('rule-id')))
                ->all();
        }

        return $this->render('index', [
            'model' => $model,
            'fact_template_model' => $fact_template_model,
            'fact_template_slot_model' => $fact_template_slot_model,
            'fact_model' => $fact_model,
            'fact_slot_model' => $fact_slot_model,
            'rule_template_model' => $rule_template_model,
            'rule_template_condition_models' => (empty($rule_template_condition_models)) ?
                [new RuleTemplateCondition] : $rule_template_condition_models,
            'rule_template_action_models' => (empty($rule_template_action_models)) ?
                [new RuleTemplateAction] : $rule_template_action_models,
            'rule_model' => $rule_model,
            'rule_condition_models' => (empty($rule_condition_models)) ? [new RuleCondition] : $rule_condition_models,
            'rule_action_models' => (empty($rule_action_models)) ? [new RuleAction] : $rule_action_models,
            'data' => $data
        ]);
    }

    /**
     * Добавление нового шаблона факта.
     * @param $id - id базы знаний
     * @return bool|\yii\console\Response|Response
     */
    public function actionAddFactTemplate($id)
    {
        // Ajax-запрос
        if (Yii::$app->request->isAjax) {
            // Определение массива возвращаемых данных
            $data = array();
            // Установка формата JSON для возвращаемых данных
            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;
            // Формирование модели шаблона факта
            $model = new FactTemplate();
            // Задание id базы знаний
            $model->production_model = $id;
            // Определение полей модели шаблона факта и валидация формы
            if ($model->load(Yii::$app->request->post()) && $model->validate()) {
                // Успешный ввод данных
                $data["success"] = true;
                // Добавление нового шаблона факта в БД
                $model->save();
                // Формирование данных о новом шаблоне факта
                $data["id"] = $model->id;
                $data["name"] = $model->name;
                $data["description"] = $model->description;
            } else
                $data = ActiveForm::validate($model);
            // Возвращение данных
            $response->data = $data;

            return $response;
        }

        return false;
    }

    /**
     * Изменение шаблона факта.
     * @return bool|\yii\console\Response|Response
     */
    public function actionEditFactTemplate()
    {
        // Ajax-запрос
        if (Yii::$app->request->isAjax) {
            // Определение массива возвращаемых данных
            $data = array();
            // Установка формата JSON для возвращаемых данных
            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;
            // Поиск шаблона факта по id
            $fact_template = FactTemplate::findOne(Yii::$app->request->post('fact_template_id'));
            // Определение полей для модели шаблона факта и сохранение данной модели
            if ($fact_template->load(Yii::$app->request->post()) && $fact_template->save()) {
                // Успешный ввод данных
                $data["success"] = true;
                // Формирование данных об измененном шаблоне факта
                $data["id"] = $fact_template->id;
                $data["name"] = $fact_template->name;
                $data["description"] = $fact_template->description;
                // Поиск слотов шаблона факта
                $fact_template_slots = FactTemplateSlot::find()
                    ->where(array('fact_template' => $fact_template->id))
                    ->all();
                // Перезапись типа данных (запись названия типа данных)
                foreach ($fact_template_slots as $fact_template_slot)
                    $fact_template_slot['data_type'] = DataType::findOne($fact_template_slot['data_type'])->name;
                // Формирование данных о слотах измененного шаблона факта
                $data["fact_template_slots"] = $fact_template_slots;
                // Поиск фактов связанных с данным шаблоном факта
                $facts = Fact::find()->where(array('fact_template' => $fact_template->id))->all();
                // Перезапись названия факта
                foreach ($facts as $fact) {
                    $fact->name = $fact_template->name;
                    $fact->save();
                }
                // Формирование данных о фактах
                $data["facts"] = $facts;
                // Выборка всех слотов фактов
                $fact_slots = FactSlot::find()->all();
                // Перезапись типа данных (запись названия типа данных)
                foreach ($fact_slots as $fact_slot)
                    $fact_slot['data_type'] = DataType::findOne($fact_slot['data_type'])->name;
                // Формирование данных о слотах фактов
                $data["fact_slots"] = $fact_slots;
            } else
                $data = ActiveForm::validate($fact_template);
            // Возвращение данных
            $response->data = $data;

            return $response;
        }

        return false;
    }

    /**
     * Удаление шаблона факта.
     * @return bool|\yii\console\Response|Response
     */
    public function actionDeleteFactTemplate()
    {
        // Ajax-запрос
        if (Yii::$app->request->isAjax) {
            // Определение массива возвращаемых данных
            $data = array();
            // Установка формата JSON для возвращаемых данных
            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;
            // Поиск шаблона факта по id
            $fact_template = FactTemplate::findOne(Yii::$app->request->post('fact_template_id'));
            // Формирование данных о шаблоне факта
            $data["fact_template_id"] = $fact_template->id;
            // Поиск всех фактов принадлежащих данному шаблону факта
            $facts = Fact::find()->where(array('fact_template' => $fact_template->id))->all();
            // Формирование данных о фактах
            $data["facts"] = $facts;
            // Удаление шаблона факта из БД
            $fact_template->delete();
            // Поиск всех шаблонов правил у данной БЗ
            $rule_templates = RuleTemplate::find()
                ->where(array('production_model' => $fact_template->production_model))
                ->all();
            // Массив шаблонов правил
            $rule_template_array = array();
            // Массив правил
            $rule_array = array();
            // Обход шаблонов правил
            foreach ($rule_templates as $rule_template) {
                // Поиск условий в шаблонах правил
                $rule_template_conditions = RuleTemplateCondition::find()
                    ->where(array('rule_template' => $rule_template->id))
                    ->all();
                // Поиск действий в шаблонах правил
                $rule_template_actions = RuleTemplateAction::find()
                    ->where(array('rule_template' => $rule_template->id))
                    ->all();
                // Если отсутствуют условия или действия у данного шаблона правила
                if (!$rule_template_conditions || !$rule_template_actions) {
                    // Формирование массива шаблонов правил
                    array_push($rule_template_array, $rule_template);
                    // Поиск правил созданных на основе данного шаблона правила
                    $rules = Rule::find()->where(array('rule_template' => $rule_template->id))->all();
                    // Формирование массива правил
                    foreach ($rules as $rule)
                        array_push($rule_array, $rule);
                    // Удаление шаблона правила из БД
                    $rule_template->delete();
                }
            }
            // Формирование данных о шаблонах правил
            $data["rule_templates"] = $rule_template_array;
            // Формирование данных о правилах
            $data["rules"] = $rule_array;
            // Возвращение данных
            $response->data = $data;

            return $response;
        }

        return false;
    }

    /**
     * Добавление нового слота шаблону факта.
     * @return bool|\yii\console\Response|Response
     */
    public function actionAddFactTemplateSlot()
    {
        // Ajax-запрос
        if (Yii::$app->request->isAjax) {
            // Определение массива возвращаемых данных
            $data = array();
            // Установка формата JSON для возвращаемых данных
            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;
            // Формирование модели слота шаблона факта
            $fact_template_slot = new FactTemplateSlot();
            // Задание id шаблона факта
            $fact_template_slot->fact_template = Yii::$app->request->post('fact_template_id');
            // Определение полей модели слота шаблона факта и валидация формы
            if ($fact_template_slot->load(Yii::$app->request->post()) && $fact_template_slot->validate()) {
                // Успешный ввод данных
                $data["success"] = true;
                // Добавление нового слота шаблона факта в БД
                $fact_template_slot->save();
                // Массив слотов фактов
                $fact_slots = array();
                // Поиск фактов созданных на основе данного шаблона факта
                $facts = Fact::find()
                    ->where(array('fact_template' => Yii::$app->request->post('fact_template_id')))
                    ->all();
                // Обход найденных фактов
                foreach ($facts as $fact) {
                    // Формирование модели слота факта
                    $fact_slot = new FactSlot();
                    $fact_slot->name = $fact_template_slot->name;
                    $fact_slot->value = $fact_template_slot->default_value;
                    $fact_slot->description = $fact_template_slot->description;
                    $fact_slot->data_type = $fact_template_slot->data_type;
                    $fact_slot->fact = $fact->id;
                    $fact_slot->save();
                    // Изменение названия типа данных
                    $fact_slot->data_type = DataType::findOne($fact_template_slot->data_type)->name;
                    // Запись в массив текущего нового слота факта
                    $fact_slots[$fact->id] = $fact_slot;
                }
                // Формирование данных о новом слоте для шаблона факта
                $fact_template_slot->data_type = DataType::findOne($fact_template_slot->data_type)->name;
                $data["fact_template_slot"] = $fact_template_slot;
                // Формирование данных о новом слоте для фактов
                $data["fact_slots"] = $fact_slots;
            } else
                $data = ActiveForm::validate($fact_template_slot);
            // Возвращение данных
            $response->data = $data;

            return $response;
        }

        return false;
    }

    /**
     * Изменение слота шаблона факта.
     * @return bool|\yii\console\Response|Response
     */
    public function actionEditFactTemplateSlot()
    {
        // Ajax-запрос
        if (Yii::$app->request->isAjax) {
            // Определение массива возвращаемых данных
            $data = array();
            // Установка формата JSON для возвращаемых данных
            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;
            // Поиск слота шаблона факта по id
            $fact_template_slot = FactTemplateSlot::findOne(Yii::$app->request->post('fact_template_slot_id'));
            // Запоминание старого наименования слота шаблона факта
            $old_fact_template_slot = FactTemplateSlot::findOne(Yii::$app->request->post('fact_template_slot_id'));
            // Определение полей для модели слота шаблона факта и сохранение данной модели
            if ($fact_template_slot->load(Yii::$app->request->post()) && $fact_template_slot->save()) {
                // Успешный ввод данных
                $data["success"] = true;
                // Массив слотов фактов
                $fact_slots = array();
                // Поиск фактов созданных на основе данного шаблона факта
                $facts = Fact::find()
                    ->where(array('fact_template' => $fact_template_slot->fact_template))
                    ->all();
                // Обход найденных фактов
                foreach ($facts as $fact) {
                    // Поиск слота факта по id
                    $fact_slot = FactSlot::find()
                        ->where(array('name' => $old_fact_template_slot->name, 'fact' => $fact->id))
                        ->one();
                    if ($fact_slot) {
                        $fact_slot->name = $fact_template_slot->name;
                        $fact_slot->save();
                        // Изменение названия типа данных
                        $fact_slot->data_type = DataType::findOne($fact_template_slot->data_type)->name;
                        // Запись в массив текущего нового слота факта
                        $fact_slots[$fact->id] = $fact_slot;
                    }
                }
                // Формирование данных о новом слоте для шаблона факта
                $fact_template_slot->data_type = DataType::findOne($fact_template_slot->data_type)->name;
                $data["fact_template_slot"] = $fact_template_slot;
                // Формирование данных о новом слоте для фактов
                $data["fact_slots"] = $fact_slots;
            } else
                $data = ActiveForm::validate($fact_template_slot);
            // Возвращение данных
            $response->data = $data;

            return $response;
        }

        return false;
    }

    /**
     * Удаление слота шаблона факта.
     * @return bool|\yii\console\Response|Response
     */
    public function actionDeleteFactTemplateSlot()
    {
        // Ajax-запрос
        if (Yii::$app->request->isAjax) {
            // Определение массива возвращаемых данных
            $data = array();
            // Установка формата JSON для возвращаемых данных
            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;
            // Поиск слота шаблона факта по id
            $fact_template_slot = FactTemplateSlot::findOne(Yii::$app->request->post('fact_template_slot_id'));
            // Поиск всех фактов принадлежащих данному шаблону факта
            $facts = Fact::find()->where(array('fact_template' => $fact_template_slot->fact_template))->all();
            // Массив слотов фактов
            $fact_slots = array();
            // Обход найденных фактов
            foreach ($facts as $fact) {
                // Поиск слота факта по id
                $fact_slot = FactSlot::find()
                    ->where(array('name' => $fact_template_slot->name, 'fact' => $fact->id))
                    ->one();
                if ($fact_slot) {
                    // Запись в массив текущего нового слота факта
                    array_push($fact_slots, $fact_slot);
                    // Удаление слота факта из БД
                    $fact_slot->delete();
                }
            }
            // Формирование данных о слоте шаблона факта
            $data["fact_template_slot_id"] = $fact_template_slot->id;
            // Формирование данных о новом слоте для фактов
            $data["fact_slots"] = $fact_slots;
            // Удаление слота шаблона факта из БД
            $fact_template_slot->delete();
            // Возвращение данных
            $response->data = $data;

            return $response;
        }

        return false;
    }

    /**
     * Добавление нового начального факта.
     * @param $id - id базы знаний
     * @return bool|\yii\console\Response|Response
     */
    public function actionAddInitialFact($id)
    {
        // Ajax-запрос
        if (Yii::$app->request->isAjax) {
            // Определение массива возвращаемых данных
            $data = array();
            // Установка формата JSON для возвращаемых данных
            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;
            // Формирование модели факта
            $model = new Fact();
            // Задание id базы знаний
            $model->production_model = $id;
            // Определение полей модели факта и валидация формы
            if ($model->load(Yii::$app->request->post()) && $model->validate()) {
                // Успешный ввод данных
                $data["success"] = true;
                // Добавление нового начального факта в БД
                $model->save();
                // Формирование данных о новом начальном факте
                $data["id"] = $model->id;
                $data["name"] = $model->name;
                $data["certainty_factor"] = $model->certainty_factor;
                $data["description"] = $model->description;
                $data["fact_template"] = $model->fact_template;
                // Массив слотов начального факта
                $fact_slots = array();
                // Поиск слотов шаблона факта по id шаблона факта
                $fact_template_slots = FactTemplateSlot::find()
                    ->where(array('fact_template' => $model->fact_template))
                    ->all();
                // Обход найденных слотов шаблона факта
                foreach ($fact_template_slots as $fact_template_slot) {
                    // Формирование модели слота для начального факта
                    $fact_slot = new FactSlot();
                    $fact_slot->name = $fact_template_slot->name;
                    $fact_slot->value = $fact_template_slot->default_value;
                    $fact_slot->description = $fact_template_slot->description;
                    $fact_slot->data_type = $fact_template_slot->data_type;
                    $fact_slot->fact = $model->id;
                    $fact_slot->save();
                    // Изменение названия типа данных
                    $fact_slot->data_type = DataType::findOne($fact_template_slot->data_type)->name;
                    // Запись в массив слота начального факта
                    array_push($fact_slots, $fact_slot);
                }
                // Формирование данных о слотах начального факта
                $data["fact_slots"] = $fact_slots;
            } else
                $data = ActiveForm::validate($model);
            // Возвращение данных
            $response->data = $data;

            return $response;
        }

        return false;
    }

    /**
     * Изменение начального факта.
     * @return bool|\yii\console\Response|Response
     */
    public function actionEditInitialFact()
    {
        // Ajax-запрос
        if (Yii::$app->request->isAjax) {
            // Определение массива возвращаемых данных
            $data = array();
            // Установка формата JSON для возвращаемых данных
            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;
            // Поиск начального факта по id
            $fact = Fact::findOne(Yii::$app->request->post('fact_id'));
            // Определение полей для модели факта и сохранение данной модели
            if ($fact->load(Yii::$app->request->post()) && $fact->save()) {
                // Успешный ввод данных
                $data["success"] = true;
                // Формирование данных об измененном начальном факте
                $data["id"] = $fact->id;
                $data["name"] = $fact->name;
                $data["certainty_factor"] = $fact->certainty_factor;
                $data["description"] = $fact->description;
                $data["fact_template"] = $fact->fact_template;
                // Поиск слотов начального факта
                $fact_slots = FactSlot::find()->where(array('fact' => $fact->id))->all();
                // Перезапись типа данных (запись названия типа данных)
                foreach ($fact_slots as $fact_slot)
                    $fact_slot['data_type'] = DataType::findOne($fact_slot['data_type'])->name;
                // Формирование данных о слотах измененного начального факта
                $data["fact_slots"] = $fact_slots;
            } else
                $data = ActiveForm::validate($fact);
            // Возвращение данных
            $response->data = $data;

            return $response;
        }

        return false;
    }

    /**
     * Удаление начального факта.
     * @return bool|\yii\console\Response|Response
     */
    public function actionDeleteInitialFact()
    {
        // Ajax-запрос
        if (Yii::$app->request->isAjax) {
            // Определение массива возвращаемых данных
            $data = array();
            // Установка формата JSON для возвращаемых данных
            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;
            // Поиск факта по id
            $fact = Fact::findOne(Yii::$app->request->post('fact_id'));
            // Формирование данных о факте
            $data["fact_id"] = $fact->id;
            // Удаление факта из БД
            $fact->delete();
            // Возвращение данных
            $response->data = $data;

            return $response;
        }

        return false;
    }

    /**
     * Изменение слота факта (начального факта, условия и действия правила).
     * @return bool|\yii\console\Response|Response
     */
    public function actionEditFactSlot()
    {
        // Ajax-запрос
        if (Yii::$app->request->isAjax) {
            // Определение массива возвращаемых данных
            $data = array();
            // Установка формата JSON для возвращаемых данных
            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;
            // Поиск слота начального факта по id
            $fact_slot = FactSlot::findOne(Yii::$app->request->post('fact_slot_id'));
            // Определение полей для модели слота факта и сохранение данной модели
            if ($fact_slot->load(Yii::$app->request->post()) && $fact_slot->save()) {
                // Успешный ввод данных
                $data["success"] = true;
                // Формирование данных об измененном начальном факте
                $data["id"] = $fact_slot->id;
                $data["name"] = $fact_slot->name;
                $data["data_type"] = DataType::findOne($fact_slot->data_type)->name;
                $data["value"] = $fact_slot->value;
                $data["description"] = $fact_slot->description;
            } else
                $data = ActiveForm::validate($fact_slot);
            // Возвращение данных
            $response->data = $data;

            return $response;
        }

        return false;
    }

    /**
     * Добавление нового шаблона правила.
     * @param $id - id базы знаний
     * @return bool|\yii\console\Response|Response
     */
    public function actionAddRuleTemplate($id)
    {
        // Ajax-запрос
        if (Yii::$app->request->isAjax) {
            // Определение массива возвращаемых данных
            $data = array();
            // Установка формата JSON для возвращаемых данных
            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;
            // Формирование модели шаблона правила
            $model = new RuleTemplate();
            // Задание id базы знаний
            $model->production_model = $id;
            // Определение полей модели шаблона правила, условий и действий, а также сохранение данных моделей
            if ($model->loadAll(Yii::$app->request->post()) && $model->saveAll()) {
                // Успешный ввод данных
                $data["success"] = true;
                // Формирование данных о новом шаблоне правила
                $data["id"] = $model->id;
                $data["name"] = $model->name;
                $data["salience"] = $model->salience;
                $data["description"] = $model->description;
                // Массив шаблонов фактов (условий шаблона правила)
                $condition_fact_templates = array();
                // Массив слотов шаблонов фактов (слотов для условий шаблона правила)
                $condition_fact_template_slots = array();
                // Поиск всех условий задействованных в данном шаблоне правила
                $rule_template_conditions = RuleTemplateCondition::find()
                    ->where(array('rule_template' => $model->id))
                    ->all();
                // Обход условий данного шаблона правила
                foreach ($rule_template_conditions as $rule_template_condition) {
                    // Поиск шаблонов фактов являющиеся условиями в шаблоне правила
                    $fact_template = FactTemplate::findOne($rule_template_condition->fact_template);
                    // Поиск слотов для шаблонов фактов (условий шаблона правила)
                    $fact_template_slots = FactTemplateSlot::find()
                        ->where(array('fact_template' => $fact_template->id))
                        ->all();
                    // Перезапись типа данных (запись названия типа данных)
                    foreach ($fact_template_slots as $fact_template_slot)
                        $fact_template_slot['data_type'] = DataType::findOne($fact_template_slot['data_type'])->name;
                    // Добавление в массив шаблона факта
                    array_push($condition_fact_templates, $fact_template);
                    // Добавление в массив слотов шаблона факта
                    $condition_fact_template_slots[$fact_template->id] = $fact_template_slots;
                }
                // Формирование данных об условиях шаблона правила
                $data["rule_template_conditions"] = $rule_template_conditions;
                $data["condition_fact_templates"] = $condition_fact_templates;
                $data["condition_fact_template_slots"] = $condition_fact_template_slots;
                // Массив шаблонов фактов (действий шаблона правила)
                $action_fact_templates = array();
                // Массив слотов шаблонов фактов (слотов для действий шаблона правила)
                $action_fact_template_slots = array();
                // Поиск всех действий задействованных в данном шаблоне правила
                $rule_template_actions = RuleTemplateAction::find()->where(array('rule_template' => $model->id))->all();
                // Обход действий данного шаблона правила
                foreach ($rule_template_actions as $rule_template_action) {
                    // Поиск шаблонов фактов являющиеся действиями в шаблоне правила
                    $fact_template = FactTemplate::findOne($rule_template_action->fact_template);
                    // Поиск слотов для шаблонов фактов (действий шаблона правила)
                    $fact_template_slots = FactTemplateSlot::find()
                        ->where(array('fact_template' => $fact_template->id))
                        ->all();
                    // Перезапись типа данных (запись названия типа данных)
                    foreach ($fact_template_slots as $fact_template_slot)
                        $fact_template_slot['data_type'] = DataType::findOne($fact_template_slot['data_type'])->name;
                    // Добавление в массив шаблона факта
                    array_push($action_fact_templates, $fact_template);
                    // Добавление в массив слотов шаблона факта
                    $action_fact_template_slots[$fact_template->id] = $fact_template_slots;
                }
                // Формирование данных об условиях шаблона правила
                $data["rule_template_actions"] = $rule_template_actions;
                $data["action_fact_templates"] = $action_fact_templates;
                $data["action_fact_template_slots"] = $action_fact_template_slots;
            } else
                $data = ActiveForm::validate($model);
            // Возвращение данных
            $response->data = $data;

            return $response;
        }

        return false;
    }

    /**
     * Изменение шаблона правила.
     * @return bool|\yii\console\Response|Response
     */
    public function actionEditRuleTemplate()
    {
        // Ajax-запрос
        if (Yii::$app->request->isAjax) {
            // Определение массива возвращаемых данных
            $data = array();
            // Установка формата JSON для возвращаемых данных
            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;
            // Поиск шаблона правила по id (пакетная выборка с жадной загрузкой)
            $rule_template = RuleTemplate::findOne(Yii::$app->request->post('rule_template_id'))
                ->find()->with('rules')->where(["id"=>Yii::$app->request->post('rule_template_id')])->limit(1)->one();
            // Определение полей для модели шаблона правила и сохранение данной модели
            if ($rule_template->loadAll(Yii::$app->request->post()) && $rule_template->saveAll()) {
                // Успешный ввод данных
                $data["success"] = true;
                // Формирование данных об измененном шаблоне правила
                $data["id"] = $rule_template->id;
                $data["name"] = $rule_template->name;
                $data["salience"] = $rule_template->salience;
                $data["description"] = $rule_template->description;
                // Массив шаблонов фактов (условий шаблона правила)
                $condition_fact_templates = array();
                // Массив слотов шаблонов фактов (слотов для условий шаблона правила)
                $condition_fact_template_slots = array();
                // Поиск всех условий задействованных в данном шаблоне правила
                $rule_template_conditions = RuleTemplateCondition::find()
                    ->where(array('rule_template' => $rule_template->id))
                    ->all();
                // Обход условий данного шаблона правила
                foreach ($rule_template_conditions as $rule_template_condition) {
                    // Поиск шаблонов фактов являющиеся условиями в шаблоне правила
                    $fact_template = FactTemplate::findOne($rule_template_condition->fact_template);
                    // Поиск слотов для шаблонов фактов (условий шаблона правила)
                    $fact_template_slots = FactTemplateSlot::find()
                        ->where(array('fact_template' => $fact_template->id))
                        ->all();
                    // Перезапись типа данных (запись названия типа данных)
                    foreach ($fact_template_slots as $fact_template_slot)
                        $fact_template_slot['data_type'] = DataType::findOne($fact_template_slot['data_type'])->name;
                    // Добавление в массив шаблона факта
                    array_push($condition_fact_templates, $fact_template);
                    // Добавление в массив слотов шаблона факта
                    $condition_fact_template_slots[$fact_template->id] = $fact_template_slots;
                }
                // Формирование данных об условиях шаблона правила
                $data["rule_template_conditions"] = $rule_template_conditions;
                $data["condition_fact_templates"] = $condition_fact_templates;
                $data["condition_fact_template_slots"] = $condition_fact_template_slots;
                // Массив шаблонов фактов (действий шаблона правила)
                $action_fact_templates = array();
                // Массив слотов шаблонов фактов (слотов для действий шаблона правила)
                $action_fact_template_slots = array();
                // Поиск всех действий задействованных в данном шаблоне правила
                $rule_template_actions = RuleTemplateAction::find()
                    ->where(array('rule_template' => $rule_template->id))
                    ->all();
                // Обход действий данного шаблона правила
                foreach ($rule_template_actions as $rule_template_action) {
                    // Поиск шаблонов фактов являющиеся действиями в шаблоне правила
                    $fact_template = FactTemplate::findOne($rule_template_action->fact_template);
                    // Поиск слотов для шаблонов фактов (действий шаблона правила)
                    $fact_template_slots = FactTemplateSlot::find()
                        ->where(array('fact_template' => $fact_template->id))
                        ->all();
                    // Перезапись типа данных (запись названия типа данных)
                    foreach ($fact_template_slots as $fact_template_slot)
                        $fact_template_slot['data_type'] = DataType::findOne($fact_template_slot['data_type'])->name;
                    // Добавление в массив шаблона факта
                    array_push($action_fact_templates, $fact_template);
                    // Добавление в массив слотов шаблона факта
                    $action_fact_template_slots[$fact_template->id] = $fact_template_slots;
                }
                // Формирование данных об условиях шаблона правила
                $data["rule_template_actions"] = $rule_template_actions;
                $data["action_fact_templates"] = $action_fact_templates;
                $data["action_fact_template_slots"] = $action_fact_template_slots;
            } else
                $data = ActiveForm::validate($rule_template);
            // Возвращение данных
            $response->data = $data;

            return $response;
        }

        return false;
    }

    /**
     * Удаление шаблона правила.
     * @return bool|\yii\console\Response|Response
     */
    public function actionDeleteRuleTemplate()
    {
        // Ajax-запрос
        if (Yii::$app->request->isAjax) {
            // Определение массива возвращаемых данных
            $data = array();
            // Установка формата JSON для возвращаемых данных
            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;
            // Поиск шаблона правила по id
            $rule_template = RuleTemplate::findOne(Yii::$app->request->post('rule_template_id'));
            // Формирование данных о шаблоне правила
            $data["rule_template_id"] = $rule_template->id;
            // Поиск всех правил принадлежащих данному шаблону правила
            $rules = Rule::find()->where(array('rule_template' => $rule_template->id))->all();
            // Формирование данных о правилах
            $data["rules"] = $rules;
            // Удаление шаблона правила из БД
            $rule_template->delete();
            // Возвращение данных
            $response->data = $data;

            return $response;
        }

        return false;
    }

    /**
     * Добавление нового правила.
     * @param $id - id базы знаний
     * @return bool|\yii\console\Response|Response
     */
    public function actionAddRule($id)
    {
        // Ajax-запрос
        if (Yii::$app->request->isAjax) {
            // Определение массива возвращаемых данных
            $data = array();
            // Установка формата JSON для возвращаемых данных
            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;
            // Формирование модели правила
            $model = new Rule();
            // Задание id базы знаний
            $model->production_model = $id;
            // Определение полей модели правила и валидация формы
            if ($model->load(Yii::$app->request->post()) && $model->validate()) {
                // Успешный ввод данных
                $data["success"] = true;
                // Добавление нового правила в БД
                $model->save();
                // Массив фактов (условий правила)
                $condition_facts = array();
                // Массив слотов фактов (условий правила)
                $condition_fact_slots = array();
                // Массив условий правила
                $rule_conditions = array();
                // Массив фактов (действий правила)
                $action_facts = array();
                // Массив слотов фактов (действий правила)
                $action_fact_slots = array();
                // Массив действий правила
                $rule_actions = array();
                // Обход всех шаблонов фактов являющихся условиями в правиле
                foreach (Yii::$app->request->post('RuleTemplateCondition') as $rule_template_condition) {
                    // Поиск шаблона факта по id
                    $fact_template = FactTemplate::findOne($rule_template_condition['fact_template']);
                    // Поиск всех слотов шаблона факта
                    $fact_template_slots = FactTemplateSlot::find()
                        ->where(array('fact_template' => $fact_template->id))
                        ->all();
                    // Добавление нового факта в БД
                    $fact = new Fact();
                    $fact->name = $fact_template->name;
                    $fact->initial = false;
                    $fact->fact_template = $fact_template->id;
                    $fact->production_model = $id;
                    $fact->save();
                    // Добавление в массив факта (условия правила)
                    array_push($condition_facts, $fact);
                    // Массив слотов фактов
                    $fact_slots = array();
                    // Обход найденных слотов шаблона факта
                    foreach ($fact_template_slots as $fact_template_slot) {
                        // Добавление нового слота факта в БД
                        $fact_slot = new FactSlot();
                        $fact_slot->name = $fact_template_slot->name;
                        $fact_slot->value = $fact_template_slot->default_value;
                        $fact_slot->description = $fact_template_slot->description;
                        $fact_slot->data_type = $fact_template_slot->data_type;
                        $fact_slot->fact = $fact->id;
                        $fact_slot->save();
                        // Изменение названия типа данных
                        $fact_slot->data_type = DataType::findOne($fact_template_slot->data_type)->name;
                        // Добавление в массив текущего нового слота факта
                        array_push($fact_slots, $fact_slot);
                    }
                    // Запись в массив всех слотов факта
                    $condition_fact_slots[$fact->id] = $fact_slots;
                    // Добавление нового условия правила в БД
                    $rule_condition = new RuleCondition();
                    $rule_condition->operator = RuleCondition::OPERATOR_NONE;
                    $rule_condition->rule = $model->id;
                    $rule_condition->fact = $fact->id;
                    $rule_condition->save();
                    // Добавление в массив условия правила
                    array_push($rule_conditions, $rule_condition);
                }
                // Обход всех шаблонов фактов являющихся действиями в правиле
                foreach (Yii::$app->request->post('RuleTemplateAction') as $rule_template_action) {
                    // Поиск шаблона факта по id
                    $fact_template = FactTemplate::findOne($rule_template_action['fact_template']);
                    // Поиск всех слотов шаблона факта
                    $fact_template_slots = FactTemplateSlot::find()
                        ->where(array('fact_template' => $fact_template->id))
                        ->all();
                    // Добавление нового факта в БД
                    $fact = new Fact();
                    $fact->name = $fact_template->name;
                    $fact->initial = false;
                    $fact->fact_template = $fact_template->id;
                    $fact->production_model = $id;
                    $fact->save();
                    // Добавление в массив факта (действия правила)
                    array_push($action_facts, $fact);
                    // Массив слотов фактов
                    $fact_slots = array();
                    // Обход найденных слотов шаблона факта
                    foreach ($fact_template_slots as $fact_template_slot) {
                        // Добавление нового слота факта в БД
                        $fact_slot = new FactSlot();
                        $fact_slot->name = $fact_template_slot->name;
                        $fact_slot->value = $fact_template_slot->default_value;
                        $fact_slot->description = $fact_template_slot->description;
                        $fact_slot->data_type = $fact_template_slot->data_type;
                        $fact_slot->fact = $fact->id;
                        $fact_slot->save();
                        // Изменение названия типа данных
                        $fact_slot->data_type = DataType::findOne($fact_template_slot->data_type)->name;
                        // Добавление в массив текущего нового слота факта
                        array_push($fact_slots, $fact_slot);
                    }
                    // Запись в массив текущего нового слота факта
                    $action_fact_slots[$fact->id] = $fact_slots;
                    // Добавление нового действия правила в БД
                    $rule_action = new RuleAction();
                    $rule_action->function = $rule_template_action['function'];
                    $rule_action->rule = $model->id;
                    $rule_action->fact = $fact->id;
                    $rule_action->save();
                    // Добавление в массив действия правила
                    array_push($rule_actions, $rule_action);
                }
                // Формирование данных о новом правиле
                $data["id"] = $model->id;
                $data["name"] = $model->name;
                $data["certainty_factor"] = $model->certainty_factor;
                $data["salience"] = $model->salience;
                $data["description"] = $model->description;
                $data["rule_template"] = $model->rule_template;
                // Формирование данных о новых условиях правила
                $data["condition_facts"] = $condition_facts;
                $data["condition_fact_slots"] = $condition_fact_slots;
                $data["rule_conditions"] = $rule_conditions;
                // Формирование данных о новых действиях правила
                $data["action_facts"] = $action_facts;
                $data["action_fact_slots"] = $action_fact_slots;
                $data["rule_actions"] = $rule_actions;
            } else
                $data = ActiveForm::validate($model);
            // Возвращение данных
            $response->data = $data;

            return $response;
        }

        return false;
    }

    /**
     * Получение всех уловий и действий шаблона правила.
     * @param $id - id шаблона правила
     * @return bool|\yii\console\Response|Response
     */
    public function actionGetRuleTemplateParameters($id)
    {
        // Ajax-запрос
        if (Yii::$app->request->isAjax) {
            // Определение массива возвращаемых данных
            $data = array();
            // Установка формата JSON для возвращаемых данных
            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;
            // Поиск всех уловий шаблона правила
            $rule_template_conditions = RuleTemplateCondition::find()->where(array('rule_template' => $id))->all();
            // Поиск всех действий шаблона правила
            $rule_template_actions = RuleTemplateAction::find()->where(array('rule_template' => $id))->all();
            // Формирование данных об условиях шаблона правила
            $data["rule_template_conditions"] = $rule_template_conditions;
            // Формирование данных о действиях шаблона правила
            $data["rule_template_actions"] = $rule_template_actions;
            // Возвращение данных
            $response->data = $data;

            return $response;
        }

        return false;
    }

    /**
     * Изменение условия правила.
     * @return bool|\yii\console\Response|Response
     */
    public function actionEditRuleCondition()
    {
        // Ajax-запрос
        if (Yii::$app->request->isAjax) {
            // Определение массива возвращаемых данных
            $data = array();
            // Установка формата JSON для возвращаемых данных
            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;
            // Поиск условия правила по id
            $rule_condition = RuleCondition::findOne(Yii::$app->request->post('rule_condition_id'));
            // Поиск факта по id
            $fact = Fact::findOne(Yii::$app->request->post('fact_id'));
            // Определение полей для модели факта и сохранение данной модели
            if ($fact->load(Yii::$app->request->post()) && $fact->save()) {
                // Успешный ввод данных
                $data["success"] = true;
                // Формирование данных об измененном факте
                $data["id"] = $fact->id;
                $data["name"] = $fact->name;
                $data["certainty_factor"] = $fact->certainty_factor;
                $data["description"] = $fact->description;
                $data["fact_template"] = $fact->fact_template;
                $data["rule_condition_id"] = $rule_condition->id;
                $data["rule_condition_operator"] = $rule_condition->operator;
                // Поиск слотов факта
                $fact_slots = FactSlot::find()->where(array('fact' => $fact->id))->all();
                // Перезапись типа данных (запись названия типа данных)
                foreach ($fact_slots as $fact_slot)
                    $fact_slot['data_type'] = DataType::findOne($fact_slot['data_type'])->name;
                // Формирование данных о слотах измененного факта
                $data["fact_slots"] = $fact_slots;
            } else
                $data = ActiveForm::validate($fact);
            // Возвращение данных
            $response->data = $data;

            return $response;
        }

        return false;
    }

    /**
     * Изменение действия правила.
     * @return bool|\yii\console\Response|Response
     */
    public function actionEditRuleAction()
    {
        // Ajax-запрос
        if (Yii::$app->request->isAjax) {
            // Определение массива возвращаемых данных
            $data = array();
            // Установка формата JSON для возвращаемых данных
            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;
            // Поиск действия правила по id
            $rule_action = RuleAction::findOne(Yii::$app->request->post('rule_action_id'));
            // Поиск факта по id
            $fact = Fact::findOne(Yii::$app->request->post('fact_id'));
            // Определение полей для модели факта и сохранение данной модели
            if ($fact->load(Yii::$app->request->post()) && $fact->save()) {
                // Успешный ввод данных
                $data["success"] = true;
                // Формирование данных об измененном факте
                $data["id"] = $fact->id;
                $data["name"] = $fact->name;
                $data["certainty_factor"] = $fact->certainty_factor;
                $data["description"] = $fact->description;
                $data["fact_template"] = $fact->fact_template;
                $data["rule_action_id"] = $rule_action->id;
                $data["rule_action_function"] = $rule_action->function;
                // Поиск слотов факта
                $fact_slots = FactSlot::find()->where(array('fact' => $fact->id))->all();
                // Перезапись типа данных (запись названия типа данных)
                foreach ($fact_slots as $fact_slot)
                    $fact_slot['data_type'] = DataType::findOne($fact_slot['data_type'])->name;
                // Формирование данных о слотах измененного факта
                $data["fact_slots"] = $fact_slots;
            } else
                $data = ActiveForm::validate($fact);
            // Возвращение данных
            $response->data = $data;

            return $response;
        }

        return false;
    }

    /**
     * Изменение правила.
     * @return bool|\yii\console\Response|Response
     */
    public function actionEditRule()
    {
        // Ajax-запрос
        if (Yii::$app->request->isAjax) {
            // Определение массива возвращаемых данных
            $data = array();
            // Установка формата JSON для возвращаемых данных
            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;
            // Поиск правила по id
            $rule = Rule::findOne(Yii::$app->request->post('rule_id'));
            // Определение полей для модели правила и сохранение данной модели
            if ($rule->load(Yii::$app->request->post()) && $rule->save()) {
                // Успешный ввод данных
                $data["success"] = true;
                // Массив фактов (условий правила)
                $condition_facts = array();
                // Массив слотов фактов (условий правила)
                $condition_fact_slots = array();
                // Массив условий правила
                $rule_conditions = array();
                // Массив фактов (действий правила)
                $action_facts = array();
                // Массив слотов фактов (действий правила)
                $action_fact_slots = array();
                // Массив действий правила
                $rule_actions = array();

                // Обход всех введенный (измененных) условий правила
                foreach (Yii::$app->request->post('RuleCondition') as $rule_condition) {
                    // Поиск условия правила
                    $rule_condition_model = RuleCondition::findOne($rule_condition['id']);
                    // Добавление в массив условия правила
                    array_push($rule_conditions, $rule_condition_model);
                    // Поиск факта по id
                    $fact = Fact::findOne($rule_condition['fact']);
                    // Изменение названия факта
                    $fact->name = $rule_condition['fact_name'];
                    // Сохранение измененного факта в БД
                    $fact->save();
                    // Добавление в массив факта (условия правила)
                    array_push($condition_facts, $fact);
                    // Массив слотов фактов
                    $fact_slots = array();
                    // Поиск всех слотов факта
                    $fact_slot_models = FactSlot::find()->where(array('fact' => $fact->id))->all();
                    // Обход найденных слотов факта
                    foreach ($fact_slot_models as $fact_slot_model) {
                        // Изменение названия типа данных
                        $fact_slot_model->data_type = DataType::findOne($fact_slot_model->data_type)->name;
                        // Добавление в массив текущего слота факта
                        array_push($fact_slots, $fact_slot_model);
                    }
                    // Запись в массив всех слотов факта
                    $condition_fact_slots[$fact->id] = $fact_slots;
                }
                // Массив id условий правила, которых необходимо удалить из БД
                $deleted_rule_condition_ids = array();
                // Старые условия правила
                $old_rule_conditions = RuleCondition::find()->where(array('rule' => $rule->id))->all();
                // Обход всех старых условий правила
                foreach ($old_rule_conditions as $old_rule_condition) {
                    $flag = false;
                    // Цикл по новым измененным условиям правила
                    foreach ($rule_conditions as $rule_condition) {
                        if ($rule_condition->id == $old_rule_condition->id)
                            $flag = true;
                    }
                    // Если старое условие правила отсутствует в новом измененном списке условий, то запоминание его id
                    if ($flag == false)
                        array_push($deleted_rule_condition_ids, $old_rule_condition->id);
                }
                // Обход всех id условий правила (фактов), которых необходимо удалить из БД
                foreach ($deleted_rule_condition_ids as $deleted_rule_condition_id) {
                    // Поиск условия правила по id
                    $rule_condition = RuleCondition::findOne($deleted_rule_condition_id);
                    // Поиск факта по id
                    $fact = Fact::findOne($rule_condition->fact);
                    // Удаление условия правила из БД
                    $rule_condition->delete();
                    // Удаление факта из БД
                    $fact->delete();
                }
                //
                if (Yii::$app->request->post('RuleTemplateCondition'))
                    // Обход всех шаблонов фактов являющихся условиями в правиле
                    foreach (Yii::$app->request->post('RuleTemplateCondition') as $rule_template_condition) {
                        // Поиск шаблона факта по id
                        $fact_template = FactTemplate::findOne($rule_template_condition['fact_template']);
                        // Поиск всех слотов шаблона факта
                        $fact_template_slots = FactTemplateSlot::find()
                            ->where(array('fact_template' => $fact_template->id))
                            ->all();
                        // Добавление нового факта в БД
                        $fact = new Fact();
                        $fact->name = $fact_template->name;
                        $fact->initial = false;
                        $fact->fact_template = $fact_template->id;
                        $fact->production_model = $rule->production_model;
                        $fact->save();
                        // Добавление в массив факта (условия правила)
                        array_push($condition_facts, $fact);
                        // Массив слотов фактов
                        $fact_slots = array();
                        // Обход найденных слотов шаблона факта
                        foreach ($fact_template_slots as $fact_template_slot) {
                            // Добавление нового слота факта в БД
                            $fact_slot = new FactSlot();
                            $fact_slot->name = $fact_template_slot->name;
                            $fact_slot->value = $fact_template_slot->default_value;
                            $fact_slot->description = $fact_template_slot->description;
                            $fact_slot->data_type = $fact_template_slot->data_type;
                            $fact_slot->fact = $fact->id;
                            $fact_slot->save();
                            // Изменение названия типа данных
                            $fact_slot->data_type = DataType::findOne($fact_template_slot->data_type)->name;
                            // Добавление в массив текущего нового слота факта
                            array_push($fact_slots, $fact_slot);
                        }
                        // Запись в массив всех слотов факта
                        $condition_fact_slots[$fact->id] = $fact_slots;
                        // Добавление нового условия правила в БД
                        $rule_condition = new RuleCondition();
                        $rule_condition->operator = RuleCondition::OPERATOR_NONE;
                        $rule_condition->rule = $rule->id;
                        $rule_condition->fact = $fact->id;
                        $rule_condition->save();
                        // Добавление в массив условия правила
                        array_push($rule_conditions, $rule_condition);
                    }

                // Обход всех введенный (измененных) действий правила
                foreach (Yii::$app->request->post('RuleAction') as $rule_action) {
                    // Поиск действия правила
                    $rule_action_model = RuleAction::findOne($rule_action['id']);
                    // Изменение функции действия
                    $rule_action_model->function = $rule_action['function'];
                    // Сохранение измененного действия правила в БД
                    $rule_action_model->save();
                    // Добавление в массив действия правила
                    array_push($rule_actions, $rule_action_model);
                    // Поиск факта по id
                    $fact = Fact::findOne($rule_action['fact']);
                    // Изменение названия факта
                    $fact->name = $rule_action['fact_name'];
                    // Сохранение измененного факта в БД
                    $fact->save();
                    // Добавление в массив факта (действия правила)
                    array_push($action_facts, $fact);
                    // Массив слотов фактов
                    $fact_slots = array();
                    // Поиск всех слотов факта
                    $fact_slot_models = FactSlot::find()->where(array('fact' => $fact->id))->all();
                    // Обход найденных слотов факта
                    foreach ($fact_slot_models as $fact_slot_model) {
                        // Изменение названия типа данных
                        $fact_slot_model->data_type = DataType::findOne($fact_slot_model->data_type)->name;
                        // Добавление в массив текущего слота факта
                        array_push($fact_slots, $fact_slot_model);
                    }
                    // Запись в массив всех слотов факта
                    $action_fact_slots[$fact->id] = $fact_slots;
                }
                // Массив id действий правила, которых необходимо удалить из БД
                $deleted_rule_action_ids = array();
                // Старые действия правила
                $old_rule_actions = RuleAction::find()->where(array('rule' => $rule->id))->all();
                // Обход всех старых действий правила
                foreach ($old_rule_actions as $old_rule_action) {
                    $flag = false;
                    // Цикл по новым измененным действиям правила
                    foreach ($rule_actions as $rule_action) {
                        if ($rule_action->id == $old_rule_action->id)
                            $flag = true;
                    }
                    // Если старое действие правила отсутствует в новом измененном списке действий, то запоминание его id
                    if ($flag == false)
                        array_push($deleted_rule_action_ids, $old_rule_action->id);
                }
                // Обход всех id действий правила (фактов), которых необходимо удалить из БД
                foreach ($deleted_rule_action_ids as $deleted_rule_action_id) {
                    // Поиск действия правила по id
                    $rule_action = RuleAction::findOne($deleted_rule_action_id);
                    // Поиск факта по id
                    $fact = Fact::findOne($rule_action->fact);
                    // Удаление действия правила из БД
                    $rule_action->delete();
                    // Удаление факта из БД
                    $fact->delete();
                }
                if (Yii::$app->request->post('RuleTemplateAction'))
                    // Обход всех шаблонов фактов являющихся действиями в правиле
                    foreach (Yii::$app->request->post('RuleTemplateAction') as $rule_template_action) {
                        // Поиск шаблона факта по id
                        $fact_template = FactTemplate::findOne($rule_template_action['fact_template']);
                        // Поиск всех слотов шаблона факта
                        $fact_template_slots = FactTemplateSlot::find()
                            ->where(array('fact_template' => $fact_template->id))
                            ->all();
                        // Добавление нового факта в БД
                        $fact = new Fact();
                        $fact->name = $fact_template->name;
                        $fact->initial = false;
                        $fact->fact_template = $fact_template->id;
                        $fact->production_model = $rule->production_model;
                        $fact->save();
                        // Добавление в массив факта (действия правила)
                        array_push($action_facts, $fact);
                        // Массив слотов фактов
                        $fact_slots = array();
                        // Обход найденных слотов шаблона факта
                        foreach ($fact_template_slots as $fact_template_slot) {
                            // Добавление нового слота факта в БД
                            $fact_slot = new FactSlot();
                            $fact_slot->name = $fact_template_slot->name;
                            $fact_slot->value = $fact_template_slot->default_value;
                            $fact_slot->description = $fact_template_slot->description;
                            $fact_slot->data_type = $fact_template_slot->data_type;
                            $fact_slot->fact = $fact->id;
                            $fact_slot->save();
                            // Изменение названия типа данных
                            $fact_slot->data_type = DataType::findOne($fact_template_slot->data_type)->name;
                            // Добавление в массив текущего нового слота факта
                            array_push($fact_slots, $fact_slot);
                        }
                        // Запись в массив текущего нового слота факта
                        $action_fact_slots[$fact->id] = $fact_slots;
                        // Добавление нового действия правила в БД
                        $rule_action = new RuleAction();
                        $rule_action->function = $rule_template_action['function'];
                        $rule_action->rule = $rule->id;
                        $rule_action->fact = $fact->id;
                        $rule_action->save();
                        // Добавление в массив действия правила
                        array_push($rule_actions, $rule_action);
                    }

                // Формирование данных об измененном правиле
                $data["id"] = $rule->id;
                $data["name"] = $rule->name;
                $data["certainty_factor"] = $rule->certainty_factor;
                $data["salience"] = $rule->salience;
                $data["description"] = $rule->description;
                $data["rule_template"] = $rule->rule_template;
                // Формирование данных об условиях правила
                $data["condition_facts"] = $condition_facts;
                $data["condition_fact_slots"] = $condition_fact_slots;
                $data["rule_conditions"] = $rule_conditions;
                // Формирование данных о действиях правила
                $data["action_facts"] = $action_facts;
                $data["action_fact_slots"] = $action_fact_slots;
                $data["rule_actions"] = $rule_actions;
            } else
                $data = ActiveForm::validate($rule);
            // Возвращение данных
            $response->data = $data;

            return $response;
        }

        return false;
    }

    /**
     * Удаление правила.
     * @return bool|\yii\console\Response|Response
     */
    public function actionDeleteRule()
    {
        // Ajax-запрос
        if (Yii::$app->request->isAjax) {
            // Определение массива возвращаемых данных
            $data = array();
            // Установка формата JSON для возвращаемых данных
            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;
            // Поиск правила по id
            $rule = Rule::findOne(Yii::$app->request->post('rule_id'));
            // Поиск всех условий правила
            $rule_conditions = RuleCondition::find()->where(array('rule' => $rule->id))->all();
            // Удаление фактов являющихся условиями в удаляемом правиле
            foreach ($rule_conditions as $rule_condition) {
                $fact = Fact::findOne($rule_condition->fact);
                $fact->delete();
            }
            // Поиск всех действий правила
            $rule_actions = RuleAction::find()->where(array('rule' => $rule->id))->all();
            // Удаление фактов являющихся действиями в удаляемом правиле
            foreach ($rule_actions as $rule_action) {
                $fact = Fact::findOne($rule_action->fact);
                $fact->delete();
            }
            // Формирование данных о правиле
            $data["rule_id"] = $rule->id;
            // Удаление правила из БД
            $rule->delete();
            // Возвращение данных
            $response->data = $data;

            return $response;
        }

        return false;
    }

    /**
     * Генерация и экспорт кода CLIPS.
     * @param $id - идентификатор базы знаний
     * @return bool|\yii\console\Response|Response
     */
    public function actionGenerateClipsCode($id)
    {
        // Текущая (выбранная) база знаний
        $model = $this->findModel($id);
        // Создание экземпляра класса CLIPSCodeGenerator (генератора кода базы знаний в формате CLIPS)
        $clips_code_generator = new CLIPSCodeGenerator();
        // Генерация кода базы знаний в формате CLIPS
        $clips_code_generator->generateCLIPSCode($model);

        return false;
    }

    /**
     * Finds the KnowledgeBase model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return KnowledgeBase the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = KnowledgeBase::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'ERROR_MESSAGE_PAGE_NOT_FOUND'));
        }
    }
}