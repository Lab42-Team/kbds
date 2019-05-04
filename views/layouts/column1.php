<?php
/* @var $content */

use kartik\sidenav\SideNav;
?>

<?php $this->beginContent('@app/views/layouts/main.php'); ?>

    <div class="row">
        <div class="col-md-3">
            <div class="pg-sidebar">
                <?php echo SideNav::widget([
                    'type' => SideNav::TYPE_DEFAULT,
                    'heading' => Yii::t('app', 'SIDE_NAV_POSSIBLE_ACTIONS'),
                    'items' => $this->params['menu'],
                ]); ?>
            </div><!-- sidebar -->
        </div>
        <div class="col-md-9">
            <?= $content; ?>
        </div>
    </div>
<?php $this->endContent(); ?>
