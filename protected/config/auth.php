<?php

return array(
    'guest' => array(
        'type' => CAuthItem::TYPE_ROLE,
        'description' => 'Guest',
        'data' => null,
        'bizRule' => null,
    ),
    'user' => array(
        'type' => CAuthItem::TYPE_ROLE,
        'description' => 'Пользователь',
        'children' => array(
            'guest', // унаследуемся от гостя
        ),
        'data' => null,
        'bizRule' => null,
    ),
    'moderator' => array(
        'type' => CAuthItem::TYPE_ROLE,
        'description' => 'Модератор',
        'children' => array(
            'user', // позволим модератору всё, что позволено пользователю
        ),
        'data' => null,
        'bizRule' => null,
    ),
    'admin' => array(
        'type' => CAuthItem::TYPE_ROLE,
        'description' => 'Админ',
        'children' => array(
            'moderator', // позволим админу всё, что позволено модератору
        ),
        'data' => null,
        'bizRule' => null,
    ),
);
