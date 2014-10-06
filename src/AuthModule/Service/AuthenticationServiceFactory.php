<?php
namespace AuthModule\Service;

use Zend\Authentication\Adapter\AdapterInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\FactoryInterface;
use AuthModule\AuthenticationService;
use Zend\Authentication\Storage\Session;
use Zend\Session\SessionManager;

/**
 * Class AuthenticationServiceFactory
 */
class AuthenticationServiceFactory implements FactoryInterface
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

        $sessionStorageConfig = [
            'namespace' => __NAMESPACE__,
            'member'    => null,
        ];
        if (isset($config['session_storage']) && is_array($config['session_storage'])) {
            $sessionStorageConfig = array_merge($sessionStorageConfig, $config['session_storage']);
        }

        /** @var $sessionManager SessionManager */
        $sessionManager = $serviceLocator->get('Zend\Session\SessionManager');

        $authService->setStorage(
            new Session($sessionStorageConfig['namespace'], $sessionStorageConfig['member'], $sessionManager)
        );

        if (isset($config['adapter']) && is_string($config['adapter']) && $serviceLocator->has($config['adapter'])) {
            /** @var $adapter AdapterInterface */
            $adapter = $serviceLocator->get($config['adapter']);
            $authService->setAdapter($adapter);
        }

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
            $this->config = [];
            return $this->config;
        }

        $config = $serviceLocator->get('Config');
        if (!isset($config[$this->configKey])
            || !is_array($config[$this->configKey])
        ) {
            $this->config = [];
            return $this->config;
        }

        $this->config = $config[$this->configKey];
        return $this->config;
    }
}
