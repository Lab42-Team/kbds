<?php

/* @var $this yii\web\View */
/* @var $model app\modules\user\models\User */

use yii\helpers\Html;
use yii\widgets\DetailView;

$this->title = Yii::t('app', 'USERS_PAGE_USER') . ' - ' . $model->username;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'USERS_PAGE_USERS'), 'url' => ['list']];
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
        'url' => '',
        'active' => true
    ],
    [
        'label' => Yii::t('app', 'USERS_PAGE_UPDATE_USER'),
        'icon' => 'glyphicon glyphicon-pencil',
        'url' => ['/users/update/' . $model->id]
    ],
    [
        'label' => Yii::t('app', 'USERS_PAGE_UPDATE_USER_PASSWORD'),
        'icon' => 'glyphicon glyphicon-edit',
        'url' => ['/users/update-password/' . $model->id]
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

<div class="user-view">

    <h1><?= Html::encode(Yii::t('app', 'USERS_PAGE_VIEW_USER')) . ': ' . $model->username ?></h1>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'username',
            'first_name',
            'last_name',
            'middle_name',
            'email:email',
            [
                'attribute'=>'status',
                'format'=>'raw',
                'value' => $model->getStatusName()
            ],
            [
                'attribute'=>'role',
                'value' => $model->getRoleName()
            ],
            [
                'attribute' => 'created_at',
                'format' => ['date', 'dd.MM.Y HH:mm:ss']
            ],
            [
                'attribute' => 'updated_at',
                'format' => ['date', 'dd.MM.Y HH:mm:ss']
            ],
            'auth_key',
            'email_confirm_token:email',
            'password_hash',
            'password_reset_token',
        ],
    ]) ?>

</div>
