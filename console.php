<?php

include_once dirname(__FILE__) . '/vendor/autoload.php';
$config = dirname(__FILE__) . '/protected/config/console.php';

// удалить следующую строку в режиме production
defined('YII_DEBUG') or define('YII_DEBUG', true);

date_default_timezone_set('Europe/Moscow');
Yii::createConsoleApplication($config)->run();
