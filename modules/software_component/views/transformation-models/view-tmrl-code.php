<?php

/* @var $this yii\web\View */
/* @var $model app\modules\software_component\models\TransformationModel */
/* @var $software_component app\modules\software_component\models\SoftwareComponent */
/* @var $tmrl_code app\modules\software_component\controllers\TransformationModelsController */

use yii\helpers\Html;
use yii\bootstrap\Alert;
use app\modules\software_component\models\SoftwareComponent;

$this->title = Yii::t('app', 'TRANSFORMATION_MODELS_PAGE_VIEW_TMRL_CODE');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'TRANSFORMATION_MODELS_PAGE_TRANSFORMATION_MODELS'),
    'url' => ['list']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'TRANSFORMATION_MODELS_PAGE_TRANSFORMATION_MODEL') . ' - ' .
    $model->name,
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
        'url' => '',
        'active' => true
    ]
];
?>

<?= $this->render('_modal_form_transformation_models', ['model' => $model]) ?>

<div class="transformation-model-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php if($software_component->status == SoftwareComponent::STATUS_GENERATED): ?>
        <?php echo Alert::widget([
            'options' => ['class' => 'alert-success'],
            'body' => Yii::t('app', 'TRANSFORMATION_MODELS_PAGE_NOTICE_STATUS_GENERATED')
        ]); ?>
    <?php endif; ?>

    <?php if($software_component->status == SoftwareComponent::STATUS_OUTDATED): ?>
        <?php echo Alert::widget([
            'options' => ['class' => 'alert-warning'],
            'body' => '<b>' . Yii::t('app', 'NOTICE_TITLE') . '</b> ' .
                Yii::t('app', 'TRANSFORMATION_MODELS_PAGE_NOTICE_STATUS_OUTDATED')
        ]); ?>
    <?php endif; ?>

    <?php if($software_component->status == SoftwareComponent::STATUS_DESIGN): ?>
        <?php echo Alert::widget([
            'options' => ['class' => 'alert-warning'],
            'body' => '<b>' . Yii::t('app', 'NOTICE_TITLE') . '</b> ' . Yii::t('app', 'NOTICE_TEXT')
        ]); ?>
        <div class="well"><?= Yii::t('app', 'TRANSFORMATION_MODELS_PAGE_NOTICE_STATUS_DESIGN') ?></div>
    <?php endif; ?>

    <?php if($software_component->status == SoftwareComponent::STATUS_GENERATED ||
        $software_component->status == SoftwareComponent::STATUS_OUTDATED): ?>
        <div class="well" id="tmrl-code"><?php echo $tmrl_code; ?></div>
    <?php endif; ?>

</div>