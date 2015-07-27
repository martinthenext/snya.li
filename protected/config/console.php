<?php
return array(
    'language' => 'ru',
    'preload' => array('log'),
    'sourceLanguage' => 'ru',
    'name' => 'Сня.ли',
    'import' => array(
        'application.components.*',
        'application.models.*',
    ),
    'basePath' => dirname(dirname(__FILE__)),
    'params' => array(
        'vendorPath' => dirname(dirname(__FILE__)) . '/vendor',
        'webRoot' => '/var/www/snya.li/www',
        'imagesStorage' => '/var/www/snya.li/www/images',
    ),
    'components' => array(
        'db' => require dirname(__FILE__) . '/mysql.php',
        'urlManager' => require dirname(__FILE__) . '/url.php',
        'log' => array(
            'class' => 'CLogRouter',
            'routes' => array(
                array(// -- CWebLogRoute ---------------------------
                    'class' => 'CWebLogRoute',
                    'levels' => 'error, warning, trace, profile, info',
                    'enabled' => false,
                ),
                array(// -- CProfileLogRoute -----------------------
                    'class' => 'CProfileLogRoute',
                    'levels' => 'profile',
                    'enabled' => false,
                ),
            ),
        ),
        'cache' => array(
            'class' => 'CFileCache',
            'cachePath' => '/tmp/cache',
        )
    ),
);
