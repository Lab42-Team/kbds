<?php

namespace app\modules\main\controllers;

use Yii;
use yii\web\Controller;
use \yii\console\controllers\MigrateController;
use app\commands\RbacController;
use app\commands\UsersController;
use app\commands\MetamodelController;
use app\commands\SoftwareComponentController;

class DefaultController extends Controller
{
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    /**
     * Главная страница сайта.
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Страница помощи.
     * @return string
     */
    public function actionHelp()
    {
        return $this->render('help');
    }

    /**
     * Выполнение команд миграций через http-запрос.
     * @return string
     * @throws \yii\console\Exception
     */
    public function actionMigrateUp()
    {
        if (!defined('STDOUT'))
            define('STDOUT', fopen('/tmp/stdout', 'w'));
        // migrations command begin
        $migration = new MigrateController('migrate', Yii::$app);
        $migration->runAction('', ['migrationPath' => '@yii/rbac/migrations', 'interactive' => false]);
        $migration->runAction('up', ['migrationPath' => '@app/migrations/', 'interactive' => false]);
        // migrations command end
        $handle = fopen('/tmp/stdout', 'r');
        $message = '';
        while (($buffer = fgets($handle, 4096)) !== false)
            $message.=$buffer . "<br>";
        fclose($handle);

        return $this->render('index', ['message' => $message]);
    }

    /**
     * Выполнение команды создания ролей пользователей RBAC по умолчанию через http-запрос.
     * @return string
     * @throws \yii\console\Exception
     */
    public function actionRbacInit()
    {
        if (!defined('STDOUT'))
            define('STDOUT', fopen('/tmp/stdout', 'w'));
        // command begin
        $migration = new RbacController('rbac', Yii::$app);
        $migration->runAction('init');
        // command end
        $handle = fopen('/tmp/stdout', 'r');
        $message = '';
        while (($buffer = fgets($handle, 4096)) !== false)
            $message.=$buffer . "<br>";
        fclose($handle);

        return $this->render('index', ['message' => $message]);
    }

    /**
     * Выполнение команды создания пользователя (администратора) по умолчанию через http-запрос.
     * @return string
     * @throws \yii\console\Exception
     */
    public function actionCreateDefaultUser()
    {
        if (!defined('STDOUT'))
            define('STDOUT', fopen('/tmp/stdout', 'w'));
        // command begin
        $migration = new UsersController('users', Yii::$app);
        $migration->runAction('create-default-user');
        // command end
        $handle = fopen('/tmp/stdout', 'r');
        $message = '';
        while (($buffer = fgets($handle, 4096)) !== false)
            $message.=$buffer . "<br>";
        fclose($handle);

        return $this->render('index', ['message' => $message]);
    }

    /**
     * Выполнение команды создания метамоделей по умолчанию через http-запрос.
     * @return string
     * @throws \yii\console\Exception
     */
    public function actionCreateDefaultMetamodels()
    {
        if (!defined('STDOUT'))
            define('STDOUT', fopen('/tmp/stdout', 'w'));
        // command begin
        $migration = new MetamodelController('metamodel', Yii::$app);
        $migration->runAction('create');
        // command end
        $handle = fopen('/tmp/stdout', 'r');
        $message = '';
        while (($buffer = fgets($handle, 4096)) !== false)
            $message.=$buffer . "<br>";
        fclose($handle);

        return $this->render('index', ['message' => $message]);
    }

    /**
     * Выполнение команды создания программных компонентов по умолчанию через http-запрос.
     * @return string
     * @throws \yii\console\Exception
     */
    public function actionCreateDefaultSoftwareComponents()
    {
        if (!defined('STDOUT'))
            define('STDOUT', fopen('/tmp/stdout', 'w'));
        // command begin
        $migration = new SoftwareComponentController('software-component', Yii::$app);
        $migration->runAction('create');
        // command end
        $handle = fopen('/tmp/stdout', 'r');
        $message = '';
        while (($buffer = fgets($handle, 4096)) !== false)
            $message.=$buffer . "<br>";
        fclose($handle);

        return $this->render('index', ['message' => $message]);
    }
}