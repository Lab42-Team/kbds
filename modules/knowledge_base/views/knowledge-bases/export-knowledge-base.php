<?php

/* @var $model app\modules\knowledge_base\models\KnowledgeBase */
/* @var $exist_ontology_elements app\modules\knowledge_base\controllers\KnowledgeBasesController */
/* @var $exist_production_model_elements app\modules\knowledge_base\controllers\KnowledgeBasesController */

use yii\helpers\Html;
use yii\bootstrap\Alert;
use yii\widgets\ActiveForm;
use app\modules\knowledge_base\models\KnowledgeBase;

$this->title = Yii::t('app', 'KNOWLEDGE_BASES_PAGE_EXPORT_KNOWLEDGE_BASE');
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

<div class="export-knowledge-base">

    <h1><?= Html::encode($this->title) ?></h1>

    <!-- Если не существуют элементы у данной базы знаний (онтологической модели) -->
    <?php if($exist_ontology_elements == false && $model->type == KnowledgeBase::TYPE_ONTOLOGY): ?>
        <!-- Вывод предупредительного сообщения -->
        <?php echo Alert::widget([
            'options' => ['class' => 'alert-warning'],
            'body' => '<b>' . Yii::t('app', 'NOTICE_TITLE') . '</b> ' . Yii::t('app', 'NOTICE_TEXT')
        ]); ?>
        <div class="well">
            <?= Yii::t('app', 'KNOWLEDGE_BASE_MODEL_MESSAGE_NOT_EXIST_ONTOLOGY') ?>
        </div>
    <?php endif; ?>

    <!-- Если не существуют элементы у данной базы знаний (продукционной модели) -->
    <?php if($exist_production_model_elements == false && $model->type == KnowledgeBase::TYPE_RULES): ?>
        <!-- Вывод предупредительного сообщения -->
        <?php echo Alert::widget([
            'options' => ['class' => 'alert-warning'],
            'body' => '<b>' . Yii::t('app', 'NOTICE_TITLE') . '</b> ' . Yii::t('app', 'NOTICE_TEXT')
        ]); ?>
        <div class="well">
            <?= Yii::t('app', 'KNOWLEDGE_BASE_MODEL_MESSAGE_NOT_EXIST_PRODUCTION_MODEL') ?>
        </div>
    <?php endif; ?>

    <!-- Если существуют элементы у данной базы знаний (продукционной модели) -->
    <?php if($exist_ontology_elements == true || $exist_production_model_elements == true): ?>

        <!-- Информация об OWL -->
        <?php if($model->type == KnowledgeBase::TYPE_ONTOLOGY): ?>
            <p>
                <b>OWL (Web Ontology Language)</b> -
                <?= Yii::t('app', 'OWL_CODE_CLIPS_DESCRIPTION') ?>
            </p>
            <div >
                <?php echo Alert::widget([
                    'options' => [
                        'class' => 'alert-info',
                    ],
                    'body' => Yii::t('app', 'OWL_CODE_GENERATION'),
                ]); ?>
            </div>
        <?php endif; ?>
        <!-- Информация о CLIPS -->
        <?php if($model->type == KnowledgeBase::TYPE_RULES): ?>
            <p>
                <b>CLIPS (C Language Integrated Production System)</b> -
                <?= Yii::t('app', 'CLIPS_CODE_CLIPS_DESCRIPTION') ?>
            </p>
            <div >
                <?php echo Alert::widget([
                    'options' => [
                        'class' => 'alert-info',
                    ],
                    'body' => Yii::t('app', 'CLIPS_CODE_GENERATION'),
                ]); ?>
            </div>
        <?php endif; ?>

        <!-- Форма скачивания базы знаний в формате CLIPS или OWL (выгрузка соответствующих файлов) -->
        <?php $form = ActiveForm::begin([
            'id'=>'export-knowledge-base-code-form',
            'options' => ['enctype' => 'multipart/form-data']
        ]); ?>

            <div class="form-group">
                <?= Html::submitButton(Yii::t('app', 'BUTTON_EXPORT'),
                    ['class' => 'btn btn-success', 'name'=>'export-knowledge-base-code-button']) ?>
            </div>

        <?php ActiveForm::end(); ?>
    <?php endif; ?>

</div>