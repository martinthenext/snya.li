<?php

include_once dirname(dirname(__FILE__)) . '/vendor/autoload.php';
$config = dirname(dirname(__FILE__)) . '/protected/config/www.php';


defined('YII_DEBUG') or define('YII_DEBUG', preg_match("/^dev\./isu", $_SERVER['HTTP_HOST']));

if (YII_DEBUG) {
    ini_set('display_errors', 'on');
    error_reporting(E_ALL);
}

date_default_timezone_set('Europe/Moscow');
Yii::createWebApplication($config)->run();
