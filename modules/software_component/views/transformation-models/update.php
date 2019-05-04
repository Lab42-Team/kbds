<?php

/* @var $this yii\web\View */
/* @var $model app\modules\software_component\models\TransformationModel */

use yii\helpers\Html;

$this->title = Yii::t('app', 'TRANSFORMATION_MODELS_PAGE_UPDATE_TRANSFORMATION_MODEL');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'TRANSFORMATION_MODELS_PAGE_TRANSFORMATION_MODELS'),
    'url' => ['list']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'TRANSFORMATION_MODELS_PAGE_TRANSFORMATION_MODEL') . ' - ' . $model->name,
    'url' => ['/transformation-models/view/' . $model->id]];
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
        'url' => ['/transformation-models/view/' . $model->id]
    ],
    [
        'label' => $this->title,
        'icon' => 'glyphicon glyphicon-pencil',
        'url' => '',
        'active' => true
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

<div class="transformation-model-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>