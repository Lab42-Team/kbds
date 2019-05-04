<?php

namespace app\modules\user\models;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\web\IdentityInterface;
use app\modules\knowledge_base\models\KnowledgeBase;

/**
 * This is the model class for table "{{%user}}".
 *
 * @property integer $id
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $username
 * @property string $auth_key
 * @property string $email_confirm_token
 * @property string $password_hash
 * @property string $password_reset_token
 * @property string $email
 * @property integer $status
 * @property integer $first_name
 * @property integer $last_name
 * @property integer $middle_name
 *
 * @property KnowledgeBase[] $knowledgeBases
 */
class User extends ActiveRecord implements IdentityInterface
{
    const STATUS_BLOCKED = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_WAIT = 2;

    const ROLE_DEVELOPER = 'developer';
    const ROLE_ADMIN = 'admin';

    public $password;
    public $role;

    /**
     * @return string table name
     */
    public static function tableName()
    {
        return '{{%user}}';
    }

    /**
     * @return array the validation rules
     */
    public function rules()
    {
        return [
            ['username', 'required'],
            ['username', 'match', 'pattern' => '#^[\w_-]+$#i'],
            ['username', 'string', 'min' => 2, 'max' => 255],
            ['username', 'unique', 'targetClass' => self::className(),
                'message' => Yii::t('app', 'USER_MODEL_MESSAGE_USERNAME')],

            ['email', 'required'],
            ['email', 'email'],
            ['email', 'string', 'max' => 255],
            ['email', 'unique', 'targetClass' => self::className(),
                'message' => Yii::t('app', 'USER_MODEL_MESSAGE_EMAIL')],

            ['status', 'integer'],
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => array_keys(self::getStatusesArray())],
            ['status', 'compare', 'compareValue' => self::STATUS_ACTIVE, 'operator' => '==',
                'message' => Yii::t('app', 'USER_MODEL_MESSAGE_UPDATED_STATUS'), 'on' => 'update_admin'],

            ['role', 'default', 'value' => self::ROLE_DEVELOPER],
            ['role', 'in', 'range' => array_keys(self::getRolesArray())],
            ['role', 'compare', 'compareValue' => self::ROLE_ADMIN, 'operator' => '==',
                'message' => Yii::t('app', 'USER_MODEL_MESSAGE_UPDATED_ROLE'), 'on' => 'update_admin'],

            [['first_name', 'last_name', 'middle_name'], 'default', 'value' => null],
            [['first_name', 'last_name', 'middle_name'], 'match', 'pattern' => '/^[A-Za-zА-Яа-яs,]+$/u'],
            [['first_name', 'last_name', 'middle_name'], 'string', 'min' => 2, 'max' => 50],

            ['password', 'required', 'on' => 'create_and_update_password_hash'],
            ['password', 'string', 'min' => 6, 'on' => 'create_and_update_password_hash'],
        ];
    }

    /**
     * @return array customized attribute labels
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'USER_MODEL_ID'),
            'created_at' => Yii::t('app', 'USER_MODEL_CREATED_AT'),
            'updated_at' => Yii::t('app', 'USER_MODEL_UPDATED_AT'),
            'username' => Yii::t('app', 'USER_MODEL_USERNAME'),
            'auth_key' => Yii::t('app', 'USER_MODEL_AUTH_KEY'),
            'email_confirm_token' => Yii::t('app', 'USER_MODEL_EMAIL_CONFIRM_TOKEN'),
            'password_hash' => Yii::t('app', 'USER_MODEL_PASSWORD_HASH'),
            'password_reset_token' => Yii::t('app', 'USER_MODEL_PASSWORD_RESET_TOKEN'),
            'email' => Yii::t('app', 'USER_MODEL_EMAIL'),
            'status' => Yii::t('app', 'USER_MODEL_STATUS'),
            'role' => Yii::t('app', 'USER_MODEL_ROLE'),
            'password' => Yii::t('app', 'USER_MODEL_PASSWORD'),
            'first_name' => Yii::t('app', 'USER_MODEL_FIRST_NAME'),
            'last_name' => Yii::t('app', 'USER_MODEL_LAST_NAME'),
            'middle_name' => Yii::t('app', 'USER_MODEL_MIDDLE_NAME'),
        ];
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    /**
     * Поиск активного пользователя по идентификатору.
     * @param int|string $id
     * @return null|static
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('findIdentityByAccessToken is not implemented.');
    }

    /**
     * Поиск пользователя по имени.
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username]);
    }

    /**
     * Получить id пользователя.
     * @return mixed
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * Получить ключ аутентификации.
     * @return string
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * Проверка ключа аутентификации.
     * @param string $authKey
     * @return bool
     */
    public function validateAuthKey($authKey)
    {
        return $this->auth_key === $authKey;
    }

    /**
     * Генерирование ключа аутентификации при активации "запомнить меня".
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Проверка пароля.
     * @param string $password password to validate
     * @return boolean if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Установка пароля.
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Finds out if password reset token is valid.
     * @param string $token password reset token
     * @return boolean
     */
    public static function isPasswordResetTokenValid($token)
    {
        if(empty($token))
            return false;

        $expire = Yii::$app->params['user.passwordResetTokenExpire'];
        $parts = explode('_', $token);
        $timestamp = (int) end($parts);

        return $timestamp + $expire >= time();
    }

    /**
     * Finds user by password reset token.
     * @param string $token password reset token
     * @return static|null
     */
    public static function findByPasswordResetToken($token)
    {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }
        return static::findOne([
            'password_reset_token' => $token,
            'status' => self::STATUS_ACTIVE,
        ]);
    }

    /**
     * Generates new password reset token.
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Removes password reset token.
     */
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }

    /**
     * @param string $email_confirm_token
     * @return static|null
     */
    public static function findByEmailConfirmToken($email_confirm_token)
    {
        return static::findOne(['email_confirm_token' => $email_confirm_token, 'status' => self::STATUS_WAIT]);
    }

    /**
     * Generates email confirmation token.
     */
    public function generateEmailConfirmToken()
    {
        $this->email_confirm_token = Yii::$app->security->generateRandomString();
    }

    /**
     * Removes email confirmation token.
     */
    public function removeEmailConfirmToken()
    {
        $this->email_confirm_token = null;
    }

    /**
     * Получение списка статусов пользователей.
     * @return array - массив всех статусов пользователей
     */
    public static function getStatusesArray()
    {
        return [
            self::STATUS_BLOCKED => Yii::t('app', 'USER_MODEL_STATUS_BLOCKED'),
            self::STATUS_ACTIVE => Yii::t('app', 'USER_MODEL_STATUS_ACTIVE'),
            self::STATUS_WAIT => Yii::t('app', 'USER_MODEL_STATUS_WAIT'),
        ];
    }

    /**
     * Получение списка раскрашенных статусов пользователей.
     * @return array - массив всех раскрашенных статусов пользователей
     */
    public static function getColoredStatusesArray()
    {
        return [
            self::STATUS_BLOCKED => '<span class="status-blocked">' .
                Yii::t('app', 'USER_MODEL_STATUS_BLOCKED') . '</span>',
            self::STATUS_ACTIVE => '<span class="status-active">' .
                Yii::t('app', 'USER_MODEL_STATUS_ACTIVE') . '</span>',
            self::STATUS_WAIT => '<span class="status-wait">' .
                Yii::t('app', 'USER_MODEL_STATUS_WAIT') . '</span>',
        ];
    }

    /**
     * Получение названия статуса пользователя.
     * @return mixed
     */
    public function getStatusName()
    {
        return ArrayHelper::getValue(self::getColoredStatusesArray(), $this->status);
    }

    /**
     * Получение роли пользователя.
     * @return mixed
     */
    public function getRoleName()
    {
        $roles = Yii::$app->authManager->getRolesByUser($this->id);
        $this->role = '';
        foreach($roles as $key => $value)
            $this->role = $key;
        return ArrayHelper::getValue(self::getRolesArray(), $this->role);
    }

    /**
     * Получение списка ролей пользователя.
     * @return array
     */
    public static function getRolesArray()
    {
        return [
            self::ROLE_DEVELOPER => Yii::t('app', 'USER_MODEL_ROLE_DEVELOPER'),
            self::ROLE_ADMIN => Yii::t('app', 'USER_MODEL_ROLE_ADMIN'),
        ];
    }

    /**
     * Генерация ключа автоматической аутентификации перед записью в БД.
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($insert)
                $this->generateAuthKey();

            return true;
        }

        return false;
    }

    /**
     * Получение списка всех пользователей.
     * @return array - массив всех записей из таблицы user
     */
    public static function getAllUsersArray()
    {
        return ArrayHelper::map(self::find()->all(), 'id', 'username');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getKnowledgeBases()
    {
        return $this->hasMany(KnowledgeBase::className(), ['subject_domain' => 'id']);
    }
}