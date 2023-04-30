<?php

namespace Lar\Tagable\Core\Traits;

use Lar\LJS\LJS;
use Lar\Tagable\Core\TagCollection;
use Lar\Tagable\Tag;

/**
 * Trait LogicMethods.
 *
 * @package Lar\Tagable\Core\Traits
 */
trait LogicMethods
{
    /**
     * If the element of this tag is equal to the specified.
     *
     * @param string $element
     * @return bool
     */
    public function isTsElement(string $element): bool
    {
        return $this->element === $element;
    }

    /**
     * Check if this tag closes or not.
     *
     * @return bool
     */
    public function isClosingTag()
    {
        return is_tag_closing($this->element);
    }

    /**
     * Is debug mode.
     *
     * @return bool
     */
    public function isDebug(): bool
    {
        return $this->debug;
    }

    /**
     * Is rendered element.
     *
     * @return bool
     */
    public function isRendered()
    {
        return $this->rendered && ! empty($this->rendered) ? true : false;
    }

    /**
     * Check change status.
     *
     * @return bool
     */
    public function isChanged()
    {
        return $this->changed;
    }

    /**
     * Check is bottom mode.
     *
     * @return bool
     */
    public function isBottom()
    {
        return $this->bottom_content_mode;
    }

    /**
     * Is tag initialization.
     *
     * @return bool
     */
    public function isElement(): bool
    {
        return $this->element ? true : false;
    }

    /**
     * Has JS.
     *
     * @return bool
     */
    public function hasJs()
    {
        return $this->js instanceof LJS;
    }

    /**
     * Check if the component has such a class.
     *
     * @param string $class
     * @return bool
     */
    public function hasClass(string $class)
    {
        return $this->attributes->has('class') ? $this->attributes->get('class')->has($class) : false;
    }

    /**
     * Check whether the component has such an attribute.
     *
     * @param string $attribute
     * @return mixed
     */
    public function hasAttribute(string $attribute)
    {
        return $this->attributes->has($attribute);
    }

    /**
     * Has parent check.
     *
     * @return bool
     */
    public function hasParent()
    {
        return $this->parent ? true : false;
    }

    /**
     * If attribute equal to value.
     *
     * @param $attr
     * @param $value
     * @return bool
     */
    public function ifAttribute($attr, $value)
    {
        return $this->getAttribute($attr) === $value;
    }

    /**
     * Compare tag with cache.
     *
     * @return bool|array
     * @throws \Exception
     */
    public function compareWithCache()
    {
        if (! static::$cached instanceof TagCollection) {
            return true;
        }

        if (! static::$cached->has($this->id)) {
            return true;
        }

        $tag = static::$cached->get($this->id);

        $this->changed_hashes = $this->compareHashes($tag);

        $this->changed = count($this->changed_hashes) ? true : false;

        return $this->changed_hashes;
    }

    /**
     * Compare hashes.
     *
     * @param array|Tag $comparable_hashes
     * @return array
     * @throws \Exception
     */
    public function compareHashes($comparable_hashes = [])
    {
        if ($comparable_hashes instanceof Tag) {
            $comparable_hashes = $comparable_hashes->getHashes();
        }

        if (! is_array($comparable_hashes)) {
            throw new \Exception('Comparable hashes must be array');
        }

        return array_diff_assoc($this->hashes, $comparable_hashes);
    }
}
