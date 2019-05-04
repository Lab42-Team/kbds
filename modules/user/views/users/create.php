<?php

/* @var $this yii\web\View */
/* @var $model app\modules\user\models\User */

use yii\helpers\Html;

$this->title = Yii::t('app', 'USERS_PAGE_CREATE_USER');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'USERS_PAGE_USERS'), 'url' => ['/users/list']];
$this->params['breadcrumbs'][] = $this->title;

$this->params['menu'] = [
    [
        'label' => Yii::t('app', 'USERS_PAGE_USERS'),
        'icon' => 'glyphicon glyphicon-list-alt',
        'url' => ['/users/list']
    ],
    [
        'label' => $this->title,
        'icon' => 'glyphicon glyphicon-plus-sign',
        'url' => '',
        'active' => true
    ]
];
?>

<div class="user-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form_create', [
        'model' => $model,
    ]) ?>

</div>
