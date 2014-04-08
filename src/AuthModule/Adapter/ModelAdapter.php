<?php
namespace AuthModule\Adapter;

use Zend\Authentication\Result;
use Zend\Authentication\Adapter\AdapterInterface;
use Zend\Authentication\Adapter\ValidatableAdapterInterface;
use AuthModule\Indentity\ModelInterface;
use AuthModule\Indentity\ObjectInterface;
use Zend\Stdlib\ArrayUtils;

class ModelAdapter implements AdapterInterface, ValidatableAdapterInterface
{

    /**
     * @var mixed
     */
    protected $identity;

    /**
     * @var mixed
     */
    protected $credential;

    /**
     * @var ModelInterface
     */
    protected $model;

    public function __construct(ModelInterface $model)
    {
        $this->model = $model;
    }

    /**
     * Returns the identity of the account being authenticated, or
     * NULL if none is set.
     *
     * @return mixed
     */
    public function getIdentity()
    {
        return $this->identity;
    }

    /**
     * Sets the identity for binding
     *
     * @param  mixed                       $identity
     * @return $this
    */
    public function setIdentity($identity)
    {
        $this->identity = $identity;
        return $this;
    }

    /**
     * Returns the credential of the account being authenticated, or
     * NULL if none is set.
     *
     * @return mixed
    */
    public function getCredential()
    {
        return $this->credential;
    }

    /**
     * Sets the credential for binding
     *
     * @param  mixed                       $credential
     * @return $this
    */
    public function setCredential($credential)
    {
        $this->credential = $credential;
        return $this;
    }


    /**
     * Performs an authentication attempt
     *
     * @return \Zend\Authentication\Result
     * @throws \Zend\Authentication\Adapter\Exception\ExceptionInterface If authentication cannot be performed
     */
    public function authenticate()
    {
        $identity = $this->getIdentity();
        $results = $this->model->findByIdentity($identity);
        $results = new \ArrayIterator($results);

        $resCount = $results->count($results);

        if ($resCount > 1) {
            return new Result(Result::FAILURE_IDENTITY_AMBIGUOUS, $identity);
        }

        if ($resCount == 0) {
            return new Result(Result::FAILURE_IDENTITY_NOT_FOUND, $identity);
        }

        $identityObject = $results->current();

        if ($identityObject instanceof ObjectInterface) {
            if ($identityObject->validateCredential($this->getCredential())) {
                return new Result(Result::SUCCESS, $identity);
            }//else
            return new Result(Result::FAILURE, $identity);
        }

        return new Result(Result::FAILURE_UNCATEGORIZED, $identity);
    }

}