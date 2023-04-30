<?php

namespace Lar\Tagable\Interfaces;

/**
 * Interface AuthUser.
 *
 * @package Lar\Tagable\Interfaces
 */
interface AuthUser
{
    /**
     * Event for authorized users.
     *
     * @return void
     */
    public function auth_user() : void;
}
