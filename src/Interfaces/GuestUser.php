<?php

namespace Lar\Tagable\Interfaces;

/**
 * Interface GuestUser.
 *
 * @package Lar\Tagable\Interfaces
 */
interface GuestUser
{
    /**
     * Event for unauthorized users.
     *
     * @return void
     */
    public function guest_user() : void;
}
