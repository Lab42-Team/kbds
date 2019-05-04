<?php

/* @var $this yii\web\View */
/* @var $model app\modules\software_component\models\TransformationModel */

use yii\helpers\Html;
use yii\widgets\DetailView;

$this->title = Yii::t('app', 'TRANSFORMATION_MODELS_PAGE_TRANSFORMATION_MODEL') . ' - ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'TRANSFORMATION_MODELS_PAGE_TRANSFORMATION_MODELS'),
    'url' => ['list']];
$this->params['breadcrumbs'][] = $this->title;

$this->params['menu'] = [
    [
        'label' => Yii::t('app', 'TRANSFORMATION_MODELS_PAGE_TRANSFORMATION_MODELS'),
        'icon' => 'glyphicon glyphicon-list-alt',
        'url' => ['/transformation-models/list']
    ],
    [
        'label' => Yii::t('app', 'TRANSFORMATION_MODELS_PAGE_CREATE_TRANSFORMATION_MODEL'),
        'icon' => 'glyphicon glyphicon-plus-sign',
        'url' => ['/transformation-models/create']
    ],
    [
        'label' => Yii::t('app', 'TRANSFORMATION_MODELS_PAGE_VIEW_TRANSFORMATION_MODEL'),
        'icon' => 'glyphicon glyphicon-eye-open',
        'url' => '',
        'active' => true
    ],
    [
        'label' => Yii::t('app', 'TRANSFORMATION_MODELS_PAGE_UPDATE_TRANSFORMATION_MODEL'),
        'icon' => 'glyphicon glyphicon-pencil',
        'url' => ['/transformation-models/update/' . $model->id]
    ],
    [
        'label' => Yii::t('app', 'TRANSFORMATION_MODELS_PAGE_DELETE_TRANSFORMATION_MODEL'),
        'icon' => 'glyphicon glyphicon-trash',
        'url' => '#',
        'options' => ['data-toggle'=>'modal', 'data-target'=>'#removeTransformationModelModalForm']
    ],
    [
        'label' => Yii::t('app', 'TRANSFORMATION_MODELS_PAGE_TRANSFORMATION_EDITOR'),
        'url' => ['/transformation-models/transformation-editor/' . $model->id]
    ],
    [
        'label' => Yii::t('app', 'TRANSFORMATION_MODELS_PAGE_VIEW_TMRL_CODE'),
        'url' => ['/transformation-models/view-tmrl-code/' . $model->id]
    ]
];
?>

<?= $this->render('_modal_form_transformation_models', ['model' => $model]) ?>

<div class="transformation-model-view">

    <h1>
        <?= Html::encode(Yii::t('app', 'TRANSFORMATION_MODELS_PAGE_VIEW_TRANSFORMATION_MODEL')) . ': ' .
            $model->name ?>
    </h1>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'name',
            [
                'attribute'=>'software_component',
                'value' => $model->softwareComponent->name,
            ],
            [
                'attribute'=>'source_metamodel',
                'value' => $model->sourceMetamodel->name,
            ],
            [
                'attribute'=>'target_metamodel',
                'value' => $model->targetMetamodel->name,
            ],
            [
                'attribute' => 'created_at',
                'format' => ['date', 'dd.MM.Y HH:mm:ss']
            ],
            [
                'attribute' => 'updated_at',
                'format' => ['date', 'dd.MM.Y HH:mm:ss']
            ],
            'description',
        ],
    ]) ?>

</div>