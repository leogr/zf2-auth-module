<?php
namespace AuthModule\Indentity;

interface ObjectInterface
{

    /**
     * @param mixed $credential
     * @return bool
     */
    public function validateCredential($credential);

}