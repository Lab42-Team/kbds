Knowledge Bases Development System
============================

<b>The Knowledge Bases Development System (KBDS)</b> is a web-oriented platform for prototyping rule-based knowledge bases by using different conceptual models (e.g., UML, concept maps).

KBDS is based on the [PHP 7](https://www.php.net/releases/7_0_0.php) and the [Yii 2 Framework](http://www.yiiframework.com/).

[![Latest Stable Version](https://poser.pugx.org/yiisoft/yii2-app-basic/v/stable.png)](https://packagist.org/packages/yiisoft/yii2-app-basic)
[![Total Downloads](https://poser.pugx.org/yiisoft/yii2-app-basic/downloads.png)](https://packagist.org/packages/yiisoft/yii2-app-basic)
[![Build Status](https://travis-ci.org/yiisoft/yii2-app-basic.svg?branch=master)](https://travis-ci.org/yiisoft/yii2-app-basic)

DIRECTORY STRUCTURE
-------------------

      assets/             contains assets definition
      commands/           contains console commands (controllers)
      common/             contains RBAC rules
      components/         contains all custom components and widgets for the Web application
      config/             contains application configurations
      mail/               contains view files for e-mails
      messages/           contains files with the translation (en|ru)
      migrations/         contains migrations definition (tables)
      modules/            contains structured MVC-components for the Web application
      tests/              contains various tests for the basic application
      views/              contains main view file for the Web application
      web/                contains the entry script and Web resources


REQUIREMENTS
------------

The minimum requirement by this project that your Web server supports <b>PHP 7.0</b>, <b>jsPlumb 2.12</b>, <b>PostgreSQL 9.0</b>.


INSTALLATION
------------

### Download

Extract the archive file downloaded from [github.com](https://github.com/LedZeppe1in/kbds/archive/master.zip) this directory.


CONFIGURATION
-------------

### Database

Edit the file `config/db.php` with real data, for example:

```php
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
```

### Other

Also, check and edit the other files in the `config/` directory to customize your application.