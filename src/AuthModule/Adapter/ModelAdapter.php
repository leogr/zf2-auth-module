<?php
namespace AuthModule\Adapter;

use AuthModule\Exception\ExceptionInterface;
use AuthModule\Identity\ModelInterface;
use AuthModule\Identity\ObjectInterface;
use Zend\Authentication\Adapter\AdapterInterface;
use Zend\Authentication\Adapter\ValidatableAdapterInterface;
use Zend\Authentication\Result;

/**
 * Class ModelAdapter
 */
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

    /**
     * Ctor
     *
     * @param ModelInterface $model
     */
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
     * @param  mixed $identity
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
     * @param  mixed $credential
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
     * @throws ExceptionInterface If authentication cannot be performed
     * @return Result
     */
    public function authenticate()
    {
        $identity = $this->getIdentity();
        $results = $this->model->findByIdentity($identity);

        $identityObject = null;
        $count = 0;
        foreach ($results as $identityObject) {
            if ($count > 1) {
                return new Result(
                    Result::FAILURE_IDENTITY_AMBIGUOUS,
                    $identity,
                    ['More than one record matches the supplied identity.']
                );
            }
            $count++;
        }

        if ($count == 0) {
            return new Result(
                Result::FAILURE_IDENTITY_NOT_FOUND,
                $identity,
                ['A record with the supplied identity could not be found.']
            );
        }

        if ($identityObject instanceof ObjectInterface) {
            if ($identityObject->validateCredential($this->getCredential())) {
                return new Result(Result::SUCCESS, $identity);
            } // else
            return new Result(
                Result::FAILURE_CREDENTIAL_INVALID,
                $identity,
                ['wrong password']
            );
        }

        return new Result(
            Result::FAILURE_UNCATEGORIZED,
            $identity,
            ['generic error']
        );
    }

    /**
     * @param $identity
     * @return ObjectInterface|null
     */
    public function getIdentityObjectByIdentity($identity)
    {
        if (!empty($identity)) {
            $results = $this->model->findByIdentity($identity);
            $objIdentity = $results->current(); // FIXME: strong assumption: try current($results)
            if ($objIdentity instanceof ObjectInterface) {
                return $objIdentity;
            }
        }
        return null;
    }
}
