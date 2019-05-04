<?php

namespace app\modules\project\controllers;

use Yii;
use yii\web\Response;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\bootstrap\ActiveForm;
use app\modules\knowledge_base\models\SubjectDomain;
use app\modules\knowledge_base\models\KnowledgeBase;
use app\modules\knowledge_base\models\KnowledgeBaseSearch;

/**
 * MyKnowledgeBasesController implements the CRUD actions for KnowledgeBase model.
 */
class MyKnowledgeBasesController extends Controller
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
     * Список авторских БЗ.
     * @return mixed
     */
    public function actionList()
    {
        $searchModel = new KnowledgeBaseSearch();
        // Выборка всех БЗ для авторизованного пользователя, если он является их автором
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, Yii::$app->user->identity->getId());

        return $this->render('list', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single KnowledgeBase model.
     * @param integer $id
     * @return string
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     *
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);

        // Проверка доступа
        if (!\Yii::$app->user->can('viewOwnKnowledgeBase', ['knowledge-base' => $model]))
            throw new ForbiddenHttpException(Yii::t('app', 'ERROR_MESSAGE_ACCESS_DENIED'));

        return $this->render('view', [
            'model' => $model,
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

        // Формирование модели предметной области
        $subject_domain_model = new SubjectDomain();

        if ($model->load(Yii::$app->request->post())) {
            $model->author = Yii::$app->user->identity->getId();
            if ($model->save()) {
                Yii::$app->getSession()->setFlash('success',
                    Yii::t('app', 'KNOWLEDGE_BASE_MODEL_MESSAGE_CREATE_KNOWLEDGE_BASE'));

                return $this->redirect(['view', 'id' => $model->id]);
            }
        } else {
            return $this->render('create', [
                'model' => $model,
                'subject_domain_model' => $subject_domain_model
            ]);
        }
    }

    /**
     * Updates an existing KnowledgeBase model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return string|\yii\web\Response
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        // Формирование модели предметной области
        $subject_domain_model = new SubjectDomain();

        // Проверка доступа
        if (!\Yii::$app->user->can('updateOwnKnowledgeBase', ['knowledge-base' => $model]))
            throw new ForbiddenHttpException(Yii::t('app', 'ERROR_MESSAGE_ACCESS_DENIED'));

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->getSession()->setFlash('success',
                Yii::t('app', 'KNOWLEDGE_BASE_MODEL_MESSAGE_UPDATED_KNOWLEDGE_BASE'));

            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
                'subject_domain_model' => $subject_domain_model
            ]);
        }
    }

    /**
     * Deletes an existing KnowledgeBase model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return \yii\web\Response
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws \Exception
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        // Проверка доступа
        if (!\Yii::$app->user->can('deleteOwnKnowledgeBase', ['knowledge-base' => $model]))
            throw new ForbiddenHttpException(Yii::t('app', 'ERROR_MESSAGE_ACCESS_DENIED'));

        // Удаление проекта БЗ из БД
        $model->delete();
        Yii::$app->getSession()->setFlash('success',
            Yii::t('app', 'KNOWLEDGE_BASE_MODEL_MESSAGE_DELETED_KNOWLEDGE_BASE'));

        return $this->redirect(['list']);
    }

    /**
     * Добавление новой предметной области.
     * @return bool|\yii\console\Response|Response
     */
    public function actionAddNewSubjectDomain()
    {
        // Ajax-запрос
        if (Yii::$app->request->isAjax) {
            // Определение массива возвращаемых данных
            $data = array();
            // Установка формата JSON для возвращаемых данных
            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;
            // Формирование модели предметной области
            $model = new SubjectDomain();
            // Определение полей модели предметной области
            $model->load(Yii::$app->request->post());
            // Валидация формы
            if ($model->validate()) {
                // Ошибки ввода отсутствуют
                $data["error_status"] = false;
                // Добавление новой предметной области в БД
                $model->author = Yii::$app->user->identity->getId();
                $model->save();
                // Формирование данных о новой предметной области
                $data["id"] = $model->id;
                $data["name"] = $model->name;
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