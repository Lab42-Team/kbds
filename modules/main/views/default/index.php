<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\bootstrap\Carousel;

$this->title = Yii::$app->name;
?>

<div class="main-default-index">

    <?php echo Carousel::widget ( [
        'items' => [
            ['content' => Html::img('@web/images/KBDS-logo.png', ['class'=>'logo', 'width'=>'70%'])],
            ['content' => Html::img('@web/images/RVML-logo.png', ['class'=>'logo', 'width'=>'70%'])],
            ['content' => Html::img('@web/images/PKBD-logo.png', ['class'=>'logo', 'width'=>'70%'])]
        ],
        'options' => []
    ]); ?>

    <div class="body-content">
        <div class="row">
            <div class="col-lg-4"></div>
            <div class="col-lg-4"></div>
            <div class="col-lg-4"></div>
        </div>
    </div>
</div>