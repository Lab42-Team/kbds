<?php

/* @var $this yii\web\View */
/* @var $model app\modules\software_component\models\Metamodel */

use yii\helpers\Html;

$this->title = Yii::t('app', 'METAMODELS_PAGE_CREATE_METAMODEL');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'METAMODELS_PAGE_METAMODELS'),
    'url' => ['/metamodels/list']];
$this->params['breadcrumbs'][] = $this->title;

$this->params['menu'] = [
    [
        'label' => Yii::t('app', 'METAMODELS_PAGE_METAMODELS'),
        'icon' => 'glyphicon glyphicon-list-alt',
        'url' => ['/metamodels/list']
    ],
    [
        'label' => $this->title,
        'icon' => 'glyphicon glyphicon-plus-sign',
        'url' => '',
        'active' => true
    ]
];
?>

<div class="metamodel-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
