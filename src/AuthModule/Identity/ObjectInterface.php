<?php
namespace AuthModule\Identity;

/**
 * Interface ObjectInterface
 */
interface ObjectInterface
{
    /**
     * @param mixed $credential
     * @return bool
     */
    public function validateCredential($credential);
}
