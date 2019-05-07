<?php

$params = require(__DIR__ . '/params.php');

// console alias
//Yii::setAlias('@console', dirname(dirname(__DIR__)) . '/console');

$config = [
    // site ID
    'id' => 'kbds',
    // site name
    'name' => 'Knowledge Bases Development Service',
    // home page(index) route by default
    'defaultRoute' => 'main/default/index',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],

    // all modules of site
    'modules' => [
        'main' => [
            'class' => 'app\modules\main\Module',
        ],
        'user' => [
            'class' => 'app\modules\user\Module',
        ],
        'knowledge_base' => [
            'class' => 'app\modules\knowledge_base\Module',
        ],
        'software_component' => [
            'class' => 'app\modules\software_component\Module',
        ],
        'project' => [
            'class' => 'app\modules\project\Module',
        ],
        'editor' => [
            'class' => 'app\modules\editor\Module',
        ],
        'api' => [
            'class' => 'app\modules\api\Module',
        ]
    ],

    'components' => [
        'language' => 'ru-RU',
        'request' => [
            'class' => 'app\components\LangRequest',
            // site root directory
			'baseUrl' => '',
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'dplzbspf32',
        ],
		'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'class' => 'app\components\LangUrlManager',
            'rules' => [
                '/' => 'main/default/index',
                'help' => 'main/default/help',
                'contact-us' => 'main/contact-us/index',
                '<_a:error>' => 'main/default/<_a>',
                '<_a:(sing-in|sing-out|sign-up|email-confirm|request-password-reset|password-reset)>' => 'user/default/<_a>',
                '/users/<_u:(list|create)>' => 'user/users/<_u>',
                '/users/<_u:(view|update|update-password|delete)>/<id:\d+>' => 'user/users/<_u>',
                '/user/<_u:(account|update|update-password)>' => 'user/user/<_u>',
                '/user/<_u:delete>/<id:\d+>' => 'user/user/<_u>',
                '/subject-domains/<_sd:(list|create)>' => 'knowledge_base/subject-domains/<_sd>',
                '/subject-domains/<_sd:(view|update|delete)>/<id:\d+>' => 'knowledge_base/subject-domains/<_sd>',
                '/knowledge-bases/<_kb:(list|create)>' => 'knowledge_base/knowledge-bases/<_kb>',
                '/knowledge-bases/<_kb:(view|update|delete|ontology-editor|rvml-editor)>/<id:\d+>' =>
                    'knowledge_base/knowledge-bases/<_kb>',
                '/knowledge-bases/<_kb:(generate-ontology|generate-owl-code|generate-production-model|generate-clips-code)>/<id:\d+>' =>
                    'knowledge_base/knowledge-bases/<_kb>',
                '/knowledge-bases/<_kb:(import-conceptual-model|export-knowledge-base)>/<id:\d+>/<sc:\d+>' =>
                    'knowledge_base/knowledge-bases/<_kb>',
                '/software-components/<_sc:(list|create)>' => 'software_component/software-components/<_sc>',
                '/software-components/<_sc:(view|update|delete|view-tmrl-code)>/<id:\d+>' =>
                    'software_component/software-components/<_sc>',
                '/transformation-models/<_sc:(list|create|add-class-connection|get-class-connection-values|edit-class-connection|delete-class-connection|add-attribute-connection|delete-attribute-connection|check-class-visibility)>' =>
                    'software_component/transformation-models/<_sc>',
                '/transformation-models/<_sc:(view|update|delete|transformation-editor|generate-software-component|view-tmrl-code)>/<id:\d+>' =>
                    'software_component/transformation-models/<_sc>',
                '/metamodels/<_mm:(list|create|add-relation|get-relation-values|edit-relation|delete-relation)>' =>
                    'software_component/metamodels/<_mm>',
                '/metamodels/<_mm:(view|update|delete|import-xml-schema|import-conceptual-model|metamodel-editor)>/<id:\d+>' =>
                    'software_component/metamodels/<_mm>',
                '/my-knowledge-bases/<_mykb:(list|create|add-new-subject-domain)>' => 'project/my-knowledge-bases/<_mykb>',
                '/my-knowledge-bases/<_mykb:(view|update|delete)>/<id:\d+>' => 'project/my-knowledge-bases/<_mykb>',
                '/rvml-editor/<id:\d+>' => 'editor/rvml-editor/index',
                '/rvml-editor/<_rvml:(add-fact-template|add-initial-fact|add-rule-template|add-rule|get-rule-template-parameters|generate-clips-code)>/<id:\d+>' =>
                    'editor/rvml-editor/<_rvml>',
                '/rvml-editor/<_rvml:(edit-fact-template|delete-fact-template|add-fact-template-slot|edit-fact-template-slot|delete-fact-template-slot|edit-initial-fact|delete-initial-fact|edit-fact-slot|edit-rule-template|delete-rule-template|edit-rule|delete-rule|edit-rule-condition|edit-rule-action)>' =>
                    'editor/rvml-editor/<_rvml>',
                '/ontology-editor/<id:\d+>' => 'editor/ontology-editor/index',
                '/api/get-all-modules-list' => 'api/default/get-all-modules-list',
                '/api/get-modules-list/<type:\d+>/<status:\d+>' => 'api/default/get-modules-list',
                '/api/get-all-knowledge-bases-list' => 'api/default/get-all-knowledge-bases-list',
                '/api/get-knowledge-bases-list/<type:\d+>/<status:\d+>' => 'api/default/get-knowledge-bases-list',
                '/api/import-conceptual-model/<id:\d+>/<sc:\d+>' => 'api/default/import-conceptual-model',
                '/api/export-knowledge-base/<id:\d+>' => 'api/default/export-knowledge-base',
            ],
		],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\modules\user\models\User',
            'enableAutoLogin' => true,
            'loginUrl' => ['user/default/sing-in'],
        ],
        'errorHandler' => [
            'errorAction' => 'main/default/error',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            'useFileTransport' => true,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'authManager' => [
            'class' => 'yii\rbac\DbManager',
        ],
        'db' => require(__DIR__ . '/db.php'),
        'i18n' => [
            'translations' => [
                'app' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@app/messages',
                    'forceTranslation' => true,
                    'sourceLanguage' => 'en-US',
                ],
            ],
        ],
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
    ];
}

return $config;