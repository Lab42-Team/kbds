<?php

/* @var $this yii\web\View */
/* @var $model app\modules\software_component\models\SoftwareComponent */
/* @var $tmrl_code app\modules\software_component\controllers\SoftwareComponentsController */

use yii\helpers\Html;
use yii\bootstrap\Alert;
use app\modules\software_component\models\SoftwareComponent;

$this->title = Yii::t('app', 'SOFTWARE_COMPONENTS_PAGE_VIEW_TMRL_CODE');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'SOFTWARE_COMPONENTS_PAGE_SOFTWARE_COMPONENTS'),
    'url' => ['list']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'SOFTWARE_COMPONENTS_PAGE_SOFTWARE_COMPONENT') . ' - ' .
    $model->name,
    'url' => ['/software-components/view/' . $model->id]];
$this->params['breadcrumbs'][] = $this->title;

$this->params['menu'] = [
    [
        'label' => Yii::t('app', 'SOFTWARE_COMPONENTS_PAGE_SOFTWARE_COMPONENTS'),
        'icon' => 'glyphicon glyphicon-list-alt',
        'url' => ['/software-components/list']
    ],
    [
        'label' => Yii::t('app', 'SOFTWARE_COMPONENTS_PAGE_CREATE_SOFTWARE_COMPONENT'),
        'icon' => 'glyphicon glyphicon-plus-sign',
        'url' => ['/software-components/create']
    ],
    [
        'label' => Yii::t('app', 'SOFTWARE_COMPONENTS_PAGE_VIEW_SOFTWARE_COMPONENT'),
        'icon' => 'glyphicon glyphicon-eye-open',
        'url' => ['/software-components/view/' . $model->id]
    ],
    [
        'label' => Yii::t('app', 'SOFTWARE_COMPONENTS_PAGE_UPDATE_SOFTWARE_COMPONENT'),
        'icon' => 'glyphicon glyphicon-pencil',
        'url' => ['/software-components/update/' . $model->id]
    ],
    [
        'label' => Yii::t('app', 'SOFTWARE_COMPONENTS_PAGE_DELETE_SOFTWARE_COMPONENT'),
        'icon' => 'glyphicon glyphicon-trash',
        'url' => '#',
        'options' => ['data-toggle'=>'modal', 'data-target'=>'#removeSoftwareComponentModalForm']
    ]
];

// Если программный компонент имеет статус сгенерированного или устаревшего,
// то в правом меню добавляется пункт просмотра TMRL-кода модели трансформации
if ($model->status == SoftwareComponent::STATUS_GENERATED || $model->status == SoftwareComponent::STATUS_OUTDATED)
    array_push($this->params['menu'],
        [
            'label' => Yii::t('app', 'SOFTWARE_COMPONENTS_PAGE_VIEW_TMRL_CODE'),
            'url' => '',
            'active' => true
        ]);
?>

<?= $this->render('_modal_form_software_components', ['model' => $model]) ?>

<div class="view-tmrl-code">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php if($model->status == SoftwareComponent::STATUS_GENERATED): ?>
        <?php echo Alert::widget([
            'options' => ['class' => 'alert-success'],
            'body' => Yii::t('app', 'SOFTWARE_COMPONENTS_PAGE_NOTICE_STATUS_GENERATED')
        ]); ?>
    <?php endif; ?>

    <?php if($model->status == SoftwareComponent::STATUS_OUTDATED): ?>
        <?php echo Alert::widget([
            'options' => ['class' => 'alert-warning'],
            'body' => '<b>' . Yii::t('app', 'NOTICE_TITLE') . '</b> ' .
                Yii::t('app', 'SOFTWARE_COMPONENTS_PAGE_NOTICE_STATUS_OUTDATED')
        ]); ?>
    <?php endif; ?>

    <div class="well" id="tmrl-code"><?php echo $tmrl_code; ?></div>

</div>