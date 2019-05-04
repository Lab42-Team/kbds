<?php

/* @var $this yii\web\View */
/* @var $model app\modules\user\models\User */

use yii\helpers\Html;

$this->title = Yii::t('app', 'USERS_PAGE_UPDATE_USER_PASSWORD');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'USERS_PAGE_USERS'), 'url' => ['list']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'USERS_PAGE_USER') . ' - ' . $model->username,
    'url' => ['/users/view/' . $model->id]];
$this->params['breadcrumbs'][] = $this->title;

$this->params['menu'] = [
    [
        'label' => Yii::t('app', 'USERS_PAGE_USERS'),
        'icon' => 'glyphicon glyphicon-list-alt',
        'url' => ['/users/list']
    ],
    [
        'label' => Yii::t('app', 'USERS_PAGE_CREATE_USER'),
        'icon' => 'glyphicon glyphicon-plus-sign',
        'url' => ['/users/create']
    ],
    [
        'label' => Yii::t('app', 'USERS_PAGE_VIEW_USER'),
        'icon' => 'glyphicon glyphicon-eye-open',
        'url' => ['/users/view/' . $model->id]
    ],
    [
        'label' => Yii::t('app', 'USERS_PAGE_UPDATE_USER'),
        'icon' => 'glyphicon glyphicon-pencil',
        'url' => ['/users/update/' . $model->id]
    ],
    [
        'label' => $this->title,
        'icon' => 'glyphicon glyphicon-edit',
        'url' => '',
        'active' => true
    ],
    [
        'label' => Yii::t('app', 'USERS_PAGE_DELETE_USER'),
        'icon' => 'glyphicon glyphicon-trash',
        'url' => '#',
        'options' => ['data-toggle'=>'modal', 'data-target'=>'#removeUserModalForm']
    ]
];
?>

<?= $this->render('_modal_form_users', ['model' => $model]) ?>

<div class="user-password-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form_password_update', [
        'model' => $model,
    ]) ?>

</div>
