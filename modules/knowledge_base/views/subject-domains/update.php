<?php

/* @var $this yii\web\View */
/* @var $model app\modules\knowledge_base\models\SubjectDomain */

use yii\helpers\Html;

$this->title = Yii::t('app', 'SUBJECT_DOMAINS_PAGE_UPDATE_SUBJECT_DOMAIN');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'SUBJECT_DOMAINS_PAGE_SUBJECT_DOMAINS'), 'url' => ['list']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'SUBJECT_DOMAINS_PAGE_SUBJECT_DOMAIN') . ' - ' . $model->name,
    'url' => ['/subject-domains/view/' . $model->id]];
$this->params['breadcrumbs'][] = $this->title;

$this->params['menu'] = [
    [
        'label' => Yii::t('app', 'SUBJECT_DOMAINS_PAGE_SUBJECT_DOMAINS'),
        'icon' => 'glyphicon glyphicon-list-alt',
        'url' => ['/subject-domains/list']
    ],
    [
        'label' => Yii::t('app', 'SUBJECT_DOMAINS_PAGE_CREATE_SUBJECT_DOMAIN'),
        'icon' => 'glyphicon glyphicon-plus-sign',
        'url' => ['/subject-domains/create']
    ],
    [
        'label' => Yii::t('app', 'SUBJECT_DOMAINS_PAGE_VIEW_SUBJECT_DOMAIN'),
        'icon' => 'glyphicon glyphicon-eye-open',
        'url' => ['/subject-domains/view/' . $model->id]
    ],
    [
        'label' => $this->title,
        'icon' => 'glyphicon glyphicon-pencil',
        'url' => '',
        'active' => true
    ],
    [
        'label' => Yii::t('app', 'SUBJECT_DOMAINS_PAGE_DELETE_SUBJECT_DOMAIN'),
        'icon' => 'glyphicon glyphicon-trash',
        'url' => '#',
        'options' => ['data-toggle'=>'modal', 'data-target'=>'#removeSubjectDomainModalForm']
    ]
];
?>

<?= $this->render('_modal_form_subject_domains', ['model' => $model]) ?>

<div class="subject-domain-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>