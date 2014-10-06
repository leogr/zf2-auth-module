<?php
return [
    'service_manager' => [
        'factories' => [
            'AuthModule\AuthenticationService' => 'AuthModule\Service\AuthenticationServiceFactory'
        ],
        'aliases' => [
            'Zend\Authentication\AuthenticationService' => 'AuthModule\AuthenticationService'
        ],
    ],
    'controller_plugins' => [
        'factories' => [
            'interactiveAuth' => 'AuthModule\Controller\Plugin\Service\InteractiveAuthFactory',
        ],
    ],
];
