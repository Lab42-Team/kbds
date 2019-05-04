<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\modules\main\models\ContactForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\captcha\Captcha;

$this->title = Yii::t('app', 'CONTACT_US_PAGE_TITLE');
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="main-contact-index">
    <h1><?= Html::encode($this->title) ?></h1>

    <?php if (Yii::$app->session->hasFlash('contactFormSubmitted')): ?>

        <div class="alert alert-success">
            <?= Yii::t('app', 'CONTACT_US_PAGE_SUCCESS_MESSAGE') ?>
        </div>

        <p>
            <?php if (Yii::$app->mailer->useFileTransport): ?>
                Because the application is in development mode, the email is not sent but saved as
                a file under <code><?= Yii::getAlias(Yii::$app->mailer->fileTransportPath) ?></code>.
                Please configure the <code>useFileTransport</code> property of the <code>mail</code>
                application component to be false to enable email sending.<br/>
                Note that if you turn on the Yii debugger, you should be able
                to view the mail message on the mail panel of the debugger.
            <?php endif; ?>
        </p>

    <?php else: ?>

        <p><?= Yii::t('app', 'CONTACT_US_PAGE_TEXT') ?></p>

        <div class="row">
            <div class="col-lg-5">

                <?php $form = ActiveForm::begin(['id' => 'contact-form']); ?>

                    <?= $form->field($model, 'name') ?>

                    <?= $form->field($model, 'email') ?>

                    <?= $form->field($model, 'subject') ?>

                    <?= $form->field($model, 'body')->textArea(['rows' => 6]) ?>

                    <?= $form->field($model, 'verifyCode')->widget(Captcha::className(), [
                        'captchaAction' => '/main/contact-us/captcha',
                        'template' => '<div class="row"><div class="col-lg-3">{image}</div><div class="col-lg-6">{input}</div></div>',
                    ]) ?>

                    <p><?= Yii::t('app', 'CAPTCHA_NOTICE_ONE') . '<br/>' . Yii::t('app', 'CAPTCHA_NOTICE_TWO') .
                        '<br/>' . Yii::t('app', 'CAPTCHA_NOTICE_THREE') ?></p>

                    <div class="form-group">
                        <?= Html::submitButton(Yii::t('app', 'BUTTON_SEND'), [
                            'class' => 'btn btn-primary',
                            'name' => 'contact-button'
                        ]) ?>
                    </div>

                <?php ActiveForm::end(); ?>

            </div>
        </div>

    <?php endif; ?>
</div>