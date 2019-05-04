<?php

namespace app\modules\api\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use app\components\XMLAnalyzer;
use app\components\OWLCodeGenerator;
use app\components\OntologyGenerator;
use app\components\CLIPSCodeGenerator;
use app\components\ProductionModelGenerator;
use app\modules\knowledge_base\models\DataType;
use app\modules\knowledge_base\models\OntologyClass;
use app\modules\knowledge_base\models\Relationship;
use app\modules\knowledge_base\models\FactTemplate;
use app\modules\knowledge_base\models\RuleTemplate;
use app\modules\user\models\User;
use app\modules\knowledge_base\models\KnowledgeBase;
use app\modules\knowledge_base\models\SubjectDomain;
use app\modules\software_component\models\SoftwareComponent;

/**
 * Default controller for the `api` module
 */
class DefaultController extends Controller
{
    public function behaviors()
    {
        return [
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
     * Возвращение списка всех программных компонентов.
     * @return bool|string - возвращает строку с описанием программных компонентов,
     * иначе false, если программные компоненты не найдены
     */
    public function actionGetAllModulesList()
    {
        $all_modules_list = false;
        // Поиск всех программных компонентов
        $software_components = SoftwareComponent::find()->all();
        // Обход программных компонентов
        foreach ($software_components as $software_component)
            // Формирование строки с описанием программных компонентов
            $all_modules_list .= '~id=' . $software_component->id .
                ';name=' . $software_component->name .
                ';description=' . $software_component->description .
                ';type=' . $software_component->type .
                ';status=' . $software_component->status;

        return $all_modules_list;
    }

    /**
     * Возвращение списка программных компонентов по типу и/или статусу.
     * @param $type - тип программного компонента
     * @param $status - статус программного компонента
     * @return bool|string - возвращает строку с описанием программных компонентов,
     * иначе false, если программные компоненты не найдены
     */
    public function actionGetModulesList($type, $status)
    {
        $modules_list = false;
        // Поиск программных компонентов по типу и статусу
        $software_components = SoftwareComponent::find()->where(array('type' => $type, 'status' => $status))->all();
        // Обход программных компонентов
        foreach ($software_components as $software_component)
            // Формирование строки с описанием программных компонентов
            $modules_list .= '~id=' . $software_component->id .
                ';name=' . $software_component->name .
                ';description=' . $software_component->description;

        return $modules_list;
    }

    /**
     * Возвращение списка всех баз знаний.
     * @return bool|string - возвращает строку с описанием баз знаний,
     * иначе false, если базы знаний не найдены
     */
    public function actionGetAllKnowledgeBasesList()
    {
        $all_knowledge_bases_list = false;
        // Поиск всех баз знаний
        $knowledge_bases = KnowledgeBase::find()->all();
        // Обход баз знаний
        foreach ($knowledge_bases as $knowledge_base) {
            // Поиск предметной области по id
            $subject_domain = SubjectDomain::findOne($knowledge_base->subject_domain);
            // Поиск пользователя (автора базы знаний) по id
            $author = User::findOne($knowledge_base->author);
            // Формирование строки с описанием баз знаний
            $all_knowledge_bases_list .= '~id=' . $knowledge_base->id .
                ';name=' . $knowledge_base->name .
                ';description=' . $knowledge_base->description .
                ';subject_domain=' . $subject_domain->name .
                ';type=' . $knowledge_base->type .
                ';status=' . $knowledge_base->status .
                ';author=' . $author->username;
        }

        return $all_knowledge_bases_list;
    }

    /**
     * Возвращение списка баз знаний по типу и/или статусу.
     * @param $type - тип базы знаний
     * @param $status - статус базы знаний
     * @return bool|string - возвращает строку с описанием баз знаний,
     * иначе false, если баз знаний не найдены
     */
    public function actionGetKnowledgeBasesList($type, $status)
    {
        $knowledge_bases_list = false;
        // Поиск баз знаний по типу и статусу
        $knowledge_bases = KnowledgeBase::find()->where(array('type' => $type, 'status' => $status))->all();
        // Обход баз знаний
        foreach ($knowledge_bases as $knowledge_base) {
            // Поиск предметной области по id
            $subject_domain = SubjectDomain::findOne($knowledge_base->subject_domain);
            // Поиск пользователя (автора базы знаний) по id
            $author = User::findOne($knowledge_base->author);
            // Формирование строки с описанием баз знаний
            $knowledge_bases_list .= '~id=' . $knowledge_base->id .
                ';name=' . $knowledge_base->name .
                ';description=' . $knowledge_base->description .
                ';subject_domain=' . $subject_domain->name .
                ';author=' . $author->username;
        }

        return $knowledge_bases_list;
    }

    /**
     * Импорт концептуальной модели.
     * @param $id - идентификатор базы знаний
     * @param $sc - идентификатор программного компонента
     * @return bool|string - возвращает текст хода выполнения импорта концептуальной модели
     * (генерации элементов продукций), иначе false
     */
    public function actionImportConceptualModel($id, $sc)
    {
        // Переменная для хранения хода выполнения импорта концептуальной модели
        $import_progress = false;
        // Если метод запроса POST
        if (Yii::$app->request->isPost) {
            // Текущая (выбранная) база знаний
            $model = KnowledgeBase::findOne($id);
            // Получение XML-строк из XML-файла концептуальной модели
            $xml_rows = simplexml_load_file($_POST['file']);
            // Если тип базы знаний - онтология
            if ($model->type == KnowledgeBase::TYPE_ONTOLOGY) {
                // Проверка существования сгенерированных элементов у онтологии
                $exist_knowledge_base_elements = OntologyGenerator::existElements($id);
                // Если существуют элементы у данной онтологии
                if ($exist_knowledge_base_elements) {
                    // Каскадное удаление всех данных связанных с данной онтологией
                    OntologyClass::deleteAll(array('ontology' => $id));
                    Relationship::deleteAll(array('ontology' => $id));
                    DataType::deleteAll(array('knowledge_base' => $id));
                }
            }
            // Если тип базы знаний - продукции
            if ($model->type == KnowledgeBase::TYPE_RULES) {
                // Проверка существования сгенерированных элементов у продукционной модели
                $exist_knowledge_base_elements = ProductionModelGenerator::existElements($id);
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
            }
        }

        return $import_progress;
    }

    /**
     * Экспорт базы знаний.
     * @param $id - идентификатор базы знаний
     * @return string - код сгенерированной базы знаний
     */
    public function actionExportKnowledgeBase($id)
    {
        // Текущая (выбранная) база знаний
        $model = KnowledgeBase::findOne($id);
        // Если тип базы знаний - онтология
        if ($model->type == KnowledgeBase::TYPE_ONTOLOGY) {
            // Создание экземпляра класса OntologyGenerator (генератора кода базы знаний в формате OWL)
            $owl_code_generator = new OWLCodeGenerator();
            // Генерация кода базы знаний в формате OWL
            $owl_code_generator->generateOWLCode($model);
        }
        // Если тип базы знаний - продукции
        if ($model->type == KnowledgeBase::TYPE_RULES) {
            // Создание экземпляра класса CLIPSCodeGenerator (генератора кода базы знаний в формате CLIPS)
            $clips_code_generator = new CLIPSCodeGenerator();
            // Генерация кода базы знаний в формате CLIPS
            $clips_code_generator->generateCLIPSCode($model);
        }
    }
}