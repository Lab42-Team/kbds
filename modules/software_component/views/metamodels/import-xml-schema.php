<?php

/* @var $this yii\web\View */
/* @var $file_form app\modules\software_component\models\XMLSchemaFileForm */
/* @var $model app\modules\software_component\models\Metamodel */
/* @var $metaclasses app\modules\software_component\controllers\MetamodelsController */

use yii\helpers\Html;
use yii\bootstrap\Alert;
use yii\widgets\ActiveForm;

$this->title = Yii::t('app', 'METAMODELS_PAGE_IMPORT_XML_SCHEMA');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'METAMODELS_PAGE_METAMODELS'),
    'url' => ['list']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'METAMODELS_PAGE_METAMODEL') . ' - ' . $model->name,
    'url' => ['/metamodels/view/' . $model->id]];
$this->params['breadcrumbs'][] = $this->title;

$this->params['menu'] = [
    [
        'label' => Yii::t('app', 'METAMODELS_PAGE_METAMODELS'),
        'icon' => 'glyphicon glyphicon-list-alt',
        'url' => ['/metamodels/list']
    ],
    [
        'label' => Yii::t('app', 'METAMODELS_PAGE_CREATE_METAMODEL'),
        'icon' => 'glyphicon glyphicon-plus-sign',
        'url' => ['/metamodels/create']
    ],
    [
        'label' => Yii::t('app', 'METAMODELS_PAGE_VIEW_METAMODEL'),
        'icon' => 'glyphicon glyphicon-eye-open',
        'url' => ['/metamodels/view/' . $model->id],
    ],
    [
        'label' => Yii::t('app', 'METAMODELS_PAGE_UPDATE_METAMODEL'),
        'icon' => 'glyphicon glyphicon-pencil',
        'url' => ['/metamodels/update/' . $model->id]
    ],
    [
        'label' => Yii::t('app', 'METAMODELS_PAGE_DELETE_METAMODEL'),
        'icon' => 'glyphicon glyphicon-trash',
        'url' => '#',
        'options' => ['data-toggle'=>'modal', 'data-target'=>'#removeMetamodelModalForm']
    ],
    [
        'label' => Yii::t('app', 'METAMODELS_PAGE_METAMODEL_EDITOR'),
        'url' => ['/metamodels/metamodel-editor/' . $model->id]
    ],
    [
        'label' => Yii::t('app', 'METAMODELS_PAGE_IMPORT_CONCEPTUAL_MODEL'),
        'url' => ['/metamodels/import-conceptual-model/' . $model->id]
    ],
    [
        'label' => Yii::t('app', 'METAMODELS_PAGE_IMPORT_XML_SCHEMA'),
        'url' => '',
        'active' => true
    ]
];
?>

<?= $this->render('_modal_form_metamodels', ['model' => $model]) ?>

<div class="xml-schema-import">

    <h1><?= Html::encode($this->title) ?></h1>

    <!-- Если метамодель уже сформирована, то выводим предупредительное сообщение -->
    <?php if(!empty($metaclasses)): ?>
        <?php echo Alert::widget([
            'options' => ['class' => 'alert-warning'],
            'body' => '<b>' . Yii::t('app', 'NOTICE_TITLE') . '</b> ' . Yii::t('app', 'NOTICE_TEXT')
        ]); ?>
        <div class="well">
            <?= Yii::t('app', 'METAMODEL_MODEL_MESSAGE_EXIST_CLASSES') ?>
        </div>
    <?php endif; ?>

    <!-- Формирование формы загрузки XML-схемы концептуальной модели -->
    <?php $form = ActiveForm::begin([
        'id'=>'import-xml-schema-form',
        'options' => ['enctype' => 'multipart/form-data']
    ]); ?>

        <?= $form->errorSummary($file_form); ?>

        <?= $form->field($file_form, 'xml_schema_file')->fileInput() ?>

        <div class="form-group">
            <?= Html::submitButton(Yii::t('app', 'BUTTON_IMPORT'),
                ['class' => 'btn btn-success', 'name'=>'import-xml-schema-button']) ?>
        </div>

    <?php ActiveForm::end(); ?>
</div>