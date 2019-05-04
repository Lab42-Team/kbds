<?php

return [
    'class' => 'yii\db\Connection',
    'dsn' => 'pgsql:host=localhost;port=5432;dbname=kbds;',
    'username' => 'postgres',
    'password' => 'root',
    'charset' => 'utf8',
    'tablePrefix' => 'kbds_',
    'schemaMap' => [
        'pgsql'=> [
            'class'=>'yii\db\pgsql\Schema',
            'defaultSchema' => 'public'
        ]
    ],
];
