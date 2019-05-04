<?php

namespace app\commands;

use Yii;
use yii\helpers\Console;
use yii\console\Controller;
use app\common\rbac\KnowledgeBaseAuthorRule;

/**
 * RbacController реализует консольную команду для создания и назначения ролей пользователям по умолчанию.
 * @package app\commands
 */
class RbacController extends Controller
{
    /**
     * Инициализация команды.
     */
    public function actionIndex()
    {
        echo 'yii rbac/init' . PHP_EOL;
    }

    /**
     * Инициализатор RBAC.
     */
    public function actionInit() {
        $auth = Yii::$app->authManager;
        // Удаление старых данных из БД
        $auth->removeAll();

        /* Создание ролей */
        // Создание роли администратора и разработчика
        $admin = $auth->createRole('admin');
        $developer = $auth->createRole('developer');
        // Запись ролей в БД
        $auth->add($admin);
        $auth->add($developer);
        // Роль admin наследует роль developer
        $auth->addChild($admin, $developer);

        /* Создание правил проверки */
        // Создание правила проверки авторства БЗ
        $knowledge_base_author_rule = new KnowledgeBaseAuthorRule;
        // Запись правила в БД
        $auth->add($knowledge_base_author_rule);

        /* Создание разрешений */
        // Создание разрешения «Просмотр собственной БЗ»
        $view_own_knowledge_base = $auth->createPermission('viewOwnKnowledgeBase');
        // Создание разрешения «Редактирование собственной БЗ»
        $update_own_knowledge_base = $auth->createPermission('updateOwnKnowledgeBase');
        // Создание разрешения «Удаление собственной БЗ»
        $delete_own_knowledge_base = $auth->createPermission('deleteOwnKnowledgeBase');

        // Указание правил для разрешений
        $view_own_knowledge_base->ruleName = $knowledge_base_author_rule->name;
        $update_own_knowledge_base->ruleName = $knowledge_base_author_rule->name;
        $delete_own_knowledge_base->ruleName = $knowledge_base_author_rule->name;
        // Запись всех разрешений в БД
        $auth->add($view_own_knowledge_base);
        $auth->add($update_own_knowledge_base);
        $auth->add($delete_own_knowledge_base);
        // Присваивание разрешений разработчику
        $auth->addChild($developer, $view_own_knowledge_base);
        $auth->addChild($developer, $update_own_knowledge_base);
        $auth->addChild($developer, $delete_own_knowledge_base);

        // Вывод сообщения
        $this->stdout('New RBAC data are created!', Console::FG_GREEN, Console::BOLD);
    }
}