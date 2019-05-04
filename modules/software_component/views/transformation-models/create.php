<?php

/* @var $this yii\web\View */
/* @var $model app\modules\software_component\models\TransformationModel */

use yii\helpers\Html;

$this->title = Yii::t('app', 'TRANSFORMATION_MODELS_PAGE_CREATE_TRANSFORMATION_MODEL');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'TRANSFORMATION_MODELS_PAGE_TRANSFORMATION_MODELS'),
    'url' => ['/transformation-models/list']];
$this->params['breadcrumbs'][] = $this->title;

$this->params['menu'] = [
    [
        'label' => Yii::t('app', 'TRANSFORMATION_MODELS_PAGE_TRANSFORMATION_MODELS'),
        'icon' => 'glyphicon glyphicon-list-alt',
        'url' => ['/transformation-models/list']
    ],
    [
        'label' => $this->title,
        'icon' => 'glyphicon glyphicon-plus-sign',
        'url' => '',
        'active' => true
    ]
];
?>

<div class="transformation-model-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
