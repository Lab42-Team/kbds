<?php

/* @var $this yii\web\View */
/* @var $model app\modules\knowledge_base\models\KnowledgeBase */

use yii\helpers\Html;

$this->title = Yii::t('app', 'KNOWLEDGE_BASES_PAGE_CREATE_KNOWLEDGE_BASE');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'KNOWLEDGE_BASES_PAGE_KNOWLEDGE_BASES'),
    'url' => ['/knowledge-bases/list']];
$this->params['breadcrumbs'][] = $this->title;

$this->params['menu'] = [
    [
        'label' => Yii::t('app', 'KNOWLEDGE_BASES_PAGE_KNOWLEDGE_BASES'),
        'icon' => 'glyphicon glyphicon-list-alt',
        'url' => ['/knowledge-bases/list']
    ],
    [
        'label' => $this->title,
        'icon' => 'glyphicon glyphicon-plus-sign',
        'url' => '',
        'active' => true
    ]
];
?>

<div class="knowledge-base-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
