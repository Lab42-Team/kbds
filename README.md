Knowledge Bases Development Service (KBDS)
============================

KBDS is based on [Yii 2](http://www.yiiframework.com/) Basic Project Template.

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
      vendor/             contains dependent 3rd-party packages (yii2 framework)
      views/              contains main view file for the Web application
      web/                contains the entry script and Web resources



REQUIREMENTS
------------

The minimum requirement by this project template that your Web server supports PHP 5.4.0.


INSTALLATION
------------

### Download

Extract the archive file downloaded from [bitbucket.org](https://bitbucket.org/Led_Zeppelin/kbds/downloads) this directory.

You can then access the application through the following URL:

~~~
http://localhost/
~~~


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
