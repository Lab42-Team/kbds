<?php

/* @var $this yii\web\View */
/* @var $searchModel app\modules\software_component\models\Metamodel */
/* @var $dataProvider yii\data\ActiveDataProvider */

use yii\helpers\Html;
use yii\grid\GridView;
use app\modules\user\models\User;
use app\modules\software_component\models\Metamodel;

$this->title = Yii::t('app', 'METAMODELS_PAGE_METAMODELS');
$this->params['breadcrumbs'][] = $this->title;

$this->params['menu'] = [
    [
        'label' => $this->title,
        'icon' => 'glyphicon glyphicon-list-alt',
        'url' => '',
        'active' => true
    ],
    [
        'label' => Yii::t('app', 'METAMODELS_PAGE_CREATE_METAMODEL'),
        'icon' => 'glyphicon glyphicon-plus-sign',
        'url' => ['/metamodels/create']
    ]
];
?>

<div class="metamodel-list">

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
                'attribute' => 'created_at',
                'format' => ['date', 'dd.MM.Y HH:mm:ss']
            ],
            [
                'attribute'=>'type',
                'format' => 'raw',
                'value' => function($data) {
                    return $data->getTypeName();
                },
                'filter'=>Metamodel::getTypesArray(),
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
                'class' => 'yii\grid\ActionColumn',
                'headerOptions' => ['class' => 'action-column'],
                'template' => '{view} {update} {delete}',
            ],
        ],
    ]); ?>

</div>