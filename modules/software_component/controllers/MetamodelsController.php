<?php

namespace app\modules\software_component\controllers;

use Yii;
use yii\web\Response;
use yii\web\Controller;
use yii\web\UploadedFile;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\bootstrap\ActiveForm;
use yii\web\NotFoundHttpException;
use app\components\XMLFile;
use app\components\XSDFile;
use app\modules\software_component\models\XMLSchemaFileForm;
use app\modules\software_component\models\ConceptualModelFileForm;
use app\modules\software_component\models\Metamodel;
use app\modules\software_component\models\MetamodelSearch;
use app\modules\software_component\models\Metaclass;
use app\modules\software_component\models\Metaattribute;
use app\modules\software_component\models\Metarelation;
use app\modules\software_component\models\Metareference;

/**
 * MetamodelsController implements the CRUD actions for Metamodel model.
 */
class MetamodelsController extends Controller
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
     * Enable Ajax validation.
     * @param $model
     * @return array
     */
    protected function performAjaxValidation($model)
    {
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
    }

    /**
     * Lists all Metamodel models.
     * @return mixed
     */
    public function actionList()
    {
        $searchModel = new MetamodelSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('list', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Metamodel model.
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
     * Creates a new Metamodel model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Metamodel();

        if ($model->load(Yii::$app->request->post())) {
            $model->type = Metamodel::USER_TYPE;
            $model->author = Yii::$app->user->identity->getId();
            if ($model->save()) {
                Yii::$app->getSession()->setFlash('success',
                    Yii::t('app', 'METAMODEL_MODEL_MESSAGE_CREATE_METAMODEL'));

                return $this->redirect(['view', 'id' => $model->id]);
            }
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Metamodel model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->getSession()->setFlash('success',
                Yii::t('app', 'METAMODEL_MODEL_MESSAGE_UPDATED_METAMODEL'));

            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing Metamodel model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        Yii::$app->getSession()->setFlash('success', Yii::t('app', 'METAMODEL_MODEL_MESSAGE_DELETED_METAMODEL'));

        return $this->redirect(['list']);
    }

    /**
     * Import metamodel in the form of XML Schema (XSD-file).
     * @param integer $id
     * @return mixed
     */
    public function actionImportXmlSchema($id)
    {
        // Получение метамодели по ее id
        $model = $this->findModel($id);
        // Поиск всех метаклассов принадлежащие данной метамодели
        $metaclasses = Metaclass::find()->where(array('metamodel' => $model->id))->asArray()->all();

        // Создаем модель формы загрузки файлов
        $file_form = new XMLSchemaFileForm();
        // Пользователь импортировал файл
        if (Yii::$app->request->isPost) {
            $file_form->xml_schema_file = UploadedFile::getInstance($file_form, 'xml_schema_file');
            if ($file_form->validate()) {
                // Установка сценария специальной валидации загружаемого XSD файла
                $file_form->scenario = 'validation_file';
                if ($file_form->validate()) {
                    // Если метамодель уже сформирована, то удаляем все связанные с ней данные
                    if (!empty($metaclasses))
                        Metaclass::deleteAll(array('metamodel' => $model->id));
                    // Получаем временно загруженный XSD файл
                    $file = $file_form->xml_schema_file->tempName;
                    // Получаем XML-строки из XSD-файла
                    $xml_rows = simplexml_load_file($file);
                    // Создаем экземпляр класса XSDFile
                    $xsd_file = new XSDFile();
                    // Проверка корректности XML-схемы
                    $xsd_file->isXSD($xml_rows);
                    // Импортирование элементов метамодели
                    $xsd_file->importElements($xml_rows);

                    // Деактивация правого меню
                    $this->layout = "/main";
                    // Вывод сообщения об успешном импортировании
                    Yii::$app->getSession()->setFlash('success',
                        Yii::t('app', 'XML_SCHEMA_FILE_FORM_MESSAGE_XML_SCHEMA_FILE_SUCCESS'));
                    // Поиск всех метаклассов принадлежащие данной метамодели
                    $metaclasses = Metaclass::find()->where(array('metamodel' => $model->id))->asArray()->all();
                    // Поиск всех атрибутов метаклассов
                    $metaattributes = Metaattribute::find()->asArray()->all();
                    // Поиск всех связей между метаклассами
                    $metarelations = Metarelation::find()->asArray()->all();
                    // Поиск всех ссылок между метаатрибутами
                    $metareferences = Metareference::find()->asArray()->all();
                    // Проверка типа метамодели
                    $default_metamodel = false;
                    if ($model->type == Metamodel::DEFAULT_TYPE)
                        $default_metamodel = true;
                    // Формирование модели отношения между метаклассами
                    $metarelation = new Metarelation();
                    // Формирование модели ссылки отношения по идентификатору между метаклассами
                    $metareference = new Metareference();

                    return $this->render('metamodel-editor', [
                        'model' => $model,
                        'metaclasses' => $metaclasses,
                        'metaattributes' => $metaattributes,
                        'metarelations' => $metarelations,
                        'metareferences' => $metareferences,
                        'default_metamodel' => $default_metamodel,
                        'metarelation' => $metarelation,
                        'metareference' => $metareference,
                    ]);
                }
            }
        }

        return $this->render('import-xml-schema', [
            'model' => $model,
            'metaclasses' => $metaclasses,
            'file_form'=>$file_form,
        ]);
    }

    /**
     * Import metamodel from a conceptual model (reverse engineering).
     * @param integer $id
     * @return mixed
     */
    public function actionImportConceptualModel($id)
    {
        // Получение метамодели по ее id
        $model = $this->findModel($id);
        // Поиск всех метаклассов принадлежащие данной метамодели
        $metaclasses = Metaclass::find()->where(array('metamodel' => $model->id))->asArray()->all();

        // Создаем модель формы загрузки файлов
        $file_form = new ConceptualModelFileForm();
        // Пользователь импортировал файл
        if (Yii::$app->request->isPost) {
            $file_form->conceptual_model_file = UploadedFile::getInstance($file_form, 'conceptual_model_file');
            if ($file_form->validate()) {
                // Установка сценария специальной валидации загружаемого XML-файла концептуальной модели
                $file_form->scenario = 'validation_file';
                if ($file_form->validate()) {
                    // Если метамодель уже сформирована, то удаляем все связанные с ней данные
                    if (!empty($metaclasses))
                        Metaclass::deleteAll(array('metamodel' => $model->id));
                    // Получаем временно загруженный XML-файл концептуальной модели
                    $file = $file_form->conceptual_model_file->tempName;
                    // Получаем XML-строки из XML-файла концептуальной модели
                    $xml_rows = simplexml_load_file($file);
                    // Создаем экземпляр класса XMLFile
                    $xml_file = new XMLFile();
                    // Извлечение элементов метамодели из XML-файла концептуальной модели
                    $xml_file->extractionMetamodelElements($xml_rows);
                    // Сохранение извлеченных элементов метамодели
                    $xml_file->saveMetamodelElements($model->id);

                    // Деактивация правого меню
                    $this->layout = "/main";
                    // Вывод сообщения об успешном импортировании
                    Yii::$app->getSession()->setFlash('success',
                        Yii::t('app', 'CONCEPTUAL_MODEL_FILE_FORM_MESSAGE_CONCEPTUAL_MODEL_FILE_SUCCESS'));
                    // Поиск всех метаклассов принадлежащие данной метамодели
                    $metaclasses = Metaclass::find()->where(array('metamodel' => $model->id))->asArray()->all();
                    // Поиск всех атрибутов метаклассов
                    $metaattributes = Metaattribute::find()->asArray()->all();
                    // Поиск всех связей между метаклассами
                    $metarelations = Metarelation::find()->asArray()->all();
                    // Поиск всех ссылок между метаатрибутами
                    $metareferences = Metareference::find()->asArray()->all();
                    // Проверка типа метамодели
                    $default_metamodel = false;
                    if ($model->type == Metamodel::DEFAULT_TYPE)
                        $default_metamodel = true;
                    // Формирование модели отношения между метаклассами
                    $metarelation = new Metarelation();
                    // Формирование модели ссылки отношения по идентификатору между метаклассами
                    $metareference = new Metareference();

                    return $this->render('metamodel-editor', [
                        'model' => $model,
                        'metaclasses' => $metaclasses,
                        'metaattributes' => $metaattributes,
                        'metarelations' => $metarelations,
                        'metareferences' => $metareferences,
                        'default_metamodel' => $default_metamodel,
                        'metarelation' => $metarelation,
                        'metareference' => $metareference,
                    ]);
                }
            }
        }

        return $this->render('import-conceptual-model', [
            'model' => $model,
            'metaclasses' => $metaclasses,
            'file_form'=>$file_form,
        ]);
    }

    /**
     * Displays a single Metamodel model in graphical form.
     * @param integer $id
     * @return mixed
     */
    public function actionMetamodelEditor($id)
    {
        // Деактивация правого меню
        $this->layout = "/main";
        // Получение метамодели по ее id
        $metamodel = $this->findModel($id);
        // Поиск всех метаклассов принадлежащие данной метамодели
        $metaclasses = Metaclass::find()->where(array('metamodel' => $metamodel->id))->asArray()->all();
        // Поиск всех атрибутов метаклассов
        $metaattributes = Metaattribute::find()->asArray()->all();
        // Поиск всех связей между метаклассами
        $metarelations = Metarelation::find()->asArray()->all();
        // Поиск всех ссылок между метаатрибутами
        $metareferences = Metareference::find()->asArray()->all();
        // Проверка типа метамодели
        $default_metamodel = false;
        if ($metamodel->type == Metamodel::DEFAULT_TYPE)
            $default_metamodel = true;
        // Формирование модели отношения между метаклассами
        $metarelation = new Metarelation();
        // Формирование модели ссылки отношения по идентификатору между метаклассами
        $metareference = new Metareference();

        return $this->render('metamodel-editor', [
            'model' => $metamodel,
            'metaclasses' => $metaclasses,
            'metaattributes' => $metaattributes,
            'metarelations' => $metarelations,
            'metareferences' => $metareferences,
            'default_metamodel' => $default_metamodel,
            'metarelation' => $metarelation,
            'metareference' => $metareference,
        ]);
    }

    /**
     * Добавление нового отношения по идентификатору между метаклассами.
     * @return bool|\yii\console\Response|Response
     */
    public function actionAddRelation()
    {
        // Ajax-запрос
        if (Yii::$app->request->isAjax) {
            // Определение массива возвращаемых данных
            $data = array();
            // Установка формата JSON для возвращаемых данных
            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;
            // Формирование модели отношения между метаклассами
            $metarelation = new Metarelation();
            // Определение полей модели отношения
            $metarelation->load(Yii::$app->request->post());
            // Формирование модели ссылки между метаатрибутами
            $metareference = new Metareference();
            // Определение полей модели ссылки
            $metareference->load(Yii::$app->request->post());
            // Валидация формы
            if ($metarelation->validate() && $metareference->validate()) {
                // Ошибки ввода отсутствуют
                $data["error_status"] = false;
                // Добавление нового отношения между метаклассами в БД
                $metarelation->save();
                // Определение поля метаотношения для модели ссылки
                $metareference->metarelation = $metarelation->id;
                // Добавление новой ссылки между метаатрибутами в БД
                $metareference->save();
                // Формирование данных о новой связи элементов
                $data["name"] = $metarelation->name;
                $data["left_metaclass_id"] = 'class-' . $metarelation->left_metaclass;
                $data["right_metaclass_id"] = 'class-' . $metarelation->right_metaclass;
                $data["left_metaattribute_id"] = 'attribute-' . $metareference->left_metaattribute;
                $data["right_metaattribute_id"] = 'attribute-' . $metareference->right_metaattribute;
            } else {
                // Ошибки ввода присутствуют
                $data["error_status"] = true;
                // Формирование данных ошибок ввода
                $data["errors"] = ActiveForm::validateMultiple([$metarelation, $metareference]);
            }
            // Возвращение данных
            $response->data = $data;
            return $response;
        }
        return false;
    }

    /**
     * Получение текущих значений метаотношения и его метассылки.
     * @return bool|\yii\console\Response|Response
     */
    function actionGetRelationValues()
    {
        // Ajax-запрос
        if (Yii::$app->request->isAjax) {
            // Определение массива возвращаемых данных
            $data = array();
            // Установка формата JSON для возвращаемых данных
            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;
            // Поиск метассылки (отношения между метаатрибутами)
            $metareference = Metareference::find()
                ->where(array('left_metaattribute' => Yii::$app->request->post('left_attribute_id'),
                    'right_metaattribute' => Yii::$app->request->post('right_attribute_id')))
                ->one();
            // Поиск метаотношения
            $metarelation = Metarelation::findOne($metareference->metarelation);
            // Формирование данных о найденном метаотношении
            $data["metarelation_id"] = $metarelation->id;
            $data["name"] = $metarelation->name;
            $data["left_metaclass_id"] = $metarelation->left_metaclass;
            $data["right_metaclass_id"] = $metarelation->right_metaclass;
            $data["metareference_id"] = $metareference->id;
            $data["left_metaattribute_id"] = $metareference->left_metaattribute;
            $data["right_metaattribute_id"] = $metareference->right_metaattribute;
            // Возвращение данных
            $response->data = $data;
            return $response;
        }
        return false;
    }

    /**
     * Изменение отношения по идентификатору между метаклассами.
     * @return bool|\yii\console\Response|Response
     */
    public function actionEditRelation()
    {
        // Ajax-запрос
        if (Yii::$app->request->isAjax) {
            // Определение массива возвращаемых данных
            $data = array();
            // Установка формата JSON для возвращаемых данных
            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;
            // Поиск отношения между метаклассами по id
            $metarelation = Metarelation::findOne(Yii::$app->request->post('metarelation_id'));
            // Переопределение полей модели отношения
            $metarelation->load(Yii::$app->request->post());
            // Поиск ссылки между метаатрибутами по id
            $metareference = Metareference::findOne(Yii::$app->request->post('metareference_id'));
            // Переопределение полей модели ссылки
            $metareference->load(Yii::$app->request->post());
            // Валидация формы
            if ($metarelation->validate() && $metareference->validate()) {
                // Ошибки ввода отсутствуют
                $data["error_status"] = false;
                // Изменение отношения между метаклассами в БД
                $metarelation->save();
                // Изменение ссылки между метаатрибутами в БД
                $metareference->save();
                // Формирование данных об измененной связи элементов
                $data["name"] = $metarelation->name;
                $data["left_metaattribute_id"] = 'attribute-' . $metareference->left_metaattribute;
                $data["right_metaattribute_id"] = 'attribute-' . $metareference->right_metaattribute;
            } else {
                // Ошибки ввода присутствуют
                $data["error_status"] = true;
                // Формирование данных ошибок ввода
                $data["errors"] = ActiveForm::validateMultiple([$metarelation, $metareference]);
            }
            // Возвращение данных
            $response->data = $data;
            return $response;
        }
        return false;
    }

    /**
     * Удаление отношения по идентификатору между метаклассами из БД,
     * а также всех связанных дополнительных связей между метаатрибутами.
     * @return bool
     * @throws \Exception
     */
    public function actionDeleteRelation()
    {
        // Ajax-запрос
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            // Поиск метассылки (отношения между метаатрибутами)
            $metareference = Metareference::find()
                ->where(array('left_metaattribute' => Yii::$app->request->post('left_attribute_id'),
                    'right_metaattribute' => Yii::$app->request->post('right_attribute_id')))
                ->one();
            // Удаление данного метаотношения из БД
            Metarelation::findOne($metareference->metarelation)->delete();
        }
        return false;
    }

    /**
     * Finds the Metamodel model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Metamodel the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Metamodel::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'ERROR_MESSAGE_PAGE_NOT_FOUND'));
        }
    }
}