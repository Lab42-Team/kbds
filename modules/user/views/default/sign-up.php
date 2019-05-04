<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\modules\user\models\SignUpForm */

use yii\captcha\Captcha;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = Yii::t('app', 'SIGN_UP_PAGE_TITLE');
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="user-default-sign-up">
    <h1><?= Html::encode($this->title) ?></h1>

    <p><?= Yii::t('app', 'SIGN_UP_PAGE_TEXT') ?></p>

    <div class="row">
        <div class="col-lg-5">

            <?php $form = ActiveForm::begin(['id' => 'form-sign-up']); ?>

                <?= $form->field($model, 'username') ?>

                <?= $form->field($model, 'email') ?>

                <?= $form->field($model, 'password')->passwordInput() ?>

                <?= $form->field($model, 'verifyCode')->widget(Captcha::className(), [
                    'captchaAction' => '/user/default/captcha',
                    'template' => '<div class="row"><div class="col-lg-3">{image}</div>
                        <div class="col-lg-6">{input}</div></div>',
                ]) ?>

                <p><?= Yii::t('app', 'CAPTCHA_NOTICE_ONE') . '<br/>' . Yii::t('app', 'CAPTCHA_NOTICE_TWO') .
                    '<br/>' . Yii::t('app', 'CAPTCHA_NOTICE_THREE') ?></p>

                <div class="form-group">
                    <?= Html::submitButton(Yii::t('app', 'BUTTON_SIGN_UP'),
                        ['class' => 'btn btn-primary', 'name' => 'sign-up-button']) ?>
                </div>

            <?php ActiveForm::end(); ?>

        </div>
    </div>
</div>
