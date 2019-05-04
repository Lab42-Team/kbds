<?php

namespace app\commands;

use Yii;
use yii\base\Model;
use yii\console\Controller;
use yii\console\Exception;
use yii\helpers\Console;
use app\modules\user\models\User;

/**
 * UsersController реализует консольные команды для работы с пользователями.
 */
class UsersController extends Controller
{
    /**
     * Инициализация команд.
     */
    public function actionIndex()
    {
        echo 'yii users/create-default-user' . PHP_EOL;
        echo 'yii users/create' . PHP_EOL;
        echo 'yii users/remove' . PHP_EOL;
        echo 'yii users/activate' . PHP_EOL;
        echo 'yii users/change-password' . PHP_EOL;
    }

    /**
     * Команда создания пользователя (администратора) по умолчанию.
     */
    public function actionCreateDefaultUser()
    {
        $model = new User();
        $model->username = 'admin';
        $model->email = 'dorodnyxnikita@gmail.com';
        $model->status = User::STATUS_ACTIVE;
        $model->setPassword('123456');
        $model->generateAuthKey();
        $this->log($model->save());
        // Назначение роли admin пользователю по умолчанию
        $auth = Yii::$app->authManager;
        $admin = $auth->getRole('admin');
        $auth->assign($admin, $model->id);
    }

    /**
     * Команда создания пользователя (администратора).
     */
    public function actionCreate()
    {
        $model = new User();
        $this->readValue($model, 'username');
        $this->readValue($model, 'email');
        $model->setPassword($this->prompt('Password:', [
            'required' => true,
            'pattern' => '#^.{6,255}$#i',
            'error' => 'More than 6 symbols',
        ]));
        $model->generateAuthKey();
        $this->log($model->save());
        // Назначение роли admin пользователю по умолчанию
        $auth = Yii::$app->authManager;
        $admin = $auth->getRole('admin');
        $auth->assign($admin, $model->id);
    }

    /**
     * Команда удаления пользователя.
     */
    public function actionRemove()
    {
        $username = $this->prompt('Username:', ['required' => true]);
        $model = $this->findModel($username);
        $this->log($model->delete());
    }

    /**
     * Команда активации пользователя.
     */
    public function actionActivate()
    {
        $username = $this->prompt('Username:', ['required' => true]);
        $model = $this->findModel($username);
        $model->status = User::STATUS_ACTIVE;
        $model->removeEmailConfirmToken();
        $this->log($model->save());
    }

    /**
     * Команда смены пароля пользователю.
     */
    public function actionChangePassword()
    {
        $username = $this->prompt('Username:', ['required' => true]);
        $model = $this->findModel($username);
        $model->setPassword($this->prompt('New password:', [
            'required' => true,
            'pattern' => '#^.{6,255}$#i',
            'error' => 'More than 6 symbols',
        ]));
        $this->log($model->save());
    }

    /**
     * Поиск пользователя по имени.
     * @param string $username
     * @throws \yii\console\Exception
     * @return User the loaded model
     */
    private function findModel($username)
    {
        if (!$model = User::findOne(['username' => $username])) {
            throw new Exception('User not found');
        }
        return $model;
    }

    /**
     * Чтение введенных пользователем значений (атрибутов команды) через командную строку.
     * @param Model $model
     * @param string $attribute
     */
    private function readValue($model, $attribute)
    {
        $model->$attribute = $this->prompt(mb_convert_case($attribute, MB_CASE_TITLE, 'utf-8') . ':', [
            'validator' => function ($input, &$error) use ($model, $attribute) {
                $model->$attribute = $input;
                if ($model->validate([$attribute])) {
                    return true;
                } else {
                    $error = implode(',', $model->getErrors($attribute));
                    return false;
                }
            },
        ]);
    }

    /**
     * Вывод сообщений на экран (консоль)
     * @param bool $success
     */
    private function log($success)
    {
        if ($success) {
            $this->stdout('Success!', Console::FG_GREEN, Console::BOLD);
        } else {
            $this->stderr('Error!', Console::FG_RED, Console::BOLD);
        }
        echo PHP_EOL;
    }
}