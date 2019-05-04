<?php

/* @var $this yii\web\View */
/* @var $model app\modules\software_component\models\SoftwareComponent */

use yii\helpers\Html;

$this->title = Yii::t('app', 'SOFTWARE_COMPONENTS_PAGE_CREATE_SOFTWARE_COMPONENT');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'SOFTWARE_COMPONENTS_PAGE_SOFTWARE_COMPONENTS'),
    'url' => ['/software-components/list']];
$this->params['breadcrumbs'][] = $this->title;

$this->params['menu'] = [
    [
        'label' => Yii::t('app', 'SOFTWARE_COMPONENTS_PAGE_SOFTWARE_COMPONENTS'),
        'icon' => 'glyphicon glyphicon-list-alt',
        'url' => ['/software-components/list']
    ],
    [
        'label' => $this->title,
        'icon' => 'glyphicon glyphicon-plus-sign',
        'url' => '',
        'active' => true
    ]
];
?>

<div class="software-component-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
