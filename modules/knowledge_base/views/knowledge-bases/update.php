<?php

/* @var $this yii\web\View */
/* @var $model app\modules\knowledge_base\models\KnowledgeBase */

use yii\helpers\Html;
use app\modules\knowledge_base\models\KnowledgeBase;

$this->title = Yii::t('app', 'KNOWLEDGE_BASES_PAGE_UPDATE_KNOWLEDGE_BASE');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'KNOWLEDGE_BASES_PAGE_KNOWLEDGE_BASES'), 'url' => ['list']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'KNOWLEDGE_BASES_PAGE_KNOWLEDGE_BASE') . ' - ' . $model->name,
    'url' => ['/knowledge-bases/view/' . $model->id]];
$this->params['breadcrumbs'][] = $this->title;

$this->params['menu'] = [
    [
        'label' => Yii::t('app', 'KNOWLEDGE_BASES_PAGE_KNOWLEDGE_BASES'),
        'icon' => 'glyphicon glyphicon-list-alt',
        'url' => ['/knowledge-bases/list']
    ],
    [
        'label' => Yii::t('app', 'KNOWLEDGE_BASES_PAGE_CREATE_KNOWLEDGE_BASE'),
        'icon' => 'glyphicon glyphicon-plus-sign',
        'url' => ['/knowledge-bases/create']
    ],
    [
        'label' => Yii::t('app', 'KNOWLEDGE_BASES_PAGE_VIEW_KNOWLEDGE_BASE'),
        'icon' => 'glyphicon glyphicon-eye-open',
        'url' => ['/knowledge-bases/view/' . $model->id]
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
        'options' => ['data-toggle'=>'modal', 'data-target'=>'#removeKnowledgeBaseModalForm']
    ]
];

// Если БЗ является онтологией, то добавляем в правое меню ссылки на онтологический редактор,
// страницу генерации онтологии и страницу генерации кода OWL
if ($model->type == KnowledgeBase::TYPE_ONTOLOGY)
    array_push($this->params['menu'],
        [
            'label' => Yii::t('app', 'ONTOLOGY_EDITOR_PAGE_ONTOLOGY_EDITOR'),
            'url' => ['/ontology-editor/' . $model->id]
        ],
        [
            'label' => Yii::t('app', 'KNOWLEDGE_BASES_PAGE_GENERATE_ONTOLOGY'),
            'url' => ['/knowledge-bases/generate-ontology/' . $model->id]
        ],
        [
            'label' => Yii::t('app', 'KNOWLEDGE_BASES_PAGE_GENERATE_OWL_CODE'),
            'url' => ['/knowledge-bases/generate-owl-code/' . $model->id]
        ]);

// Если БЗ является продукционной, то добавляем в правое меню ссылки на продукционный редактор RVML,
// страницу генерации продукций и страницу генерации кода CLIPS
if ($model->type == KnowledgeBase::TYPE_RULES)
    array_push($this->params['menu'],
        [
            'label' => Yii::t('app', 'RVML_EDITOR_PAGE_RVML_EDITOR'),
            'url' => ['/rvml-editor/' . $model->id]
        ],
        [
            'label' => Yii::t('app', 'KNOWLEDGE_BASES_PAGE_GENERATE_PRODUCTION_MODEL'),
            'url' => ['/knowledge-bases/generate-production-model/' . $model->id]
        ],
        [
            'label' => Yii::t('app', 'KNOWLEDGE_BASES_PAGE_GENERATE_CLIPS_CODE'),
            'url' => ['/knowledge-bases/generate-clips-code/' . $model->id]
        ]);
?>

<?= $this->render('_modal_form_knowledge_bases', ['model' => $model]) ?>

<div class="knowledge-base-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>