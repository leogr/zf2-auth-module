<?php
return [
    'service_manager' => [
        'factories' => [
            'AuthModule\AuthenticationService' => 'AuthModule\Service\InteractiveAuthServiceFactory'
        ]
    ],
    'controller_plugins' => [
        'factories' => [
            'interactiveAuth' => 'AuthModule\Controller\Plugin\Service\InteractiveAuthFactory',
        ],
    ],
];
