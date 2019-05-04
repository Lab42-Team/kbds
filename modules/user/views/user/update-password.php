<?php

/* @var $this yii\web\View */
/* @var $model app\modules\user\models\User */

use yii\helpers\Html;

$this->title = Yii::t('app', 'USER_PAGE_UPDATE_PASSWORD');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'NAV_ACCOUNT') . ' - ' . $model->username,
    'url' => ['/user/account/']];
$this->params['breadcrumbs'][] = $this->title;

$this->params['menu'] = [
    [
        'label' => Yii::t('app', 'USER_PAGE_ACCOUNT'),
        'icon' => 'glyphicon glyphicon-list-alt',
        'url' => ['/user/account/']
    ],
    [
        'label' => Yii::t('app', 'USER_PAGE_UPDATE'),
        'icon' => 'glyphicon glyphicon-pencil',
        'url' => ['/user/update/']
    ],
    [
        'label' => $this->title,
        'icon' => 'glyphicon glyphicon-edit',
        'url' => '',
        'active' => true
    ],
    [
        'label' => Yii::t('app', 'USER_PAGE_DELETE'),
        'icon' => 'glyphicon glyphicon-trash',
        'url' => '#',
        'options' => ['data-toggle'=>'modal', 'data-target'=>'#removeYourselfModalForm']
    ]
];
?>

<?= $this->render('_modal_form_user', ['model' => $model]) ?>

<div class="update-your-password">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form_password_update', [
        'model' => $model,
    ]) ?>

</div>
