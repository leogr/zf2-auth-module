<?php
namespace AuthModule\Controller\Plugin;

use AuthModule\AuthenticationEvent;
use AuthModule\AuthenticationService;
use AuthModule\Exception\RuntimeException;
use Zend\Authentication\Adapter\AdapterInterface;
use Zend\Authentication\Adapter\ValidatableAdapterInterface;
use Zend\Authentication\Result;
use Zend\Authentication\Storage\Session;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\Session\SessionManager;

/**
 * Class InteractiveAuth
 */
class InteractiveAuth extends AbstractPlugin
{
    /**
     * @var AuthenticationService
     */
    protected $authService;

    /**
     * @var SessionManager
     */
    protected $sessionManager;

    /**
     * @var bool
     */
    protected $rememberMe = false;

    /**
     * @param AuthenticationService $authService
     * @param SessionManager $sessionManager
     * @throws RuntimeException
     */
    public function __construct(AuthenticationService $authService, SessionManager $sessionManager)
    {
        if (!$authService->getStorage() instanceof Session) {
            throw new RuntimeException(__CLASS__ . ' requires SessionStorage');
        }

        $this->authService = $authService;
        $this->sessionManager = $sessionManager;
        $this->attachDefaultListener();
    }

    /**
     * @param string $flag
     * @return boolean
     */
    public function rememberMe($flag = null)
    {
        if (null === $flag) {
            return $this->rememberMe;
        }

        $this->rememberMe = (bool) $flag;
        return $this->rememberMe;
    }

    /**
     *
     */
    protected function attachDefaultListener()
    {
        $this->authService->getEventManager()->attach(AuthenticationEvent::EVENT_AUTH, [$this, 'authListener'], -1000);
    }

    /**
     * @param AuthenticationEvent $e
     */
    public function authListener(AuthenticationEvent $e)
    {
        $result = $e->getResult();

        if ($result->isValid()) {
            if ($this->rememberMe) {
                $this->sessionManager->rememberMe();
            } else {
                $this->sessionManager->forgetMe();
            }
            $this->sessionManager->writeClose();
        }
    }

    /**
     * @return AuthenticationService
     */
    public function getAuthenticationService()
    {
        return $this->authService;
    }

    /**
     * @return SessionManager
     */
    public function getSessionManager()
    {
        return $this->sessionManager;
    }

    /**
     * @param string $identity
     * @param string $credential
     * @throws RuntimeException
     * @return Result
     */
    public function login($identity, $credential)
    {
        $authAdapter = $this->authService->getAdapter();

        if (!$authAdapter instanceof ValidatableAdapterInterface) {
            throw new RuntimeException(__CLASS__ . ' requires ValidatableAdapterInterface');
        }

        $authAdapter->setIdentity($identity)
                    ->setCredential($credential);

        return $this->authService->authenticate();
    }

    /**
     *
     */
    public function logout()
    {
        $this->authService->clearIdentity();
        $this->sessionManager->destroy([
            'send_expire_cookie'    => true,
            'clear_storage'         => true,
        ]);
    }

    /**
     * @param AdapterInterface $adapter
     * @return Result
     */
    public function connect(AdapterInterface $adapter)
    {
        return $this->authService->authenticate($adapter);
    }
}
