<?php

/* @var $model app\modules\knowledge_base\models\KnowledgeBase */
/* @var $searchModel app\modules\software_component\models\SoftwareComponent */
/* @var $dataProvider yii\data\ActiveDataProvider */

use yii\helpers\Url;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\Alert;
use app\modules\user\models\User;
use app\modules\knowledge_base\models\KnowledgeBase;
use app\modules\software_component\models\SoftwareComponent;

$this->title = Yii::t('app', 'KNOWLEDGE_BASES_PAGE_GENERATE_PRODUCTION_MODEL');
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
            'url' => '',
            'active' => true
        ],
        [
            'label' => Yii::t('app', 'KNOWLEDGE_BASES_PAGE_GENERATE_CLIPS_CODE'),
            'url' => ['/knowledge-bases/generate-clips-code/' . $model->id]
        ]);
?>

<?= $this->render('_modal_form_knowledge_bases', ['model' => $model]) ?>

<div class="generate-production-model">

    <h1><?= Html::encode($this->title) ?></h1>

    <div >
        <?php echo Alert::widget([
            'options' => [
                'class' => 'alert-info',
            ],
            'body' => Yii::t('app', 'KNOWLEDGE_BASE_MODEL_SELECT_SOFTWARE_COMPONENT_FOR_GENERATE_PRODUCTION_MODEL'),
        ]); ?>
    </div>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            [
                'attribute' => 'id',
                'options' => ['width' => '60']
            ],
            'name',
            [
                'attribute'=>'status',
                'format' => 'raw',
                'value' => function($data) {
                    return $data->getStatusName();
                },
                'filter'=>SoftwareComponent::getSomeStatusesArray(),
            ],
            [
                'attribute'=>'author',
                'format' => 'raw',
                'value' => function($data) {
                    return $data->fkAuthor->username;
                },
                'filter'=>User::getAllUsersArray(),
            ],
            [
                'attribute' => 'created_at',
                'format' => ['date', 'dd.MM.Y HH:mm:ss']
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view} {select}',
                'buttons' => [
                    'view' => function ($url, $model, $key) {
                        $url = Url::toRoute('/software-components/view/' . $key);
                        return Html::a(
                            '<span class="glyphicon glyphicon-eye-open"></span>',
                            $url);
                    },
                    'select' => function ($url, $software_component, $key) use ($model) {
                        $url = Url::toRoute('/knowledge-bases/import-conceptual-model/' . $model->id . '/' . $key);
                        return Html::a(
                            '<span class="glyphicon glyphicon-circle-arrow-down"></span>',
                            $url);
                    },
                ],
            ],
        ],
    ]); ?>

</div>