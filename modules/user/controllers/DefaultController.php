<?php

namespace app\modules\user\controllers;

use app\modules\user\models\EmailConfirmForm;
use app\modules\user\models\LoginForm;
use app\modules\user\models\PasswordResetRequestForm;
use app\modules\user\models\ResetPasswordForm;
use app\modules\user\models\SignUpForm;
use yii\base\InvalidParamException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use Yii;

class DefaultController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    public function actions()
    {
        return [
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Sing in form.
     * @return string|\yii\web\Response
     */
    public function actionSingIn()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        } else {
            return $this->render('sing-in', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Sing out.
     * @return \yii\web\Response
     */
    public function actionSingOut()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Sing up form.
     * @return string|\yii\web\Response
     */
    public function actionSignUp()
    {
        $model = new SignUpForm();
        if ($model->load(Yii::$app->request->post())) {
            if ($user = $model->signup()) {
                Yii::$app->getSession()->setFlash('success', Yii::t('app', 'SIGN_UP_FORM_MESSAGE_EMAIL_CONFIRM'));
                return $this->goHome();
            }
        }

        return $this->render('sign-up', [
            'model' => $model,
        ]);
    }

    /**
     * @param $token
     * @return \yii\web\Response
     * @throws BadRequestHttpException
     */
    public function actionEmailConfirm($token)
    {
        try {
            $model = new EmailConfirmForm($token);
        } catch (InvalidParamException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        if ($model->confirmEmail()) {
            Yii::$app->getSession()->setFlash('success', Yii::t('app', 'SIGN_UP_FORM_MESSAGE_EMAIL_SUCCESS'));
        } else {
            Yii::$app->getSession()->setFlash('error', Yii::t('app', 'SIGN_UP_FORM_MESSAGE_EMAIL_FAIL'));
        }

        return $this->goHome();
    }

    /**
     * @return string|\yii\web\Response
     */
    public function actionPasswordResetRequest()
    {
        $model = new PasswordResetRequestForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail()) {
                Yii::$app->getSession()->setFlash('success',
                    Yii::t('app', 'SIGN_UP_FORM_MESSAGE_PASSWORD_RESET_CONFIRM'));

                return $this->goHome();
            } else {
                Yii::$app->getSession()->setFlash('error',
                    Yii::t('app', 'SIGN_UP_FORM_MESSAGE_UPDATE_PASSWORD_FAIL'));
            }
        }

        return $this->render('passwordResetRequest', [
            'model' => $model,
        ]);
    }

    /**
     * @param $token
     * @return string|\yii\web\Response
     * @throws BadRequestHttpException
     */
    public function actionPasswordReset($token)
    {
        try {
            $model = new ResetPasswordForm($token);
        } catch (InvalidParamException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->resetPassword()) {
            Yii::$app->getSession()->setFlash('success', Yii::t('app',
                'SIGN_UP_FORM_MESSAGE_UPDATE_PASSWORD_SUCCESS'));

            return $this->goHome();
        }

        return $this->render('passwordReset', [
            'model' => $model,
        ]);
    }
}