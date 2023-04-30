<?php

namespace Lar\Tagable\Core\Traits;

use Lar\Tagable\Core\AttributeCollectionArea;
use Lar\Tagable\Core\VueAttributeCollectionArea;

/**
 * Trait ExcludingMethods.
 *
 * @package Lar\Tagable\Core\Traits
 */
trait ExcludingMethods
{
    /**
     * Clear render state.
     *
     * @return $this
     */
    public function unRender()
    {
        $this->rendered = '';

        return $this;
    }

    /**
     * Remove hash by name.
     *
     * @param string $hash_name
     * @return $this
     */
    public function unHash($hash_name = 'RENDERED')
    {
        if (isset($this->hashes[$hash_name])) {
            $this->hashes[$hash_name] = false;
        }

        return $this;
    }

    /**
     * Remove attribute.
     *
     * @param string|array $name
     * @return $this
     */
    public function removeAttribute($name)
    {
        $this->attributes->remove($name);

        return $this;
    }

    /**
     * Reset all attributes.
     *
     * @return $this
     */
    public function resetAttributes()
    {
        $this->attributes = (! $this->vue ? new AttributeCollectionArea() : new VueAttributeCollectionArea())->setTag($this);

        return $this;
    }

    /**
     * Clean component.
     *
     * @throws \Exception
     */
    public function clear()
    {
        $this->element = '';
        $this->content();
        $this->resetAttributes();
    }
}
