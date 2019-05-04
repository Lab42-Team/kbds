<?php

namespace app\modules\user\controllers;

use Yii;
use app\modules\user\models\User;
use yii\web\Response;
use yii\web\Controller;
use yii\bootstrap\ActiveForm;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

/**
 * UserController implements the CRUD actions for User model.
 */
class UserController extends Controller
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
     * Displays a single User model.
     * @return mixed
     */
    public function actionAccount()
    {
        return $this->render('account', [
            'model' => $this->findModel(Yii::$app->user->identity->getId()),
        ]);
    }

    /**
     * Updates an existing User model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionUpdate()
    {
        $id = Yii::$app->user->identity->getId();
        $model = $this->findModel($id);

        if ($errors = $this->performAjaxValidation($model)) {
            return $errors;
        }

        if (Yii::$app->request->post('User')) {
            $model->attributes = Yii::$app->request->post('User');
            if ($model->update())
                Yii::$app->getSession()->setFlash('success', Yii::t('app', 'USER_MODEL_MESSAGE_UPDATED_YOUR_DETAILS'));
            else
                $errors = $model->errors;

            return $this->render('account', [
                'model' => $this->findModel($id),
            ]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing user password hash.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionUpdatePassword()
    {
        $id = Yii::$app->user->identity->getId();
        $model = $this->findModel($id);
        $model->scenario = 'create_and_update_password_hash';

        if (Yii::$app->request->post('User')) {
            $model->attributes = Yii::$app->request->post('User');
            $model->setPassword($model->password);
            if ($model->update())
                Yii::$app->getSession()->setFlash('success', Yii::t('app', 'USER_MODEL_MESSAGE_UPDATED_YOUR_PASSWORD'));

            return $this->render('account', [
                'model' => $this->findModel($id),
            ]);
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
        if (Yii::$app->user->can('admin')) {
            Yii::$app->getSession()->setFlash('warning', Yii::t('app', 'USER_MODEL_MESSAGE_DELETE_YOURSELF'));

            return $this->render('account', [
                'model' => $this->findModel($id),
            ]);
        } else {
            $this->findModel($id)->delete();
            Yii::$app->getSession()->setFlash('success', Yii::t('app', 'USER_MODEL_MESSAGE_DELETED_YOURSELF'));
        }

        return $this->redirect(['/main/default/index']);
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