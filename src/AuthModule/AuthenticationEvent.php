<?php
namespace AuthModule;

use Zend\EventManager\Event;
use Zend\Authentication\Adapter\AdapterInterface;
use Zend\Authentication\Result;

class AuthenticationEvent extends Event
{

    /**#@+
     * Events triggered by eventmanager
     */
    const EVENT_AUTH     = 'auth';
    /**#@-*/

    /**
     * @var AdapterInterface
     */
    protected $adapter;

    /**
     * @var Result
     */
    protected $result;

    /**
     * @param AdapterInterface $adapter
     * @return $this
     */
    public function setAdapter(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
        return $this;
    }

    /**
     * @return AdapterInterface
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * @param Result $result
     * @return $this
     */
    public function setResult(Result $result)
    {
        $this->result = $result;
        return $this;
    }

    /**
     * @return Result
     */
    public function getResult()
    {
        return $this->result;
    }

}
