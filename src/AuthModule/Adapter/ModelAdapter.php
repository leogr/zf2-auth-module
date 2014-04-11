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

    public function getIdentityObjectByIdentity($identity)
    {
        if (!empty($identity)) {
            $results = $this->model->findByIdentity($identity);
            $objIdentity = current($results);
            if($objIdentity instanceof ObjectInterface) {
                return $objIdentity;
            }
        }
        return null;
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

        $identityObject = null;
        $count = 0;
        foreach ($results as $identityObject) {
            if ($count > 1) {
                return new Result(Result::FAILURE_IDENTITY_AMBIGUOUS,
                    $identity,
                    array('More than one record matches the supplied identity.')
                );
            }
            $count++;
        }

        if ($count == 0) {
            return new Result(Result::FAILURE_IDENTITY_NOT_FOUND,
                $identity,
                array('A record with the supplied identity could not be found.')
            );
        }

        if ($identityObject instanceof ObjectInterface) {
            if ($identityObject->validateCredential($this->getCredential())) {
                return new Result(Result::SUCCESS, $identity);
            }//else
            return new Result(Result::FAILURE_CREDENTIAL_INVALID,
                $identity,
                array('wrong password')
            );
        }

        return new Result(Result::FAILURE_UNCATEGORIZED,
            $identity,
            array('generic error')
        );
    }

}
