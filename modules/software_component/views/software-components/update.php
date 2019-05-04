<?php

/* @var $this yii\web\View */
/* @var $model app\modules\software_component\models\SoftwareComponent */

use yii\helpers\Html;
use app\modules\software_component\models\SoftwareComponent;

$this->title = Yii::t('app', 'SOFTWARE_COMPONENTS_PAGE_UPDATE_SOFTWARE_COMPONENT');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'SOFTWARE_COMPONENTS_PAGE_SOFTWARE_COMPONENTS'),
    'url' => ['list']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'SOFTWARE_COMPONENTS_PAGE_SOFTWARE_COMPONENT') . ' - ' . $model->name,
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
        'label' => $this->title,
        'icon' => 'glyphicon glyphicon-pencil',
        'url' => '',
        'active' => true
    ],
    [
        'label' => Yii::t('app', 'SOFTWARE_COMPONENTS_PAGE_DELETE_SOFTWARE_COMPONENT'),
        'icon' => 'glyphicon glyphicon-trash',
        'url' => '#',
        'options' => ['data-toggle'=>'modal', 'data-target'=>'#removeSoftwareComponentModalForm']
    ],
];

// Если программный компонент имеет статус сгенерированного или устаревшего,
// то в правом меню добавляется пункт просмотра TMRL-кода модели трансформации
if ($model->status == SoftwareComponent::STATUS_GENERATED || $model->status == SoftwareComponent::STATUS_OUTDATED)
    array_push($this->params['menu'],
        [
            'label' => Yii::t('app', 'SOFTWARE_COMPONENTS_PAGE_VIEW_TMRL_CODE'),
            'url' => ['/software-components/view-tmrl-code/' . $model->id]
        ]);
?>

<?= $this->render('_modal_form_software_components', ['model' => $model]) ?>

<div class="software-component-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>