<?php

return array(
    'urlFormat' => 'path',
    'showScriptName' => false,
    'urlSuffix' => '',
    'class' => 'UrlManager',
    'rules' => array(
        'user/login'=>'user/login',
        'items/test'=>'items/test',
        // Страница объявления
        '/<city[\w\-]+>/<type[\w\-]+>/<link[\w\-]+>_<id\d+>'=>'items/item',
        
        // Страница поиска
        '/search/<city[\w\-]+>/<type[\w\-]+>/<search[^\/]+>'=>'items/search',
        '/search/<city[\w\-]+>/<search[^\/]+>'=>'items/search',
        '/search/<search[^\/]+>'=>'items/search',
        '/search'=>'items/search',
        
        // Списки объявлений
        '/<city[\w\-]+>/<type[\w\-]+>'=>'items/index',
        '/<city[\w\-]+>'=>'items/index',
    )
);
