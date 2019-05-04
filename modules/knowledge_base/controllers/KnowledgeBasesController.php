<?php

namespace app\modules\knowledge_base\controllers;

use Yii;
use yii\web\Controller;
use yii\web\UploadedFile;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use app\components\XMLAnalyzer;
use app\components\OWLCodeGenerator;
use app\components\OntologyGenerator;
use app\components\CLIPSCodeGenerator;
use app\components\ProductionModelGenerator;
use app\modules\knowledge_base\models\KnowledgeBase;
use app\modules\knowledge_base\models\KnowledgeBaseSearch;
use app\modules\knowledge_base\models\DataType;
use app\modules\knowledge_base\models\OntologyClass;
use app\modules\knowledge_base\models\Relationship;
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
use app\modules\software_component\models\SoftwareComponent;
use app\modules\software_component\models\SoftwareComponentSearch;
use app\modules\software_component\models\ConceptualModelFileForm;

/**
 * KnowledgeBasesController implements the CRUD actions for KnowledgeBase model.
 */
class KnowledgeBasesController extends Controller
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
                        'roles' => ['admin']
                    ],
                ],
            ],
        ];
    }

    /**
     * Lists all KnowledgeBase models.
     * @return mixed
     */
    public function actionList()
    {
        $searchModel = new KnowledgeBaseSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, null);

        return $this->render('list', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single KnowledgeBase model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new KnowledgeBase model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new KnowledgeBase();
        $model->author = Yii::$app->user->identity->getId();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->getSession()->setFlash('success',
                Yii::t('app', 'KNOWLEDGE_BASE_MODEL_MESSAGE_CREATE_KNOWLEDGE_BASE'));

            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing KnowledgeBase model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->getSession()->setFlash('success',
                Yii::t('app', 'KNOWLEDGE_BASE_MODEL_MESSAGE_UPDATED_KNOWLEDGE_BASE'));

            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing KnowledgeBase model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        Yii::$app->getSession()->setFlash('success',
            Yii::t('app', 'KNOWLEDGE_BASE_MODEL_MESSAGE_DELETED_KNOWLEDGE_BASE'));

        return $this->redirect(['list']);
    }

    /**
     * Displays all available software components for generation of ontology.
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionGenerateOntology($id)
    {
        $searchModel = new SoftwareComponentSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        // Текущая (выбранная) база знаний
        $model = $this->findModel($id);
        // Выборка всех сгенерированных программных компонентов
        $dataProvider->query->andWhere(['status' => SoftwareComponent::STATUS_GENERATED]);
        // Если тип базы знаний - онтология, то выбираются программные компоненты CM-ONT
        if($model->type == KnowledgeBase::TYPE_ONTOLOGY)
            $dataProvider->query->andWhere(['type' => 0]);

        return $this->render('generate-ontology', [
            'model' => $model,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays all available software components for generation of OWL code.
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionGenerateOwlCode($id)
    {
        $searchModel = new SoftwareComponentSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        // Текущая (выбранная) база знаний
        $model = $this->findModel($id);
        // Выборка всех сгенерированных программных компонентов
        $dataProvider->query->andWhere(['status' => SoftwareComponent::STATUS_GENERATED]);
        // Если тип базы знаний - онтология, то выбираются программные компоненты ONT-OWL
        if($model->type == KnowledgeBase::TYPE_ONTOLOGY)
            $dataProvider->query->andWhere(['type' => 2]);

        return $this->render('generate-owl-code', [
            'model' => $model,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays all available software components for generation of production model.
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionGenerateProductionModel($id)
    {
        $searchModel = new SoftwareComponentSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        // Текущая (выбранная) база знаний
        $model = $this->findModel($id);
        // Выборка всех сгенерированных программных компонентов
        $dataProvider->query->andWhere(['status' => [SoftwareComponent::STATUS_GENERATED,
            SoftwareComponent::STATUS_OUTDATED]]);
        // Если тип базы знаний - продукции, то выбираются программные компоненты CM-RULES
        if($model->type == KnowledgeBase::TYPE_RULES)
            $dataProvider->query->andWhere(['type' => 1]);

        return $this->render('generate-production-model', [
            'model' => $model,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays all available software components for generation of CLIPS code.
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionGenerateClipsCode($id)
    {
        $searchModel = new SoftwareComponentSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        // Текущая (выбранная) база знаний
        $model = $this->findModel($id);
        // Выборка всех сгенерированных программных компонентов
        $dataProvider->query->andWhere(['status' => SoftwareComponent::STATUS_GENERATED]);
        // Если тип базы знаний - продукции, то выбираются программные компоненты RULES-CLIPS
        if($model->type == KnowledgeBase::TYPE_RULES)
            $dataProvider->query->andWhere(['type' => 3]);

        return $this->render('generate-clips-code', [
            'model' => $model,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Import conceptual model.
     * @param $id - идентификатор базы знаний
     * @param $sc - идентификатор программного компонента
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionImportConceptualModel($id, $sc)
    {
        $import_progress = '';
        // Переменная результата проверки существования сгенерированных элементов у базы знаний
        $exist_knowledge_base_elements = false;
        // Текущая (выбранная) база знаний
        $model = $this->findModel($id);
        // Если тип базы знаний - онтология
        if ($model->type == KnowledgeBase::TYPE_ONTOLOGY)
            // Проверка существования сгенерированных элементов у онтологии
            $exist_knowledge_base_elements = OntologyGenerator::existElements($id);
        // Если тип базы знаний - продукции
        if ($model->type == KnowledgeBase::TYPE_RULES)
            // Проверка существования сгенерированных элементов у продукционной модели
            $exist_knowledge_base_elements = ProductionModelGenerator::existElements($id);

        // Создание модели формы загрузки файлов
        $file_form = new ConceptualModelFileForm();
        // Если пользователь импортировал файл
        if (Yii::$app->request->isPost) {
            $file_form->conceptual_model_file = UploadedFile::getInstance($file_form, 'conceptual_model_file');
            if ($file_form->validate()) {
                // Установка сценария специальной валидации загружаемого XML-файла концептуальной модели
                $file_form->scenario = 'validation_file';
                if ($file_form->validate()) {
                    // Получение временного загруженного XML-файла концептуальной модели
                    $file = $file_form->conceptual_model_file->tempName;
                    // Получение XML-строк из XML-файла концептуальной модели
                    $xml_rows = simplexml_load_file($file);
                    // Если тип базы знаний - онтология
                    if ($model->type == KnowledgeBase::TYPE_ONTOLOGY) {
                        // Если существуют элементы у данной онтологии
                        if ($exist_knowledge_base_elements) {
                            // Каскадное удаление всех данных связанных с данной онтологией
                            OntologyClass::deleteAll(array('ontology' => $id));
                            Relationship::deleteAll(array('ontology' => $id));
                            DataType::deleteAll(array('knowledge_base' => $id));
                        }
                        // Создание экземпляра класса XMLAnalyzer (анализатора концептуальной модели)
                        $xml_analyzer = new XMLAnalyzer();
                        // Создание (генерация) онтологической модели
                        $import_progress = $xml_analyzer->createOntology($id, $sc, $xml_rows);
                        // Вывод сообщения об успешной генерации онтологии
                        Yii::$app->getSession()->setFlash('success',
                            Yii::t('app', 'KNOWLEDGE_BASE_MODEL_MESSAGE_ONTOLOGY_SUCCESSFULLY_GENERATED'));
                    }

                    // Если тип базы знаний - продукции
                    if ($model->type == KnowledgeBase::TYPE_RULES) {
                        // Если существуют элементы у данной продукционной модели
                        if ($exist_knowledge_base_elements) {
                            // Каскадное удаление всех данных связанных с данной продукционной моделью
                            FactTemplate::deleteAll(array('production_model' => $id));
                            RuleTemplate::deleteAll(array('production_model' => $id));
                            DataType::deleteAll(array('knowledge_base' => $id));
                        }
                        // Создание экземпляра класса XMLAnalyzer (анализатора концептуальной модели)
                        $xml_analyzer = new XMLAnalyzer();
                        // Создание (генерация) продукционной модели
                        $import_progress = $xml_analyzer->createProductionModel($id, $sc, $xml_rows);
                        // Вывод сообщения об успешной генерации продукционной модели
                        Yii::$app->getSession()->setFlash('success',
                            Yii::t('app', 'KNOWLEDGE_BASE_MODEL_MESSAGE_PRODUCTION_MODEL_SUCCESSFULLY_GENERATED'));
                    }
                }
            }
        }

        return $this->render('import-conceptual-model', [
            'model' => $model,
            'file_form' => $file_form,
            'exist_knowledge_base_elements' => $exist_knowledge_base_elements,
            'import_progress' => $import_progress,
        ]);
    }

    /**
     * Export knowledge base.
     * @param $id - идентификатор базы знаний
     * @param $sc - идентификатор программного компонента
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionExportKnowledgeBase($id, $sc)
    {
        // Переменные результата проверки существования сгенерированных элементов у соответствующих баз знаний
        $exist_ontology_elements = false;
        $exist_production_model_elements = false;
        // Текущая (выбранная) база знаний
        $model = $this->findModel($id);
        // Создание экземпляра класса OntologyGenerator (генератора кода базы знаний в формате OWL)
        $owl_code_generator = new OWLCodeGenerator();
        // Создание экземпляра класса CLIPSCodeGenerator (генератора кода базы знаний в формате CLIPS)
        $clips_code_generator = new CLIPSCodeGenerator();
        // Если тип базы знаний - онтология
        if ($model->type == KnowledgeBase::TYPE_ONTOLOGY)
            // Проверка существования сгенерированных элементов у онтологии
            $exist_ontology_elements = $owl_code_generator->existElements($id);
        // Если тип базы знаний - продукции
        if ($model->type == KnowledgeBase::TYPE_RULES)
            // Проверка существования сгенерированных элементов у продукционной модели
            $exist_production_model_elements = $clips_code_generator->existElements($id);

        // Если пользователь экспортировал базу знаний
        if (Yii::$app->request->isPost) {
            // Если тип базы знаний - онтология
            if ($model->type == KnowledgeBase::TYPE_ONTOLOGY)
                // Генерация кода базы знаний в формате OWL
                $owl_code_generator->generateOWLCode($model);
            // Если тип базы знаний - продукции
            if ($model->type == KnowledgeBase::TYPE_RULES)
                // Генерация кода базы знаний в формате CLIPS
                $clips_code_generator->generateCLIPSCode($model);
        }

        return $this->render('export-knowledge-base', [
            'model' => $model,
            'exist_ontology_elements' => $exist_ontology_elements,
            'exist_production_model_elements' => $exist_production_model_elements,
        ]);
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