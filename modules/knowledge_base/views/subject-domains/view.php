<?php

/* @var $this yii\web\View */
/* @var $model app\modules\knowledge_base\models\SubjectDomain */

use yii\helpers\Html;
use yii\widgets\DetailView;

$this->title = Yii::t('app', 'SUBJECT_DOMAINS_PAGE_SUBJECT_DOMAIN') . ' - ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'SUBJECT_DOMAINS_PAGE_SUBJECT_DOMAINS'), 'url' => ['list']];
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
        'url' => '',
        'active' => true
    ],
    [
        'label' => Yii::t('app', 'SUBJECT_DOMAINS_PAGE_UPDATE_SUBJECT_DOMAIN'),
        'icon' => 'glyphicon glyphicon-pencil',
        'url' => ['/subject-domains/update/' . $model->id]
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

<div class="subject-domain-view">

    <h1><?= Html::encode(Yii::t('app', 'SUBJECT_DOMAINS_PAGE_VIEW_SUBJECT_DOMAIN')) . ': ' . $model->name ?></h1>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'name',
            [
                'attribute'=>'author',
                'value' => $model->kbAuthor->username,
            ],
            [
                'attribute' => 'created_at',
                'format' => ['date', 'dd.MM.Y HH:mm:ss']
            ],
            [
                'attribute' => 'updated_at',
                'format' => ['date', 'dd.MM.Y HH:mm:ss']
            ],
            'description',
        ],
    ]) ?>

</div>