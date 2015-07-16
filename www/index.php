<?php

include_once dirname(dirname(__FILE__)) . '/vendor/autoload.php';
$config = dirname(dirname(__FILE__)) . '/protected/config/www.php';

// удалить следующую строку в режиме production
defined('YII_DEBUG') or define('YII_DEBUG', preg_match("/^dev\./isu", $_SERVER['HTTP_HOST']));

date_default_timezone_set('Europe/Moscow');
Yii::createWebApplication($config)->run();
