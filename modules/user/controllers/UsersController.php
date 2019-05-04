<?php

namespace app\modules\user\controllers;

use Yii;
use app\modules\user\models\User;
use app\modules\user\models\UserSearch;
use yii\bootstrap\ActiveForm;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * UsersController implements the CRUD actions for User model.
 */
class UsersController extends Controller
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
     * Lists all User models.
     * @return mixed
     */
    public function actionList()
    {
        $searchModel = new UserSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('list', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single User model.
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
     * Creates a new User model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new User();
        $model->scenario = 'create_and_update_password_hash';

        if ($errors = $this->performAjaxValidation($model)) {
            return $errors;
        }

        if (isset($_POST['create-user-button']) && !Yii::$app->request->isAjax) {
            if ($model->load(Yii::$app->request->post())) {
                $model->setPassword($model->password);
                $model->generateAuthKey();
                if ($model->save()) {
                    // Назначение роли пользователю
                    $auth = Yii::$app->authManager;
                    $role = $auth->getRole($model->role);
                    $auth->assign($role, $model->id);
                    // Вывод сообщения
                    Yii::$app->getSession()->setFlash('success', Yii::t('app', 'USER_MODEL_MESSAGE_CREATE_USER'));

                    return $this->redirect(['view', 'id' => $model->id]);
                }
            }
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing User model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        // Получение текущей роли пользователя
        $auth = Yii::$app->authManager;
        $roles = $auth->getRolesByUser($model->id);
        $model->role = '';
        foreach($roles as $key => $value)
            $model->role = $key;
        $current_role = $auth->getRole($model->role);

        // Если авторизованный пользователь имеет права администратора, то тогда он не может изменить свой статус
        if(Yii::$app->user->identity->getId() == $id)
            $model->scenario = 'update_admin';

        if ($errors = $this->performAjaxValidation($model)) {
            return $errors;
        }

        if (Yii::$app->request->post('User')) {
            $model->attributes = Yii::$app->request->post('User');
            if ($model->update()) {
                // Удаление текущего назначения роли пользователю
                $auth->revoke($current_role, $model->id);
                // Новое назначение роли пользователю
                $role = $auth->getRole($model->role);
                $auth->assign($role, $model->id);
                // Вывод сообщения
                Yii::$app->getSession()->setFlash('success', Yii::t('app', 'USER_MODEL_MESSAGE_UPDATED_USER'));
            } else
                $errors = $model->errors;

            return $this->render('view', [
                'model' => $this->findModel($id),
            ]);
        } else {
            // Нахождение роли пользователя
            $roles = Yii::$app->authManager->getRolesByUser($model->id);
            $model->role = '';
            foreach($roles as $key => $value)
                $model->role = $key;
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing user password hash.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdatePassword($id)
    {
        $model = $this->findModel($id);
        $model->scenario = 'create_and_update_password_hash';

        if (Yii::$app->request->post('User')) {
            $model->attributes = Yii::$app->request->post('User');
            $model->setPassword($model->password);
            if ($model->update())
                Yii::$app->getSession()->setFlash('success', Yii::t('app', 'USER_MODEL_MESSAGE_UPDATED_USER_PASSWORD'));

            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update-password', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing User model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        // Если авторизованный пользователь имеет права администратора, то тогда он не может удалить себя
        if (Yii::$app->user->can('admin') && Yii::$app->user->getId() == $id) {
            Yii::$app->getSession()->setFlash('warning', Yii::t('app', 'USER_MODEL_MESSAGE_DELETE_YOURSELF'));
        } else {
            // Удаление пользователя
            $this->findModel($id)->delete();
            // Удаление назначения роли для удаленного пользователя
            $auth = Yii::$app->authManager;
            $roles = $auth->getRolesByUser($id);
            $current_role = '';
            foreach($roles as $key => $value)
                $current_role = $key;
            $role = $auth->getRole($current_role);
            $auth->revoke($role, $id);
            // Вывод сообщения
            Yii::$app->getSession()->setFlash('success', Yii::t('app', 'USER_MODEL_MESSAGE_DELETED_USER'));
        }

        return $this->redirect(['list']);
    }

    /**
     * Finds the User model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return User the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = User::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'ERROR_MESSAGE_PAGE_NOT_FOUND'));
        }
    }
}