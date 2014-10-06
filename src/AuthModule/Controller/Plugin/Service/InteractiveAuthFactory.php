<?php
namespace AuthModule\Controller\Plugin\Service;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\FactoryInterface;
use AuthModule\AuthenticationService;
use Zend\Authentication\Storage\Session;
use Zend\ServiceManager\AbstractPluginManager;
use AuthModule\Controller\Plugin\InteractiveAuth;
use Zend\Session\SessionManager;

/**
 * Class InteractiveAuthFactory
 */
class InteractiveAuthFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return AuthenticationService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $serviceManager = $serviceLocator;
        if ($serviceManager instanceof AbstractPluginManager) {
            $serviceManager = $serviceManager->getServiceLocator();
        }
        /** @var $authService AuthenticationService */
        $authService = $serviceManager->get('AuthModule\AuthenticationService');
        /** @var $sessionManager SessionManager */
        $sessionManager = $serviceManager->get('Zend\Session\SessionManager');

        return new InteractiveAuth($authService, $sessionManager);
    }
}
