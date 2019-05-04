<?php

/* @var $this yii\web\View */
/* @var $model app\modules\knowledge_base\models\SubjectDomain */

use yii\helpers\Html;

$this->title = Yii::t('app', 'SUBJECT_DOMAINS_PAGE_CREATE_SUBJECT_DOMAIN');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'SUBJECT_DOMAINS_PAGE_SUBJECT_DOMAINS'), 'url' => ['/subject-domains/list']];
$this->params['breadcrumbs'][] = $this->title;

$this->params['menu'] = [
    [
        'label' => Yii::t('app', 'SUBJECT_DOMAINS_PAGE_SUBJECT_DOMAINS'),
        'icon' => 'glyphicon glyphicon-list-alt',
        'url' => ['/subject-domains/list']
    ],
    [
        'label' => $this->title,
        'icon' => 'glyphicon glyphicon-plus-sign',
        'url' => '',
        'active' => true
    ]
];
?>

<div class="subject-domain-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>