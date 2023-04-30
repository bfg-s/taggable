<?php

namespace Lar\Tagable\Core\Extension;

use Lar\Tagable\Tag;

class Content implements Contentable
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
     * @param  mixed  $value
     * @param  Tag|null  $tag
     */
    public function __construct($value = null, Tag $tag = null)
    {
        if ($tag) {
            $this->setTag($tag);
        }

        if ($value) {
            $this->setValue($value);
        }
    }

    /**
     * Attribute value getter.
     *
     * @return string
     */
    public function getValue(): string
    {
        if (is_array($this->value)) {
            $this->value = implode('', $this->value);
        }

        return (string) $this->value;
    }

    /**
     * Get original value.
     *
     * @return mixed
     */
    public function getOriginalValue()
    {
        return $this->value;
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
     * @return Content
     */
    public function setTag(Tag $tag = null)
    {
        if ($tag) {
            $this->tag = $tag;
        }

        return $this;
    }

    public function __debugInfo()
    {
        return ['value' => $this->value];
    }
}
