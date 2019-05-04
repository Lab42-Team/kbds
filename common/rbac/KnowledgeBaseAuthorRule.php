<?php

namespace app\common\rbac;

use yii\rbac\Rule;

class KnowledgeBaseAuthorRule extends Rule
{
    public $name = 'isAuthor';

    /**
     *
     * @param string|integer $user_id - id пользователя
     * @param \yii\rbac\Item $item - роль или разрешение с которым ассоциировано данное правило
     * @param array $params - параметры, переданные в ManagerInterface::checkAccess() (например при вызове проверки)
     * @return bool - a value indicating whether the rule permits the role or permission it is associated with
     */
    public function execute($user_id, $item, $params)
    {
        return isset($params['knowledge-base']) ? $params['knowledge-base']->author == $user_id : false;
    }
}