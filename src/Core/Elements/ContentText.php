<?php

namespace Lar\Tagable\Core\Elements;

use Lar\Tagable\Core\Extension\Content;

/**
 * Class ContentText.
 *
 * @package Lar\Tagable\Core
 */
class ContentText extends Content
{
    /**
     * Attribute value setter.
     *
     * @param mixed $value
     */
    public function setValue($value): void
    {
        if (is_array($value)) {
            $value = implode(' ', $value);
        }
        $this->value = $value;
    }
}
