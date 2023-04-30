<?php

namespace Lar\Tagable\Core\Extension;

interface Attributable
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

    /**
     * Value setter.
     *
     * @param mixed $value
     */
    public function setValue($value);
}
