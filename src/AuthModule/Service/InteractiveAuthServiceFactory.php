<?php
namespace AuthModule\Service;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\FactoryInterface;
use AuthModule\AuthenticationService;
use Zend\Authentication\Storage\Session;

/**
 * Class InteractiveAuthServiceFactory
 */
class InteractiveAuthServiceFactory implements FactoryInterface
{

    /**
     * Config Key
     *
     * @var string
     */
    protected $configKey = 'authentication';

    /**
     * Config
     *
     * @var array
     */
    protected $config;

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return AuthenticationService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $this->getConfig($serviceLocator);
        $authService = new AuthenticationService();

        $sessionStorageConfig = array(
            'namespace' => __NAMESPACE__,
            'member'    => null,
        );
        if (isset($config['session_storage']) && is_array($config['session_storage'])) {
           $sessionStorageConfig = array_merge($sessionStorageConfig, $config['session_storage']);
        }
        $sessionManager = $serviceLocator->get('Zend\Session\SessionManager');

        $authService->setStorage(new Session(
            $sessionStorageConfig['namespace'], $sessionStorageConfig['member'], $sessionManager
        ));

        return $authService;
    }

    /**
     * Get auth configuration, if any
     *
     * @param  ServiceLocatorInterface $serviceLocator
     * @return array
     */
    protected function getConfig(ServiceLocatorInterface $serviceLocator)
    {
        if ($this->config !== null) {
            return $this->config;
        }

        if (!$serviceLocator->has('Config')) {
            $this->config = array();
            return $this->config;
        }

        $config = $serviceLocator->get('Config');
        if (!isset($config[$this->configKey])
            || !is_array($config[$this->configKey])
        ) {
            $this->config = array();
            return $this->config;
        }

        $this->config = $config[$this->configKey];
        return $this->config;
    }
}
