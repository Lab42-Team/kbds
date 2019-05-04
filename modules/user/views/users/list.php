<?php

/* @var $this yii\web\View */
/* @var $searchModel app\modules\user\models\UserSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

use yii\helpers\Html;
use yii\grid\GridView;
use app\modules\user\models\User;

$this->title = Yii::t('app', 'USERS_PAGE_USERS');
$this->params['breadcrumbs'][] = $this->title;

$this->params['menu'] = [
    [
        'label' => $this->title,
        'icon' => 'glyphicon glyphicon-list-alt',
        'url' => '',
        'active' => true
    ],
    [
        'label' => Yii::t('app', 'USERS_PAGE_CREATE_USER'),
        'icon' => 'glyphicon glyphicon-plus-sign',
        'url' => ['/users/create']
    ]
];
?>

<div class="user-list">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php //echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            [
                'attribute' => 'id',
                'options' => ['width' => '60']
            ],
            'username',
            'email:email',
            [
                'attribute'=>'status',
                'format' => 'raw',
                'value' => function($data) {
                    return $data->getStatusName();
                },
                'filter'=>User::getStatusesArray(),
            ],
            [
                'attribute'=>'role',
                'format' => 'raw',
                'value' => function($data) {
                    return $data->getRoleName();
                },
                'filter'=>User::getRolesArray(),
            ],
            [
                'attribute' => 'created_at',
                'format' => ['date', 'dd.MM.Y HH:mm:ss']
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'headerOptions' => ['class' => 'action-column'],
                'template' => '{view} {update} {delete}',
            ],
        ],
    ]); ?>

</div>
