<?php
namespace AuthModule;

use AuthModule\Adapter\ModelAdapter;
use AuthModule\Exception\RuntimeException;
use AuthModule\Identity\IdentityObjectInterface;
use AuthModule\Identity\ObjectInterface;
use Zend\Authentication\Adapter\AdapterInterface;
use Zend\Authentication\AuthenticationService as BaseAuthService;
use Zend\Authentication\Result;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerAwareTrait;
use Zend\EventManager\EventManagerInterface;

/**
 * Class AuthenticationService
 */
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
        $events->attach(AuthenticationEvent::EVENT_AUTH, [$this, 'dispatchAuthentication']);
    }

    /**
     * @param AuthenticationEvent $e
     */
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
        $identifiers = [__CLASS__, get_class($this)];
        if (isset($this->eventIdentifier)) {
            if ((is_string($this->eventIdentifier))
                || (is_array($this->eventIdentifier))
                || ($this->eventIdentifier instanceof \Traversable)
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
     * @throws Exception\RuntimeException
     */
    public function getIdentityObject()
    {
        if ($this->hasIdentity()) {
            if (!$this->identityObject) {
                /** @var $modelAdapter ModelAdapter */
                $modelAdapter = $this->getAdapter();
                if ($modelAdapter) {
                    $this->identityObject = $modelAdapter->getIdentityObjectByIdentity($this->getIdentity());
                } else {
                    throw new RuntimeException('Not set adapter in authentication service');
                }
            }
            return $this->identityObject;
        }
        return null;
    }

    /**
     * Authenticates against the supplied adapter
     *
     * @param  AdapterInterface $adapter
     * @return Result
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
