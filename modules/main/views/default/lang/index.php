<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\modules\main\models */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Langs';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="lang-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('Create Lang', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'created_at',
            'updated_at',
            'url:url',
            'local',
            // 'name',
            // 'default',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>

</div>
