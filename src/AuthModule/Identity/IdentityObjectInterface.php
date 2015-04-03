<?php

namespace AuthModule\Identity;

/**
 * Interface IdentityObjectInterface
 */
interface IdentityObjectInterface
{

    /**
     * @return ObjectInterface|null
     * @throws Exception\RuntimeException
     */
    public function getIdentityObject();

    /**
     * @return ObjectInterface|null
     * @throws Exception\RuntimeException
     */
    public function reloadIdentityObject();
}
