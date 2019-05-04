<?php

/* @var $this yii\web\View */
/* @var $model app\modules\knowledge_base\models\KnowledgeBase */

use yii\helpers\Html;
use yii\widgets\DetailView;
use app\modules\knowledge_base\models\KnowledgeBase;

$this->title = Yii::t('app', 'KNOWLEDGE_BASES_PAGE_KNOWLEDGE_BASE') . ' - ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'KNOWLEDGE_BASES_PAGE_KNOWLEDGE_BASES'), 'url' => ['list']];
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
        'url' => '',
        'active' => true
    ],
    [
        'label' => Yii::t('app', 'KNOWLEDGE_BASES_PAGE_UPDATE_KNOWLEDGE_BASE'),
        'icon' => 'glyphicon glyphicon-pencil',
        'url' => ['/knowledge-bases/update/' . $model->id]
    ],
    [
        'label' => Yii::t('app', 'KNOWLEDGE_BASES_PAGE_DELETE_KNOWLEDGE_BASE'),
        'icon' => 'glyphicon glyphicon-trash',
        'url' => '#',
        'options' => ['data-toggle'=>'modal', 'data-target'=>'#removeKnowledgeBaseModalForm']
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
            'url' => ['/knowledge-bases/generate-ontology/' . $model->id]
        ],
        [
            'label' => Yii::t('app', 'KNOWLEDGE_BASES_PAGE_GENERATE_OWL_CODE'),
            'url' => ['/knowledge-bases/generate-owl-code/' . $model->id]
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
            'url' => ['/knowledge-bases/generate-production-model/' . $model->id]
        ],
        [
            'label' => Yii::t('app', 'KNOWLEDGE_BASES_PAGE_GENERATE_CLIPS_CODE'),
            'url' => ['/knowledge-bases/generate-clips-code/' . $model->id]
        ]);
?>

<?= $this->render('_modal_form_knowledge_bases', ['model' => $model]) ?>

<div class="knowledge-base-view">

    <h1><?= Html::encode(Yii::t('app', 'KNOWLEDGE_BASES_PAGE_VIEW_KNOWLEDGE_BASE')) . ': ' . $model->name ?></h1>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'name',
            [
                'attribute'=>'subject_domain',
                'value' => $model->subjectDomain->name,
            ],
            [
                'attribute'=>'type',
                'value' => $model->getTypeName(),
            ],
            [
                'attribute'=>'status',
                'format' => 'raw',
                'value' => $model->getStatusName(),
            ],
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
