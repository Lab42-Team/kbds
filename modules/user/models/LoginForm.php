<?php

namespace app\modules\user\models;

use Yii;
use yii\base\Model;

/**
 * LoginForm is the model behind the login form.
 */
class LoginForm extends Model
{
    public $username;          // Имя пользователя
    public $password;          // Пароль пользователя
    public $rememberMe = true; // Запомнить пользователя
    private $_user = false;    // Вычисляемое поле (пользователь)

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['username', 'password'], 'required'],
            ['rememberMe', 'boolean'],
            ['password', 'validatePassword'],
        ];
    }

    /**
     * @return array customized attribute labels
     */
    public function attributeLabels()
    {
        return [
            'username' => Yii::t('app', 'LOGIN_FORM_USERNAME'),
            'password' => Yii::t('app', 'LOGIN_FORM_PASSWORD'),
            'rememberMe' => Yii::t('app', 'LOGIN_FORM_REMEMBER_ME'),
        ];
    }

    /**
     * Validates the username and password.
     * This method serves as the inline validation for password.
     */
    public function validatePassword()
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();

            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError('password', Yii::t('app', 'LOGIN_FORM_MESSAGE_INCORRECT_USERNAME_OR_PASSWORD'));
            } elseif ($user && $user->status == User::STATUS_BLOCKED) {
                $this->addError('username', Yii::t('app', 'LOGIN_FORM_MESSAGE_BLOCKED_ACCOUNT'));
            } elseif ($user && $user->status == User::STATUS_WAIT) {
                $this->addError('username', Yii::t('app', 'LOGIN_FORM_MESSAGE_NOT_CONFIRMED_ACCOUNT'));
            }
        }
    }

    /**
     * Logs in a user using the provided username and password.
     * @return boolean whether the user is logged in successfully
     */
    public function login()
    {
        if ($this->validate()) {
            return Yii::$app->user->login($this->getUser(), $this->rememberMe ? 3600*24*30 : 0);
        } else {
            return false;
        }
    }

    /**
     * Finds user by [[username]].
     * @return User|null
     */
    public function getUser()
    {
        if ($this->_user === false) {
            $this->_user = User::findByUsername($this->username);
        }

        return $this->_user;
    }
}