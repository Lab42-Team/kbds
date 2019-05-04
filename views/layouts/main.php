<?php

/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use app\assets\AppAsset;
use app\components\widgets\Alert;
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
            'brandLabel' => Html::img('@web/images/mini-KBDS-logo.png', ['alt'=>Yii::$app->name, 'class'=>'main-logo']),
            'brandUrl' => Yii::$app->homeUrl,
            'options' => [
                'class' => 'navbar-inverse navbar-fixed-top',
            ],
        ]);
        echo Nav::widget([
            'options' => ['class' => 'navbar-nav navbar-left'],
            'encodeLabels' => false,
            'items' => array_filter([
                Yii::$app->user->isGuest ?
                    ['label' => '<span class="glyphicon glyphicon-question-sign"></span> ' . Yii::t('app', 'NAV_HELP'),
                        'url' => ['/main/default/help']] :
                    false,
                Yii::$app->user->isGuest ?
                    ['label' => '<span class="glyphicon glyphicon-envelope"></span> ' . Yii::t('app', 'NAV_CONTACT_US'),
                        'url' => ['/main/contact-us/index']] :
                    false,
                Yii::$app->user->can('developer') ?
                    [
                        'label' => '<span class="glyphicon glyphicon-folder-close"></span> ' .
                            Yii::t('app', 'NAV_MY_PROJECTS'),
                        'items' => [
                            ['label' => Yii::t('app', 'NAV_KNOWLEDGE_BASES'),
                                'url' => ['/project/my-knowledge-bases/list']],
                            ['label' => Yii::t('app', 'NAV_SOFTWARE_COMPONENTS'),
                                'url' => ['/project/my-software-components/list']],
                        ],
                    ] :
                    false,
                Yii::$app->user->can('admin') ?
                    [
                        'label' => '<span class="glyphicon glyphicon-cog"></span> ' .
                            Yii::t('app', 'NAV_ADMINISTRATION'),
                        'items' => [
                            '<li class="dropdown-header">' . Yii::t('app', 'NAV_WORK_WITH_KNOWLEDGE_BASE_PROJECTS') .
                            '</li>',
                            ['label' => Yii::t('app', 'NAV_KNOWLEDGE_BASES'),
                                'url' => ['/knowledge_base/knowledge-bases/list']],
                            ['label' => Yii::t('app', 'NAV_SUBJECT_DOMAINS'),
                                'url' => ['/knowledge_base/subject-domains/list']],
                            '<li class="divider"></li>',
                            '<li class="dropdown-header">' .
                            Yii::t('app', 'NAV_WORK_WITH_SOFTWARE_COMPONENT_PROJECTS') .
                            '</li>',
                            ['label' => Yii::t('app', 'NAV_SOFTWARE_COMPONENTS'),
                                'url' => ['/software_component/software-components/list']],
                            ['label' => Yii::t('app', 'NAV_METAMODELS'),
                                'url' => ['/software_component/metamodels/list']],
                            ['label' => Yii::t('app', 'NAV_TRANSFORMATION_MODELS'),
                                'url' => ['/software_component/transformation-models/list']],
                            '<li class="divider"></li>',
                            '<li class="dropdown-header">' . Yii::t('app', 'NAV_WORK_WITH_USERS') . '</li>',
                            ['label' => Yii::t('app', 'NAV_USERS'), 'url' => ['/user/users/list']],
                        ],
                    ] :
                    false,
            ])
        ]);
        echo "<form class='navbar-form navbar-right'>" . WLang::widget() . "</form>";
        echo Nav::widget([
            'options' => ['class' => 'navbar-nav navbar-right'],
            'encodeLabels' => false,
            'items' => array_filter([
                Yii::$app->user->isGuest ?
                    ['label' => '<span class="glyphicon glyphicon-edit"></span> ' . Yii::t('app', 'NAV_SIGN_UP'),
                        'url' => ['/user/default/sign-up']] :
                    false,
                Yii::$app->user->isGuest ?
                    ['label' => '<span class="glyphicon glyphicon-log-in"></span> ' . Yii::t('app', 'NAV_SIGN_IN'),
                        'url' => ['/user/default/sing-in']] :
                        [
                            'label' => '<span class="glyphicon glyphicon-home"></span> ' .
                                Yii::t('app', 'NAV_ACCOUNT'),
                            'items' => [
                                ['label' => Yii::t('app', 'NAV_SIGNED_IN_AS')],
                                ['label' => '<b style="font-size:small">' .
                                    Yii::$app->user->identity->username . '</b>'],
                                '<li class="divider"></li>',
                                ['label' => '<span class="glyphicon glyphicon-user"></span> ' .
                                    Yii::t('app', 'NAV_YOUR_PROFILE'), 'url' => ['/user/user/account']],
                                '<li class="divider"></li>',
                                ['label' => '<span class="glyphicon glyphicon-question-sign"></span> ' .
                                    Yii::t('app', 'NAV_HELP'), 'url' => ['/main/default/help']],
                                ['label' => '<span class="glyphicon glyphicon-envelope"></span> ' .
                                    Yii::t('app', 'NAV_CONTACT_US'), 'url' => ['/main/contact-us/index']],
                                '<li class="divider"></li>',
                                ['label' => '<span class="glyphicon glyphicon-log-out"></span> ' .
                                    Yii::t('app', 'NAV_SIGN_OUT'), 'url' => ['/user/default/sing-out'],
                                    'linkOptions' => ['data-method' => 'post']],
                            ],
                        ],
            ])
        ]);
        NavBar::end();
    ?>

    <div class="container">
        <?= Breadcrumbs::widget([
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
        <?= Alert::widget() ?>
        <?= $content ?>
    </div>
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
