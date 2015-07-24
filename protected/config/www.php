<?php

return array(
    'language' => 'ru',
    'preload' => array('log'),
    'sourceLanguage' => 'ru',
    'defaultController' => 'items',
    'name' => 'СНЯ.ЛИ - База квартир ВКонтакте, снять комнату ВКонтакте, без риелторов',
    'import' => array(
        'ext.eoauth.*',
        'ext.eoauth.lib.*',
        'ext.lightopenid.*',
        'ext.eauth.*',
        'ext.eauth.services.*',
        'application.components.*',
        'application.models.*',
    ),
    'basePath' => dirname(dirname(__FILE__)),
    'params' => array(
        'vendorPath' => dirname(dirname(dirname(__FILE__))) . '/vendor',
        'webRoot' => realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '../../www'),
        'imagesStorage' => '/var/www/snya.li/www/images',
    ),
    'components' => array(
        'db' => require dirname(__FILE__) . '/mysql.php',
        'sphinx' => require dirname(__FILE__) . '/sphinx.php',
        'cache' => array(
            'class' => 'CMemCache',
            'serializer' => false,
            'servers' => array(
                array('host' => '127.0.0.1', 'port' => 11211, 'weight' => 100),
            ),
        ),
        'urlManager' => require dirname(__FILE__) . '/url.php',
        'authManager' => array(
            'class' => 'PhpAuthManager',
            'defaultRoles' => array('guest'),
        ),
        'user' => array(
            'class' => 'WebUser',
            'loginUrl' => array('user/login'),
            'allowAutoLogin' => true,
        ),
        'loid' => array(
            'class' => 'ext.lightopenid.loid',
        ),
        'eauth' => array(
            'class' => 'ext.eauth.EAuth',
            'popup' => true, // Use the popup window instead of redirecting.
            'cache' => false, // Cache component name or false to disable cache. Defaults to 'cache'.
            'cacheExpire' => 0, // Cache lifetime. Defaults to 0 - means unlimited.
            'services' => array(// You can change the providers and their classes.
                'vkontakte' => array(
                    // register your app here: https://vk.com/editapp?act=create&site=1
                    'class' => 'VKontakteOAuthService',
                    'client_id' => '2174767',
                    'client_secret' => 'L9wkEp0HxRv0f0h6jrj3',
                    'title' => 'Войти через vk.com',
                ),
            ),
        ),
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
    ),
);
