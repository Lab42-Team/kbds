<?php

/* @var $this yii\web\View */
/* @var $searchModel app\modules\software_component\models\SoftwareComponent */
/* @var $dataProvider yii\data\ActiveDataProvider */

use yii\helpers\Html;
use yii\grid\GridView;
use app\modules\user\models\User;
use app\modules\software_component\models\SoftwareComponent;

$this->title = Yii::t('app', 'SOFTWARE_COMPONENTS_PAGE_SOFTWARE_COMPONENTS');
$this->params['breadcrumbs'][] = $this->title;

$this->params['menu'] = [
    [
        'label' => $this->title,
        'icon' => 'glyphicon glyphicon-list-alt',
        'url' => '',
        'active' => true
    ],
    [
        'label' => Yii::t('app', 'SOFTWARE_COMPONENTS_PAGE_CREATE_SOFTWARE_COMPONENT'),
        'icon' => 'glyphicon glyphicon-plus-sign',
        'url' => ['/software-components/create']
    ]
];
?>

<div class="software-component-list">

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
            'name',
            [
                'attribute'=>'type',
                'format' => 'raw',
                'value' => function($data) {
                    return $data->getTypeName();
                },
                'filter'=>SoftwareComponent::getAllTypesArray(),
            ],
            [
                'attribute'=>'status',
                'format' => 'raw',
                'value' => function($data) {
                    return $data->getStatusName();
                },
                'filter'=>SoftwareComponent::getStatusesArray(),
            ],
            [
                'attribute'=>'author',
                'format' => 'raw',
                'value' => function($data) {
                    return $data->fkAuthor->username;
                },
                'filter'=>User::getAllUsersArray(),
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