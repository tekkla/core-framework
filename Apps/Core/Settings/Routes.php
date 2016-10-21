<?php
/**
 * Routes.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
return [
    'index' => [
        'route' => '../',
        'target' => [
            'controller' => 'index',
            'action' => 'index'
        ]
    ],
    'login' => [
        'route' => '/[logout|login:action]',
        'target' => [
            'controller' => 'login'
        ]
    ],
    'register' => [
        'route' => '/register',
        'target' => [
            'controller' => 'user',
            'action' => 'register'
        ]
    ],
    'activate' => [
        'route' => '/[activate|deny:action]/[:key]',
        'target' => [
            'controller' => 'user'
        ]
    ],
    'admin' => [
        'method' => 'GET',
        'route' => '/admin',
        'target' => [
            'controller' => 'admin',
            'action' => 'admin'
        ]
    ],
    'config' => [
        'route' => '/admin/[mvc:app_name]/config'
    ],
    'config.group' => [
        'method' => 'GET|POST',
        'route' => '/admin/[mvc:app_name]/config/[a:group_name]'
    ]
];