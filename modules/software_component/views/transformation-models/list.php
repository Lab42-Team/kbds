<?php

/* @var $this yii\web\View */
/* @var $searchModel app\modules\software_component\models\TransformationModel */
/* @var $dataProvider yii\data\ActiveDataProvider */

use yii\helpers\Html;
use yii\grid\GridView;
use app\modules\software_component\models\SoftwareComponent;

$this->title = Yii::t('app', 'TRANSFORMATION_MODELS_PAGE_TRANSFORMATION_MODELS');
$this->params['breadcrumbs'][] = $this->title;

$this->params['menu'] = [
    [
        'label' => $this->title,
        'icon' => 'glyphicon glyphicon-list-alt',
        'url' => '',
        'active' => true
    ],
    [
        'label' => Yii::t('app', 'TRANSFORMATION_MODELS_PAGE_CREATE_TRANSFORMATION_MODEL'),
        'icon' => 'glyphicon glyphicon-plus-sign',
        'url' => ['/transformation-models/create']
    ]
];
?>

<div class="transformation-model-list">

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
                'attribute'=>'software_component',
                'format' => 'raw',
                'value' => function($data) {
                    return $data->softwareComponent->name;
                },
                'filter'=>SoftwareComponent::getAllSoftwareComponentsArray(),
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