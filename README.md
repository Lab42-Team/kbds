Knowledge Bases Development Service (KBDS)
============================

KBDS is based on [Yii 2](http://www.yiiframework.com/) framework.

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

The minimum requirement by this project template that your Web server supports PHP 7.x.


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
    'dsn' => 'mysql:host=localhost;dbname=yii2basic',
    'username' => 'root',
    'password' => '1234',
    'charset' => 'utf8',
];
```

### Other

Also check and edit the other files in the `config/` directory to customize your application.