<?php

namespace Lar\Tagable\Core\Elements;

use Lar\Tagable\Core\Extension\Attribute;

class AttributeText extends Attribute
{
    /**
     * Attribute value setter.
     *
     * @param mixed $value
     */
    public function setValue($value): void
    {
        $this->value = $value;
    }
}
