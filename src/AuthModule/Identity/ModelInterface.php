<?php
namespace AuthModule\Identity;

/**
 * Interface ModelInterface
 */
interface ModelInterface
{
    /**
     * @param mixed $identity
     * @return ObjectInterface[]
     */
    public function findByIdentity($identity);
}
