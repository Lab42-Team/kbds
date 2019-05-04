<?php

/* @var $this yii\web\View */
/* @var $searchModel app\modules\knowledge_base\models\KnowledgeBaseSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

use yii\helpers\Html;
use yii\grid\GridView;
use app\modules\knowledge_base\models\KnowledgeBase;
use app\modules\knowledge_base\models\SubjectDomain;

$this->title = Yii::t('app', 'MY_KNOWLEDGE_BASES_PAGE_KNOWLEDGE_BASES');
$this->params['breadcrumbs'][] = $this->title;

$this->params['menu'] = [
    [
        'label' => $this->title,
        'icon' => 'glyphicon glyphicon-list-alt',
        'url' => '',
        'active' => true
    ],
    [
        'label' => Yii::t('app', 'KNOWLEDGE_BASES_PAGE_CREATE_KNOWLEDGE_BASE'),
        'icon' => 'glyphicon glyphicon-plus-sign',
        'url' => ['/my-knowledge-bases/create']
    ]
];
?>

<div class="my-knowledge-base-list">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php //echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            'name',
            [
                'attribute'=>'subject_domain',
                'format' => 'raw',
                'value' => function($data) {
                    return $data->subjectDomain->name;
                },
                'filter'=>SubjectDomain::getAllSubjectDomainsArray(),
            ],
            [
                'attribute'=>'type',
                'format' => 'raw',
                'value' => function($data) {
                    return $data->getTypeName();
                },
                'filter'=>KnowledgeBase::getTypesArray(),
            ],
            [
                'attribute'=>'status',
                'format' => 'raw',
                'value' => function($data) {
                    return $data->getStatusName();
                },
                'filter'=>KnowledgeBase::getStatusesArray(),
                'options' => ['width' => '100']
            ],
            [
                'attribute' => 'created_at',
                'format' => ['date', 'dd.MM.Y HH:mm:ss']
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'headerOptions' => ['class' => 'action-column'],
                'template' => '{view} {update} {delete}',
            ],
        ],
    ]); ?>

</div>