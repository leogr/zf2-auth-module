<?php
namespace AuthModule\Indentity;

interface ModelInterface
{

    /**
     * @param mixed $identity
     * @return ObjectInterface[]
     */
    public function findByIdentity($identity);

}