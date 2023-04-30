<?php

namespace Lar\Tagable\Core\Extension;

use Illuminate\Contracts\Support\Renderable;
use Lar\Tagable\Tag;

class Attribute implements Attributable
{
    /**
     * Attribute value.
     *
     * @var mixed
     */
    protected $value = null;

    /**
     * Parent tag class.
     *
     * @var null | Tag
     */
    protected $tag = null;

    /**
     * Attributable constructor.
     *
     * @param mixed $value
     */
    public function __construct($value = '')
    {
        $this->setValue($value);
    }

    /**
     * Attribute value getter.
     *
     * @return string
     */
    public function getValue(): string
    {
        if ($this->value instanceof Renderable) {
            $this->value = $this->value->render();
        }

        if (is_array($this->value)) {
            return '';
        }

        return (string) $this->value;
    }

    /**
     * Attribute value setter.
     *
     * @param mixed $value
     */
    public function setValue($value): void
    {
        $this->value = $value;
    }

    /**
     * Parent tag setter.
     *
     * @param Tag|null $tag
     * @return Attribute
     */
    public function setTag(?Tag $tag = null)
    {
        $this->tag = $tag;

        return $this;
    }

    public function __debugInfo()
    {
        return ['value' => $this->value];
    }
}
