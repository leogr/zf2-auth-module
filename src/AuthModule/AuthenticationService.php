<?php
namespace AuthModule;

use AuthModule\Indentity\IdentityObjectInterface;
use Zend\Authentication\AuthenticationService as BaseAuthService;
use Zend\Authentication\Adapter\AdapterInterface;
use Zend\Authentication\Result;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerAwareTrait;
use AuthModule\Indentity\ObjectInterface;
use Zend\EventManager\EventManagerInterface;

class AuthenticationService extends BaseAuthService implements EventManagerAwareInterface, IdentityObjectInterface
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

    public function dispatchAuthentication(AuthenticationEvent $e)
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
        $identifiers = array(__CLASS__, get_class($this));
        if (isset($this->eventIdentifier)) {
            if ((is_string($this->eventIdentifier))
                || (is_array($this->eventIdentifier))
                || ($this->eventIdentifier instanceof Traversable)
            ) {
                $identifiers = array_unique(array_merge($identifiers, (array) $this->eventIdentifier));
            } elseif (is_object($this->eventIdentifier)) {
                $identifiers[] = $this->eventIdentifier;
            }
            // silently ignore invalid eventIdentifier types
        }
        $events->setIdentifiers($identifiers);
        $this->events = $events;
        $this->attachDefaultListener();
        return $this;
    }

    /**
     * @return ObjectInterface|null
     */
    public function getIdentityObject()
    {
        if ($this->hasIdentity()) {
            if(!$this->identityObject) {
                $this->identityObject = $this->getAdapter()->getIdentityObjectByIdentity($this->getIdentity());
            }
            return $this->identityObject;
        }
    }

    /**
     * Authenticates against the supplied adapter
     *
     * @param  AdapterInterface $adapter
     * @return Result
     * @throws Exception\RuntimeException
     */
    public function authenticate(AdapterInterface $adapter = null)
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