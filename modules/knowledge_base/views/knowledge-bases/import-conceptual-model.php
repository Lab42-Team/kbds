<?php

/* @var $model app\modules\knowledge_base\models\KnowledgeBase */
/* @var $file_form app\modules\software_component\models\ConceptualModelFileForm */
/* @var $exist_knowledge_base_elements app\modules\knowledge_base\controllers\KnowledgeBasesController */
/* @var $import_progress app\modules\knowledge_base\controllers\KnowledgeBasesController */

use yii\helpers\Html;
use yii\bootstrap\Alert;
use yii\widgets\ActiveForm;
use app\modules\knowledge_base\models\KnowledgeBase;

$this->title = Yii::t('app', 'KNOWLEDGE_BASES_PAGE_IMPORT_CONCEPTUAL_MODEL');
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

<div class="import-conceptual-model">

    <h1><?= Html::encode($this->title) ?></h1>

    <!-- Если существуют элементы у данной базы знаний -->
    <?php if($exist_knowledge_base_elements): ?>
        <!-- Вывод предупредительного сообщения -->
        <?php echo Alert::widget([
            'options' => ['class' => 'alert-warning'],
            'body' => '<b>' . Yii::t('app', 'NOTICE_TITLE') . '</b> ' . Yii::t('app', 'NOTICE_TEXT')
        ]); ?>
        <div class="well">
            <?= Yii::t('app', 'KNOWLEDGE_BASE_MODEL_MESSAGE_EXIST_ENTITIES') ?>
        </div>
    <?php endif; ?>

    <!-- Форма загрузки концептуальной модели -->
    <?php $form = ActiveForm::begin([
        'id'=>'import-conceptual-model-form',
        'options' => ['enctype' => 'multipart/form-data']
    ]); ?>

        <?= $form->errorSummary($file_form); ?>

        <?= $form->field($file_form, 'conceptual_model_file')->fileInput() ?>

        <div class="form-group">
            <?= Html::submitButton(Yii::t('app', 'BUTTON_IMPORT'),
                ['class' => 'btn btn-success', 'name'=>'import-conceptual-model-button']) ?>
        </div>

    <?php ActiveForm::end(); ?>

    <!-- Если существуют информация о ходе выполнения импорта концептуальной модели -->
    <?php if($import_progress != ''): ?>
        <div class="well">
            <?php echo $import_progress; ?>
        </div>
    <?php endif; ?>

</div>