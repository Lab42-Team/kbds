<?php

/* @var $this yii\web\View */
/* @var $model app\modules\knowledge_base\models\KnowledgeBase */
/* @var $subject_domain_model app\modules\knowledge_base\models\SubjectDomain */

use yii\helpers\Html;
use app\modules\knowledge_base\models\KnowledgeBase;

$this->title = Yii::t('app', 'KNOWLEDGE_BASES_PAGE_UPDATE_KNOWLEDGE_BASE');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'MY_KNOWLEDGE_BASES_PAGE_KNOWLEDGE_BASES'),
    'url' => ['list']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'KNOWLEDGE_BASES_PAGE_KNOWLEDGE_BASE') . ' - ' . $model->name,
    'url' => ['/my-knowledge-bases/view/' . $model->id]];
$this->params['breadcrumbs'][] = $this->title;

$this->params['menu'] = [
    [
        'label' => Yii::t('app', 'MY_KNOWLEDGE_BASES_PAGE_KNOWLEDGE_BASES'),
        'icon' => 'glyphicon glyphicon-list-alt',
        'url' => ['/my-knowledge-bases/list']
    ],
    [
        'label' => Yii::t('app', 'KNOWLEDGE_BASES_PAGE_CREATE_KNOWLEDGE_BASE'),
        'icon' => 'glyphicon glyphicon-plus-sign',
        'url' => ['/my-knowledge-bases/create']
    ],
    [
        'label' => Yii::t('app', 'KNOWLEDGE_BASES_PAGE_VIEW_KNOWLEDGE_BASE'),
        'icon' => 'glyphicon glyphicon-eye-open',
        'url' => ['/my-knowledge-bases/view/' . $model->id]
    ],
    [
        'label' => $this->title,
        'icon' => 'glyphicon glyphicon-pencil',
        'url' => '',
        'active' => true
    ],
    [
        'label' => Yii::t('app', 'KNOWLEDGE_BASES_PAGE_DELETE_KNOWLEDGE_BASE'),
        'icon' => 'glyphicon glyphicon-trash',
        'url' => '#',
        'options' => ['data-toggle'=>'modal', 'data-target'=>'#removeMyKnowledgeBaseModalForm']
    ]
];

// Добавление в правое меню ссылки на страницу онтологического редактора, генерации онтологии и генерации кода OWL,
// если БЗ является онтологией
if ($model->type == KnowledgeBase::TYPE_ONTOLOGY)
    array_push($this->params['menu'],
        [
            'label' => Yii::t('app', 'ONTOLOGY_EDITOR_PAGE_ONTOLOGY_EDITOR'),
            'url' => ['/ontology-editor/' . $model->id]
        ],
        [
            'label' => Yii::t('app', 'KNOWLEDGE_BASES_PAGE_GENERATE_ONTOLOGY'),
            'url' => ['/my-knowledge-bases/generate-ontology/' . $model->id]
        ],
        [
            'label' => Yii::t('app', 'KNOWLEDGE_BASES_PAGE_GENERATE_OWL_CODE'),
            'url' => ['/my-knowledge-bases/generate-owl-code/' . $model->id]
        ]);

// Добавление в правое меню ссылки на страницу редактора RVML, генерации продукций и генерации кода CLIPS,
// если БЗ является продукционной
if ($model->type == KnowledgeBase::TYPE_RULES)
    array_push($this->params['menu'],
        [
            'label' => Yii::t('app', 'RVML_EDITOR_PAGE_RVML_EDITOR'),
            'url' => ['/rvml-editor/' . $model->id]
        ],
        [
            'label' => Yii::t('app', 'KNOWLEDGE_BASES_PAGE_GENERATE_PRODUCTION_MODEL'),
            'url' => ['/my-knowledge-bases/generate-production-model/' . $model->id]
        ],
        [
            'label' => Yii::t('app', 'KNOWLEDGE_BASES_PAGE_GENERATE_CLIPS_CODE'),
            'url' => ['/my-knowledge-bases/generate-clips-code/' . $model->id]
        ]);
?>

<?= $this->render('_modal_form_my_knowledge_bases', ['model' => $model]) ?>

<div class="my-knowledge-base-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'subject_domain_model' => $subject_domain_model
    ]) ?>

</div>