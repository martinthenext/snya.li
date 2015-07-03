<?php

return array(
    'urlFormat' => 'path',
    'showScriptName' => false,
    'urlSuffix' => '.html',
    'class' => 'UrlManager',
    'rules' => array(
        '/item/<city[\w\-]+>/<type[\w\-]+>/<link[\w\-]+>_<id\d+>'=>'items/item',
        //'/items/type/<type[\w\-]+>'=>'items/index',
        '/items/<city[\w\-]+>/<type[\w\-]+>'=>'items/index',
        
        '/search/<search[^\/]+>'=>'items/search',
        '/search'=>'items/search',
        
        '/<city[\w\-]+>'=>'items/index',
        //'/items'=>'items/index',
        
        
    )
);
