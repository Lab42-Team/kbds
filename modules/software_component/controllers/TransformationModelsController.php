<?php

namespace app\modules\software_component\controllers;

use Yii;
use yii\web\Response;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\bootstrap\ActiveForm;
use app\modules\software_component\models\Metamodel;
use app\modules\software_component\models\Metaclass;
use app\modules\software_component\models\Metaattribute;
use app\modules\software_component\models\Metarelation;
use app\modules\software_component\models\Metareference;
use app\modules\software_component\models\TransformationModel;
use app\modules\software_component\models\TransformationRule;
use app\modules\software_component\models\TransformationBody;
use app\modules\software_component\models\TransformationModelSearch;
use app\modules\software_component\models\SoftwareComponent;
use app\modules\software_component\models\MetaclassVisibility;

/**
 * TransformationModelsController implements the CRUD actions for TransformationModel model.
 */
class TransformationModelsController extends Controller
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
     * Lists all TransformationModel models.
     * @return mixed
     */
    public function actionList()
    {
        $searchModel = new TransformationModelSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('list', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single TransformationModel model.
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
     * Creates a new TransformationModel model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new TransformationModel();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            // Поиск всех метаклассов принадлежащих данной исходной метамодели
            $source_metaclasses = Metaclass::find()->where(array('metamodel' => $model->source_metamodel))->all();
            // Поиск всех метаклассов принадлежащих данной целевой метамодели
            $target_metaclasses = Metaclass::find()->where(array('metamodel' => $model->target_metamodel))->all();
            // Обход по всем исходным метаклассам
            foreach ($source_metaclasses as $source_metaclass) {
                // Сохранение информации о видимости исходных метаклассов входящих в данную модель трансформации
                $metaclass_visibility = new MetaclassVisibility();
                $metaclass_visibility->transformation_model = $model->id;
                $metaclass_visibility->metaclass = $source_metaclass->id;
                $metaclass_visibility->visibility = true;
                $metaclass_visibility->save();
            }
            // Обход по всем целевым метаклассам
            foreach ($target_metaclasses as $target_metaclass) {
                // Сохранение информации о видимости целевых метаклассов входящих в данную модель трансформации
                $metaclass_visibility = new MetaclassVisibility();
                $metaclass_visibility->transformation_model = $model->id;
                $metaclass_visibility->metaclass = $target_metaclass->id;
                $metaclass_visibility->visibility = true;
                $metaclass_visibility->save();
            }
            // Вывод сообщения об успешном создании модели трансформации
            Yii::$app->getSession()->setFlash('success',
                Yii::t('app', 'TRANSFORMATION_MODEL_MODEL_MESSAGE_CREATE_TRANSFORMATION_MODEL'));

            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing TransformationModel model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            // Удаление всей старой информации о видимости метаклассов входящих в данную модель трансформации
            MetaclassVisibility::deleteAll(array('transformation_model' => $id));
            // Поиск всех метаклассов принадлежащих данной исходной метамодели
            $source_metaclasses = Metaclass::find()->where(array('metamodel' => $model->source_metamodel))->all();
            // Поиск всех метаклассов принадлежащих данной целевой метамодели
            $target_metaclasses = Metaclass::find()->where(array('metamodel' => $model->target_metamodel))->all();
            // Обход по всем исходным метаклассам
            foreach ($source_metaclasses as $source_metaclass) {
                // Сохранение информации о видимости исходных метаклассов входящих в данную модель трансформации
                $metaclass_visibility = new MetaclassVisibility();
                $metaclass_visibility->transformation_model = $model->id;
                $metaclass_visibility->metaclass = $source_metaclass->id;
                $metaclass_visibility->visibility = true;
                $metaclass_visibility->save();
            }
            // Обход по всем целевым метаклассам
            foreach ($target_metaclasses as $target_metaclass) {
                // Сохранение информации о видимости целевых метаклассов входящих в данную модель трансформации
                $metaclass_visibility = new MetaclassVisibility();
                $metaclass_visibility->transformation_model = $model->id;
                $metaclass_visibility->metaclass = $target_metaclass->id;
                $metaclass_visibility->visibility = true;
                $metaclass_visibility->save();
            }
            // Вывод сообщения об успешном изменении модели трансформации
            Yii::$app->getSession()->setFlash('success',
                Yii::t('app', 'TRANSFORMATION_MODEL_MODEL_MESSAGE_UPDATED_TRANSFORMATION_MODEL'));

            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing TransformationModel model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        Yii::$app->getSession()->setFlash('success',
            Yii::t('app', 'TRANSFORMATION_MODEL_MODEL_MESSAGE_DELETED_TRANSFORMATION_MODEL'));

        return $this->redirect(['list']);
    }

    /**
     * Отображение модели трансформации в графической форме.
     * @param integer $id
     * @return mixed
     */
    public function actionTransformationEditor($id)
    {
        // Деактивация правого меню
        $this->layout = "/main";
        // Получение модели трансформации по ее id
        $model = $this->findModel($id);
        // Поиск программного компонента связанного с данной моделью трансформации
        $software_component = SoftwareComponent::findOne($model->software_component);
        // Поиск всех метаклассов принадлежащие исходной метамодели
        $source_metaclasses = Metaclass::find()
            ->where(array('metamodel' => $model->source_metamodel))
            ->asArray()
            ->all();
        // Поиск всех метаклассов принадлежащие целевой метамодели
        $target_metaclasses = Metaclass::find()
            ->where(array('metamodel' => $model->target_metamodel))
            ->asArray()
            ->all();
        // Поиск всех атрибутов метаклассов
        $metaattributes = Metaattribute::find()->asArray()->all();
        // Поиск всех связей между метаклассами
        $metarelations = Metarelation::find()->asArray()->all();
        // Поиск правил трансформации для данной модели трансформации
        $transformation_rules = TransformationRule::find()
            ->where(array('transformation_model' => $id))
            ->asArray()
            ->all();
        // Поиск всех тел правил трансформации (контекста преобразования с соответствием метаатрибутов)
        $transformation_bodies = TransformationBody::find()->asArray()->all();
        // Формирование модели правила трансформации для модальных окон
        $transformation_rule_model = new TransformationRule();
        // Формирование модели тела правила трансформации для модальных окон
        $transformation_body_model = new TransformationBody();
        // Поиск всей информации о видимости метаклассов входящих в данную модель трансформации
        $visible_metaclasses = MetaclassVisibility::find()
            ->where(array('transformation_model' => $id))
            ->asArray()
            ->all();

        return $this->render('transformation-editor', [
            'model' => $model,
            'software_component' => $software_component,
            'source_metaclasses' => $source_metaclasses,
            'target_metaclasses' => $target_metaclasses,
            'metaattributes' => $metaattributes,
            'metarelations' => $metarelations,
            'transformation_rules' => $transformation_rules,
            'transformation_bodies' => $transformation_bodies,
            'transformation_rule_model' => $transformation_rule_model,
            'transformation_body_model' => $transformation_body_model,
            'visible_metaclasses' => $visible_metaclasses
        ]);
    }

    /**
     *
     * @return bool|\yii\console\Response|Response
     */
    function actionCheckClassVisibility()
    {
        // Ajax-запрос
        if (Yii::$app->request->isAjax) {
            // Определение массива возвращаемых данных
            $data = array();
            // Установка формата JSON для возвращаемых данных
            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;
            // Поиск метакласса принадлежащего данной модели трансформации
            $metaclass_visibility = MetaclassVisibility::find()
                ->where(array('transformation_model' => Yii::$app->request->post('transformation_model_id'),
                    'metaclass' => Yii::$app->request->post('metaclass_id')))
                ->one();
            // Изменение видимости метакласса
            if ($metaclass_visibility->visibility == MetaclassVisibility::VISIBLE)
                $metaclass_visibility->visibility = MetaclassVisibility::HIDDEN;
            else
                $metaclass_visibility->visibility = MetaclassVisibility::VISIBLE;
            // Сохранение информации о видимости данного метакласса
            $metaclass_visibility->save();
            // Формирование данных о видимости данного метакласса
            $data["visibility"] = $metaclass_visibility->visibility;
            // Возвращение данных
            $response->data = $data;
            return $response;
        }
        return false;
    }

    /**
     * Displays transformation model code TMRL for this software component.
     * @param integer $id
     * @return mixed
     */
    public function actionViewTmrlCode($id)
    {
        $tmrl_code = "";
        $model = $this->findModel($id);
        // Поиск программного компонента привязанного к данной модели трансформации
        $software_component = SoftwareComponent::findOne($model->software_component);
        // Если программный компонент сгенерирован
        if ($software_component->status == SoftwareComponent::STATUS_GENERATED ||
            $software_component->status == SoftwareComponent::STATUS_OUTDATED) {
            // Открытие TMRL-файла модели трансформации для чтения
            $file_handle = fopen($software_component->file_name, "r");
            // Пока не достигнут конец файла
            while (!feof($file_handle)) {
                // Получение строки из TMRL-файла модели трансформации
                $line = fgets($file_handle);
                // Поиск определенных нетерминалов
                $pos1 = strpos($line, '{');
                $pos2 = strpos($line, 'Elements [');
                $pos3 = strpos($line, ']');
                $pos4 = strpos($line, 'Relationships [');
                $pos5 = strpos($line, '[');
                $pos6 = strpos($line, '}');
                // Условия вывода полученных строк
                if ($pos1 || $pos6 !== false)
                    $tmrl_code .= $line . "<br />";
                if ($pos2 || $pos3 == 1 || $pos4 || ($pos2 === false && $pos5))
                    $tmrl_code .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . $line . "<br />";
                if ($pos1 == false && $pos2 == false && $pos3 != 1 && $pos6 === false && $pos5 == false)
                    $tmrl_code .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . $line . "<br />";
            }
        }

        return $this->render('view-tmrl-code', [
            'model' => $model,
            'software_component' => $software_component,
            'tmrl_code' => $tmrl_code
        ]);
    }

    /**
     * Добавление нового соответствия (отображения) между исходным и целевым метаклассом.
     * @return bool|\yii\console\Response|Response
     */
    public function actionAddClassConnection()
    {
        // Ajax-запрос
        if (Yii::$app->request->isAjax) {
            // Определение массива возвращаемых данных
            $data = array();
            // Установка формата JSON для возвращаемых данных
            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;
            // Формирование модели правила трансформации
            $model = new TransformationRule();
            // Определение полей модели правила трансформации
            $model->load(Yii::$app->request->post());
            // Валидация формы
            if ($model->validate()) {
                // Ошибки ввода отсутствуют
                $data["error_status"] = false;
                // Добавление нового правила трансформации в БД
                $model->save();
                // Поиск программного компонента связанного с данной моделью трансформации
                $software_component = SoftwareComponent::findOne(Yii::$app->request->post('software_component_id'));
                // Изменение статуса программного компонента, если его статус "сгенерированный"
                if ($software_component->status == SoftwareComponent::STATUS_GENERATED) {
                    $software_component->status = SoftwareComponent::STATUS_OUTDATED;
                    $software_component->save();
                }
                // Формирование данных о текущем статусе программного компонента
                $data["software_component_status"] = $software_component->status;
                // Формирование данных о новой связи элементов
                $data["source_id"] = 'source-class-' . $model->source_metaclass;
                $data["target_id"] = 'target-class-' . $model->target_metaclass;
                $data["priority"] = $model->priority;
            } else {
                // Ошибки ввода присутствуют
                $data["error_status"] = true;
                // Формирование данных ошибок ввода
                $data["errors"] = ActiveForm::validate($model);
            }
            // Возвращение данных
            $response->data = $data;
            return $response;
        }
        return false;
    }

    /**
     * Получение текущих значений соответствия (отображения/правила трансформации).
     * @return bool|\yii\console\Response|Response
     */
    function actionGetClassConnectionValues()
    {
        // Ajax-запрос
        if (Yii::$app->request->isAjax) {
            // Определение массива возвращаемых данных
            $data = array();
            // Установка формата JSON для возвращаемых данных
            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;
            // Нахождение правила трансформации по id модели трансформации, исходному и целевому метаклассу
            $transformation_rule = TransformationRule::find()
                ->where(array('transformation_model' => Yii::$app->request->post('transformation_model_id'),
                    'source_metaclass' => Yii::$app->request->post('source_class_id'),
                    'target_metaclass' => Yii::$app->request->post('target_class_id')))
                ->one();
            // Формирование данных о найденном правиле трансформации
            $data["transformation_rule_id"] = $transformation_rule->id;
            $data["source_metaclass"] = $transformation_rule->source_metaclass;
            $data["target_metaclass"] = $transformation_rule->target_metaclass;
            $data["priority"] = $transformation_rule->priority;
            // Возвращение данных
            $response->data = $data;
            return $response;
        }
        return false;
    }

    /**
     * Изменение соответствия (отображения/правила трансформации).
     * @return bool|\yii\console\Response|Response
     */
    public function actionEditClassConnection()
    {
        // Ajax-запрос
        if (Yii::$app->request->isAjax) {
            // Определение массива возвращаемых данных
            $data = array();
            // Установка формата JSON для возвращаемых данных
            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;
            // Поиск правила трансформации по id
            $model = TransformationRule::findOne(Yii::$app->request->post('transformation_rule_id'));
            // Переопределение полей модели правила трансформации
            $model->load(Yii::$app->request->post());
            // Валидация формы
            if ($model->validate()) {
                // Ошибки ввода отсутствуют
                $data["error_status"] = false;
                // Изменение правила трансформации в БД
                $model->save();
                // Поиск программного компонента связанного с данной моделью трансформации
                $software_component = SoftwareComponent::findOne(Yii::$app->request->post('software_component_id'));
                // Изменение статуса программного компонента, если его статус "сгенерированный"
                if ($software_component->status == SoftwareComponent::STATUS_GENERATED) {
                    $software_component->status = SoftwareComponent::STATUS_OUTDATED;
                    $software_component->save();
                }
                // Формирование данных о текущем статусе программного компонента
                $data["software_component_status"] = $software_component->status;
                // Формирование данных об измененной связи элементов
                $data["priority"] = $model->priority;
            } else {
                // Ошибки ввода присутствуют
                $data["error_status"] = true;
                // Формирование данных ошибок ввода
                $data["errors"] = ActiveForm::validate($model);
            }
            // Возвращение данных
            $response->data = $data;
            return $response;
        }
        return false;
    }

    /**
     * Удаление правила трансформации (соответствия/отображения) из БД,
     * а также всех связанных дополнительных правил соответствия метаатрибутов.
     * @return bool
     * @throws \Exception
     */
    public function actionDeleteClassConnection()
    {
        // Ajax-запрос
        if (Yii::$app->request->isAjax) {
            // Определение массива возвращаемых данных
            $data = array();
            // Установка формата JSON для возвращаемых данных
            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;
            // Поиск правила трансформации которое необходимо удалить
            $transformation_rule = TransformationRule::find()
                ->where(array('transformation_model' => Yii::$app->request->post('transformation_model_id'),
                    'source_metaclass' => Yii::$app->request->post('source_class_id'),
                    'target_metaclass' => Yii::$app->request->post('target_class_id')))
                ->one();
            // Поиск всех дополнительный связей между метаатрибутами для найденного правила трансформации
            $transformation_bodies = TransformationBody::find()
                ->where(array('transformation_rule' => $transformation_rule->id))
                ->asArray()
                ->all();
            // Поиск программного компонента связанного с данной моделью трансформации
            $software_component = SoftwareComponent::findOne(Yii::$app->request->post('software_component_id'));
            // Изменение статуса программного компонента, если его статус "сгенерированный"
            if ($software_component->status == SoftwareComponent::STATUS_GENERATED) {
                $software_component->status = SoftwareComponent::STATUS_OUTDATED;
                $software_component->save();
            }
            // Формирование данных о текущем статусе программного компонента
            $data["software_component_status"] = $software_component->status;
            // Формирование данных о связях между метаатрибутами,
            // которые необходимо удалить на форме редактора трансформации
            $data["attribute_id_array"] = $transformation_bodies;
            // Удаление правила трансформации из БД
            TransformationRule::find()
                ->where(array('transformation_model' => Yii::$app->request->post('transformation_model_id'),
                    'source_metaclass' => Yii::$app->request->post('source_class_id'),
                    'target_metaclass' => Yii::$app->request->post('target_class_id')))
                ->one()
                ->delete();
            // Возвращение данных
            $response->data = $data;
            return $response;
        }
        return false;
    }

    /**
     * Добавление нового соответствия (отображения) между исходным и целевым метаатрибутом.
     * @return bool|\yii\console\Response|Response
     */
    public function actionAddAttributeConnection()
    {
        // Ajax-запрос
        if (Yii::$app->request->isAjax) {
            // Определение массива возвращаемых данных
            $data = array();
            // Установка формата JSON для возвращаемых данных
            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;
            // Формирование модели тела правила трансформации
            $model = new TransformationBody();
            // Определение полей модели правила трансформации
            $model->load(Yii::$app->request->post());
            // Нахождение правила трансформации по id модели трансформации, исходному и целевому метаклассу
            $transformation_rule = TransformationRule::find()
                ->where(array('transformation_model' => Yii::$app->request->post('transformation_model_id'),
                    'source_metaclass' => Yii::$app->request->post('source_class_id'),
                    'target_metaclass' => Yii::$app->request->post('target_class_id')))
                ->one();
            // Присваивание id правила трансформации
            $model->transformation_rule = $transformation_rule->id;
            // Валидация формы
            if ($model->validate()) {
                // Ошибки ввода отсутствуют
                $data["error_status"] = false;
                // Добавление нового тела правила трансформации в БД
                $model->save();
                // Поиск программного компонента связанного с данной моделью трансформации
                $software_component = SoftwareComponent::findOne(Yii::$app->request->post('software_component_id'));
                // Изменение статуса программного компонента, если его статус "сгенерированный"
                if ($software_component->status == SoftwareComponent::STATUS_GENERATED) {
                    $software_component->status = SoftwareComponent::STATUS_OUTDATED;
                    $software_component->save();
                }
                // Формирование данных о текущем статусе программного компонента
                $data["software_component_status"] = $software_component->status;
                // Формирование данных о новой связи элементов
                $data["source_id"] = 'source-attribute-' . $model->source_metaattribute;
                $data["target_id"] = 'target-attribute-' . $model->target_metaattribute;
            } else {
                // Ошибки ввода присутствуют
                $data["error_status"] = true;
                // Формирование данных ошибок ввода
                $data["errors"] = ActiveForm::validate($model);
            }
            // Возвращение данных
            $response->data = $data;
            return $response;
        }
        return false;
    }

    /**
     * Удаление тела правила трансформации (правил соответствия атрибутов) из БД.
     * @return bool
     * @throws \Exception
     */
    public function actionDeleteAttributeConnection()
    {
        // Ajax-запрос
        if (Yii::$app->request->isAjax) {
            // Установка формата JSON для возвращаемых данных
            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;
            // Нахождение правила трансформации по id модели трансформации, исходному и целевому метаклассу
            $transformation_rule = TransformationRule::find()
                ->where(array('transformation_model' => Yii::$app->request->post('transformation_model_id'),
                    'source_metaclass' => Yii::$app->request->post('source_class_id'),
                    'target_metaclass' => Yii::$app->request->post('target_class_id')))
                ->one();
            // Удаление тела правила трансформации из БД
            TransformationBody::find()
                ->where(array('transformation_rule' => $transformation_rule->id,
                    'source_metaattribute' => Yii::$app->request->post('source_attribute_id'),
                    'target_metaattribute' => Yii::$app->request->post('target_attribute_id')))
                ->one()
                ->delete();
            // Поиск программного компонента связанного с данной моделью трансформации
            $software_component = SoftwareComponent::findOne(Yii::$app->request->post('software_component_id'));
            // Изменение статуса программного компонента, если его статус "сгенерированный"
            if ($software_component->status == SoftwareComponent::STATUS_GENERATED) {
                $software_component->status = SoftwareComponent::STATUS_OUTDATED;
                $software_component->save();
            }
            // Формирование данных о текущем статусе программного компонента
            $data["software_component_status"] = $software_component->status;
            // Возвращение данных
            $response->data = $data;
            return $response;
        }
        return false;
    }

    /**
     * Update status of software component - generated.
     * If deletion is successful, the browser will be redirected to the 'software-components/view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionGenerateSoftwareComponent($id)
    {
        // Нахождение модели трансформации по идентификатору
        $model = $this->findModel($id);
        // Поиск исходной метамодели
        $source_metamodel = Metamodel::findOne($model->source_metamodel);
        // Поиск всех метаклассов принадлежащих данной исходной метамодели
        $source_metaclasses = Metaclass::find()->where(array('metamodel'=>$source_metamodel->id))->all();
        // Поиск всех связей между метаклассами принадлежащих данной исходной метамодели
        $source_metarelations = Metarelation::find()->where(array('metamodel'=>$source_metamodel->id))->all();
        // Поиск целевой метамодели
        $target_metamodel = Metamodel::findOne($model->target_metamodel);
        // Поиск всех метаклассов принадлежащих данной целевой метамодели
        $target_metaclasses = Metaclass::find()->where(array('metamodel'=>$target_metamodel->id))->all();
        // Поиск всех связей между метаклассами принадлежащих данной целевой метамодели
        $target_metarelations = Metarelation::find()->where(array('metamodel'=>$target_metamodel->id))->all();
        // Выборка всех метаатрибутов
        $metaattributes = Metaattribute::find()->all();
        // Выборка всех отношений между метаатрибутами (связи по идентификаторам)
        $metareferences = Metareference::find()->all();
        // Поиск всех правил трансформации входящих в данную модель трансформации (сортировка по приоритету правила)
        $transformation_rules = TransformationRule::find()
            ->where(array('transformation_model' => $id))
            ->orderBy(['priority' => SORT_ASC])
            ->all();
        // Выборка всех тел правил трансформации
        $transformation_bodies = TransformationBody::find()->all();

        // Формирование содержимого TMRL-файла модели трансформации
        // Формирование исходной метамодели
        $content = "Source Meta-Model " . $source_metamodel->name . " {\r\n";
        $content .= "\tElements [\r\n";
        foreach ($source_metaclasses as $source_metaclass) {
            $content .= "\t\t" . $source_metaclass->name;
            $number = 1;
            foreach ($metaattributes as $metaattribute)
                if ($metaattribute->metaclass == $source_metaclass->id) {
                    if ($number == 1)
                        $content .= " attributes (" . $metaattribute->name;
                    else
                        $content .= ", " . $metaattribute->name;
                    $number++;
                }
            if ($number > 1)
                $content .= ")";
            $content .= ",\r\n";
        }
        $content = substr($content, 0, -3);
        $content .= "\r\n\t]\r\n";
        $content .= "\tRelationships [\r\n";
        foreach ($source_metarelations as $source_metarelation) {
            if ($source_metarelation->type == Metarelation::ASSOCIATION) {
                foreach ($source_metaclasses as $source_metaclass)
                    if ($source_metaclass->id == $source_metarelation->left_metaclass)
                        $content .= "\t\t" . $source_metaclass->name . " is associated with ";
                foreach ($source_metaclasses as $source_metaclass)
                    if ($source_metaclass->id == $source_metarelation->right_metaclass)
                        $content .= $source_metaclass->name . ",\r\n";
            }
            if ($source_metarelation->type == Metarelation::REFERENCE) {
                foreach ($source_metaclasses as $source_metaclass)
                    if ($source_metaclass->id == $source_metarelation->left_metaclass)
                        foreach ($metareferences as $metareference)
                            if ($metareference->metarelation == $source_metarelation->id)
                                foreach ($metaattributes as $metaattribute)
                                    if ($metaattribute->id == $metareference->left_metaattribute)
                                        $content .= "\t\t" . $source_metaclass->name . " (" .
                                            $metaattribute->name . ") is associated with ";
                foreach ($source_metaclasses as $source_metaclass)
                    if ($source_metaclass->id == $source_metarelation->right_metaclass)
                        foreach ($metareferences as $metareference)
                            if ($metareference->metarelation == $source_metarelation->id)
                                foreach ($metaattributes as $metaattribute)
                                    if ($metaattribute->id == $metareference->right_metaattribute)
                                        $content .= $source_metaclass->name . " (" . $metaattribute->name . "),\r\n";
            }
        }
        $content = substr($content, 0, -3);
        $content .= "\r\n\t]\r\n}\r\n";
        // Формирование целевой метамодели
        $content .= "Target Meta-Model " . $target_metamodel->name . " {\r\n";
        $content .= "\tElements [\r\n";
        foreach ($target_metaclasses as $target_metaclass) {
            $content .= "\t\t" . $target_metaclass->name;
            $number = 1;
            foreach ($metaattributes as $metaattribute)
                if ($metaattribute->metaclass == $target_metaclass->id) {
                    if ($number == 1)
                        $content .= " attributes (" . $metaattribute->name;
                    else
                        $content .= ", " . $metaattribute->name;
                    $number++;
                }
            if ($number > 1)
                $content .= ")";
            $content .= ",\r\n";
        }
        $content = substr($content, 0, -3);
        $content .= "\r\n\t]\r\n";
        $content .= "\tRelationships [\r\n";
        foreach ($target_metarelations as $target_metarelation) {
            foreach ($target_metaclasses as $target_metaclass)
                if ($target_metaclass->id == $target_metarelation->left_metaclass)
                    $content .= "\t\t" . $target_metaclass->name . " is associated with ";
            foreach ($target_metaclasses as $target_metaclass)
                if ($target_metaclass->id == $target_metarelation->right_metaclass)
                    $content .= $target_metaclass->name . ",\r\n";
        }
        $content = substr($content, 0, -3);
        $content .= "\r\n\t]\r\n}\r\n";
        // Формирование блока оператора преобразования (набора правил трансформации)
        $content .= "Transformation " . $source_metamodel->name . " to " . $target_metamodel->name . " {\r\n";
        // Формирование первичных массивов по правилам трансформации и их тел
        $transformation_rule_array = array();
        $transformation_body_array = array();
        foreach ($transformation_rules as $transformation_rule) {
            $source_metaclass_name = '';
            $target_metaclass_name = '';
            foreach ($source_metaclasses as $source_metaclass)
                if ($source_metaclass->id == $transformation_rule->source_metaclass)
                    $source_metaclass_name = $source_metaclass->name;
            foreach ($target_metaclasses as $target_metaclass)
                if ($target_metaclass->id == $transformation_rule->target_metaclass)
                    $target_metaclass_name = $target_metaclass->name;
            $transformation_rule_array[$transformation_rule->id] = [$source_metaclass_name, $target_metaclass_name,
                $transformation_rule->priority];
            $source_metaattribute_name = '';
            $target_metaattribute_name = '';
            $list = array();
            foreach ($transformation_bodies as $transformation_body)
                if ($transformation_body->transformation_rule == $transformation_rule->id) {
                    foreach ($metaattributes as $metaattribute) {
                        if ($metaattribute->id == $transformation_body->source_metaattribute)
                            $source_metaattribute_name = $metaattribute->name;
                        if ($metaattribute->id == $transformation_body->target_metaattribute)
                            $target_metaattribute_name = $metaattribute->name;
                    }
                    array_push($list, [$source_metaclass_name . "(" . $source_metaattribute_name . ")",
                        $target_metaclass_name . "(" . $target_metaattribute_name . ")"]);
                }
            if (!empty($list))
                $transformation_body_array[$transformation_rule->id] = [$transformation_rule->priority, $list];
            else
                $transformation_body_array[$transformation_rule->id] = [$transformation_rule->priority,
                    [[$source_metaclass_name, $target_metaclass_name . "(name)"]]];
        }
        // Слияние значений первичного массива правил трансформации, если приоритеты правил совпадают
        $merged_transformation_rule_array = array();
        $previous_priority = null;
        $current_id = 0;
        foreach ($transformation_rule_array as $transformation_rule_id => $transformation_rule) {
            if ($transformation_rule[2] != $previous_priority) {
                $merged_transformation_rule_array[$transformation_rule_id] = [[$transformation_rule[0]],
                    $transformation_rule[1], $transformation_rule[2]];
                $current_id = $transformation_rule_id;
            }
            else
                array_push($merged_transformation_rule_array[$current_id][0], $transformation_rule[0]);
            $previous_priority = $transformation_rule[2];
        }
        // Слияние значений первичного массива тел правил трансформации, если приоритеты правил совпадают
        $merged_transformation_body_array = array();
        $previous_priority = null;
        $current_id = 0;
        foreach ($transformation_body_array as $transformation_rule_id => $transformation_body) {
            if ($transformation_body[0] != $previous_priority) {
                $merged_transformation_body_array[$transformation_rule_id] = $transformation_body[1];
                $current_id = $transformation_rule_id;
            }
            else {
                foreach ($transformation_body[1] as $item)
                    array_push($merged_transformation_body_array[$current_id], $item);
            }
            $previous_priority = $transformation_body[0];
        }
        // Формирование заголовка правил птрансформации
        foreach ($merged_transformation_rule_array as $transformation_rule_id => $merged_transformation_rule) {
            $content .= "\tRule ";
            if (count($merged_transformation_rule[0]) > 1) {
                $content .= "(";
                foreach ($merged_transformation_rule[0] as $source_metaclass)
                    $content .= $source_metaclass . ", ";
                $content = substr($content, 0, -2);
                $content .= ")";
            }
            else
                $content .= $merged_transformation_rule[0][0];
            $content .= " to " . $merged_transformation_rule[1] . " priority " .
                $merged_transformation_rule[2] . " [\r\n";
            // Формирование тел правил птрансформации
            foreach ($merged_transformation_body_array as $transformation_body_id => $merged_transformation_body)
                if ($transformation_body_id == $transformation_rule_id) {
                    $result_array = array();
                    foreach ($merged_transformation_body as $item) {
                        $exist_target_metaclass = false;
                        foreach ($result_array as &$obj)
                            if ($obj[0] == $item[1]) {
                                $obj[1] .= " or " . $item[0];
                                $exist_target_metaclass = true;
                            }
                        if ($exist_target_metaclass == false)
                            array_push($result_array, [$item[1], $item[0]]);
                    }
                    foreach ($result_array as $value)
                        $content .= "\t\t" . $value[0] . " is " . $value[1] . "\r\n";
                }
            $content .= "\t]\r\n";
        }
        $content .= "}\r\n";

        // Определение директории файла
        $dir = Yii::$app->basePath . '/web/tmrl-files/' . $id . '/';
        // Определение наименования файла
        $file = $dir . 'transformation-model.tmrl';
        // Создание данной директории, если он не существует
        if (!file_exists($dir))
            mkdir($dir, 0700);
        // Создание данного файла для записи (если файл с таким именем уже есть вся информация в нем уничтожается)
        fopen($file, 'w');
        // Запись текста в данный файл
        file_put_contents($file, $content);

        // Нахождение программного компонента связанного с данной моделью трансформации
        $software_component = SoftwareComponent::findOne($model->software_component);
        // Изменение статуса программного компонента на "сгенерированный"
        $software_component->status = SoftwareComponent::STATUS_GENERATED;
        // Сохранение пути к TMRL-файлу модели трансформации
        $software_component->file_name = $file;
        $software_component->save();
        // Вывод сообщения об успешной генерации программного компонента
        Yii::$app->getSession()->setFlash('success',
            Yii::t('app', 'TRANSFORMATION_EDITOR_PAGE_MESSAGE_GENERATE_SOFTWARE_COMPONENT'));

        return $this->render('/software-components/view', [
            'model' => $software_component,
        ]);
    }

    /**
     * Finds the TransformationModel model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return TransformationModel the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = TransformationModel::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'ERROR_MESSAGE_PAGE_NOT_FOUND'));
        }
    }
}