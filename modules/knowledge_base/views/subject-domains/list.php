<?php

/* @var $this yii\web\View */
/* @var $searchModel app\modules\knowledge_base\models\SubjectDomainSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

use yii\helpers\Html;
use yii\grid\GridView;
use app\modules\user\models\User;

$this->title = Yii::t('app', 'SUBJECT_DOMAINS_PAGE_SUBJECT_DOMAINS');
$this->params['breadcrumbs'][] = $this->title;

$this->params['menu'] = [
    [
        'label' => $this->title,
        'icon' => 'glyphicon glyphicon-list-alt',
        'url' => '',
        'active' => true
    ],
    [
        'label' => Yii::t('app', 'SUBJECT_DOMAINS_PAGE_CREATE_SUBJECT_DOMAIN'),
        'icon' => 'glyphicon glyphicon-plus-sign',
        'url' => ['/subject-domains/create']
    ]
];
?>

<div class="subject-domain-list">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php //echo $this->render('_search', ['model' => $searchModel]); ?>

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
                'attribute'=>'author',
                'format' => 'raw',
                'value' => function($data) {
                    return $data->kbAuthor->username;
                },
                'filter'=>User::getAllUsersArray(),
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
