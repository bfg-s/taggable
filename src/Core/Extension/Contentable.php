<?php

namespace Lar\Tagable\Core\Extension;

interface Contentable
{
    /**
     * Attributable constructor.
     *
     * @param mixed $value
     */
    public function __construct($value = '');

    /**
     * Attribute value getter.
     *
     * @return string
     */
    public function getValue(): string;
}
