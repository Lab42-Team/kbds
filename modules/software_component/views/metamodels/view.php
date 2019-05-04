<?php

/* @var $this yii\web\View */
/* @var $model app\modules\software_component\models\Metamodel */

use yii\helpers\Html;
use yii\widgets\DetailView;
use app\modules\software_component\models\Metamodel;

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'METAMODELS_PAGE_METAMODELS'),
    'url' => ['list']];
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
        'url' => '',
        'active' => true
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
    ]
];

// Если метамодель является пользовательской (не созданной по умолчанию),
// то добавляем в правое меню ссылки для импорта метамоделей
if ($model->type == Metamodel::USER_TYPE)
    array_push($this->params['menu'],
        [
            'label' => Yii::t('app', 'METAMODELS_PAGE_IMPORT_CONCEPTUAL_MODEL'),
            'url' => ['/metamodels/import-conceptual-model/' . $model->id]
        ],
        [
            'label' => Yii::t('app', 'METAMODELS_PAGE_IMPORT_XML_SCHEMA'),
            'url' => ['/metamodels/import-xml-schema/' . $model->id]
        ]);
?>

<?= $this->render('_modal_form_metamodels', ['model' => $model]) ?>

<div class="metamodel-view">

    <h1><?= Html::encode(Yii::t('app', 'METAMODELS_PAGE_VIEW_METAMODEL')) . ': ' . $model->name ?></h1>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'name',
            [
                'attribute'=>'type',
                'value' => $model->getTypeName(),
            ],
            [
                'attribute'=>'author',
                'value' => $model->fkAuthor->username,
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