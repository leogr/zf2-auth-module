<?php
namespace AuthModule;

use Zend\Authentication\AuthenticationService as BaseAuthService;
use Zend\Authentication\Adapter\AdapterInterface;
use Zend\Authentication\Result;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerAwareTrait;
use AuthModule\Indentity\ObjectInterface;
use Zend\EventManager\EventManagerInterface;

class AuthenticationService extends BaseAuthService implements EventManagerAwareInterface
{

    use EventManagerAwareTrait;


    /**
     * @var ObjectInterface
     */
    protected $identityObject;


    /**
     * @var AuthenticationEvent
     */
    protected $event;

    /**
     * @return AuthenticationEvent
     */
    public function getEvent()
    {
        if (!$this->event) {
            $this->event = new AuthenticationEvent();
            $this->event->setTarget($this);
        }
        return $this->event;
    }

    protected function attachDefaultListener()
    {
        $events = $this->getEventManager();
        $events->attach(AuthenticationEvent::EVENT_AUTH, array($this, 'dispatchAuthentication'));
    }

    protected function dispatchAuthentication(AuthenticationEvent $e)
    {
        $e->setResult(parent::authenticate($e->getAdapter()));
    }

    /**
     * Set the event manager instance used by this context
     *
     * @param  EventManagerInterface $events
     * @return $this
     */
    public function setEventManager(EventManagerInterface $events)
    {
        parent::setEventManager($eventManager);
        $this->attachDefaultListener();
        return $this;
    }

    /**
     * @param ObjectInterface $identityObject
     * @return $this
     */
    public function setIdentityObject(ObjectInterface $identityObject)
    {
        $this->identityObject = $identityObject;
        return $this;
    }

    /**
     * @return ObjectInterface|null
     */
    public function getIdentityObject()
    {
        if ($this->hasIdentity()) {
            return $this->identityObject;
        }
        return null;
    }

    /**
     * Authenticates against the supplied adapter
     *
     * @param  Adapter\AdapterInterface $adapter
     * @return Result
     * @throws Exception\RuntimeException
     */
    public function authenticate(Adapter\AdapterInterface $adapter = null)
    {
        $event = clone $this->getEvent();
        $event->setName(AuthenticationEvent::EVENT_AUTH);

        if (!$adapter) {
            $adapter = $this->getAdapter();
        }

        if ($adapter) {
            $event->setAdapter($adapter);
        }

        $this->getEventManager()->trigger($event);

        return $event->getResult();
    }

}