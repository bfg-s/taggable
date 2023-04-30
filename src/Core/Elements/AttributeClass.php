<?php

namespace Lar\Tagable\Core\Elements;

use Illuminate\Support\Collection;
use Lar\Tagable\Core\Extension\Attributable;
use Lar\Tagable\Tag;

class AttributeClass extends Collection implements Attributable
{
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
        parent::__construct([]);
    }

    /**
     * Attribute value getter.
     *
     * @return string
     */
    public function getValue(): string
    {
        return $this->implode(' ');
    }

    /**
     * Value setter.
     *
     * @param mixed $value
     */
    public function setValue($value)
    {
        $this->put($value, $value);
    }

    /**
     * Parent tag setter.
     *
     * @param Tag|null $tag
     * @return $this
     */
    public function setTag(?Tag $tag = null)
    {
        $this->tag = $tag;

        return $this;
    }
}
