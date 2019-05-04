<?php

/* @var $this yii\web\View */
/* @var $model app\modules\user\models\User */

use yii\helpers\Html;
use yii\widgets\DetailView;

$this->title = Yii::t('app', 'NAV_ACCOUNT') . ' - ' . $model->username;
$this->params['breadcrumbs'][] = $this->title;

$this->params['menu'] = [
    [
        'label' => Yii::t('app', 'USER_PAGE_ACCOUNT'),
        'icon' => 'glyphicon glyphicon-list-alt',
        'url' => '',
        'active' => true
    ],
    [
        'label' => Yii::t('app', 'USER_PAGE_UPDATE'),
        'icon' => 'glyphicon glyphicon-pencil',
        'url' => ['/user/update/']
    ],
    [
        'label' => Yii::t('app', 'USER_PAGE_UPDATE_PASSWORD'),
        'icon' => 'glyphicon glyphicon-edit',
        'url' => ['/user/update-password/']
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

<div class="account">

    <h1><?= Html::encode(Yii::t('app', 'USER_PAGE_ACCOUNT')) ?></h1>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'username',
            'first_name',
            'last_name',
            'middle_name',
            'email',
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
        ],
    ]) ?>

</div>
