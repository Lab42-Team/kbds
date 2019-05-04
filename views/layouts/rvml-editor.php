<?php

/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use app\assets\AppAsset;
use app\components\widgets\WLang;

AppAsset::register($this);
?>

<?php $this->beginPage() ?>

<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">

<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>

<body>
<?php $this->beginBody() ?>

<div class="wrap">
    <?php
        NavBar::begin([
            'brandLabel' => Html::img('@web/images/mini-RVML-logo.png', ['class'=>'main-logo']),
            'brandUrl' => '',
        ]);
        echo Nav::widget([
            'options' => ['class' => 'navbar-nav navbar-left'],
            'encodeLabels' => false,
            'items' => array_filter([
                [
                    'label' => '<span class="glyphicon glyphicon-new-window"></span> ' .
                        Yii::t('app', 'RVML_EDITOR_PAGE_RETURN_TO_KNOWLEDGE_BASES'),
                    'url' => ['/my-knowledge-bases/list']
                ],
                [
                    'label' => '<span class="glyphicon glyphicon-plus"></span> ' . Yii::t('app', 'BUTTON_ADD'),
                    'items' => $this->params['menu']
                ],
                $this->params['export-link']
            ])
        ]);
        echo "<form class='navbar-form navbar-right'>" . WLang::widget() . "</form>";
        NavBar::end();
    ?>
    <div class="rvml-editor"><?= $content ?></div>
</div>

<footer class="footer">
    <div class="container">
        <p class="pull-left"><?= ' &copy; ' . date('Y') . ' ' . Yii::t('app', 'FOOTER_INSTITUTE') ?></p>
        <p class="pull-right"><?= Yii::t('app', 'FOOTER_POWERED_BY') . ' ' .
            ' <a href="mailto:DorodnyxNikita@gmail.com">'.Yii::$app->params['adminEmail'].'</a>' ?></p>
    </div>
</footer>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>