<?php

namespace Lar\Tagable\Core\Traits;

use Illuminate\Support\Str;
use Lar\Tagable\Core\Elements\ContentObject;
use Lar\Tagable\Core\Extension\Content;
use Lar\Tagable\Core\FindCollection;
use Lar\Tagable\Core\Selector;
use Lar\Tagable\Tag;

/**
 * Trait GetterMethods.
 *
 * @package Lar\Tagable\Core\Traits
 */
trait GetterMethods
{
    /**
     * Get component name.
     *
     * @return null|string
     */
    public function componentName()
    {
        return $this->name;
    }

    /**
     * JS init list.
     *
     * @return array
     */
    public function getJS(): array
    {
        return [];
    }

    /**
     * JS Core.
     *
     * @return \Lar\LJS\LJS
     */
    public function js()
    {
        return $this->getRoot()->setJs();
    }

    /**
     * Storage accessor.
     *
     * @return \Illuminate\Support\Collection
     */
    public function storage()
    {
        return static::$storage;
    }

    /**
     * Create and get JSQuery selector.
     *
     * @return string
     * @throws \Exception
     */
    public function haveAndGetSelector()
    {
        if ($id = $this->getID()) {
            $selector = "#{$id}";
        } else {
            $this->createUniqueAttribute();
            $selector = "[{$this->getUnique()}]";
        }

        return $selector;
    }

    /**
     * Create Or Get ID and return JSQuery selector.
     *
     * @return string
     * @throws \Exception
     */
    public function haveAndGetSelectorID()
    {
        if ($this->hasAttribute('id')) {
            $selector = '#'.$this->getAttribute('id');
        } else {
            $this->attr(['id' => $this->getUnique()]);

            $selector = '#'.$this->getUnique();
        }

        return  $selector;
    }

    /**
     * Selector getter.
     *
     * @return string
     * @throws \Exception
     */
    public function getSelector()
    {
        return $this->haveAndGetSelectorID();
    }

    /**
     * Component name getter.
     *
     * @return string
     */
    public function getObjName()
    {
        return $this->name;
    }

    /**
     * Element getter.
     *
     * @return string
     */
    public function getElement(): string
    {
        return $this->element;
    }

    /**
     * Parent getter.
     *
     * @return Tag
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Get root parent.
     *
     * @return Tag
     */
    public function getRoot()
    {
        $test = $this;

        while ($test->hasParent()) {
            $test = $test->getParent();
        }

        return $test;
    }

    /**
     * Get child element count.
     *
     * @return int
     */
    public function getChildElementCount(): int
    {
        return $this->child_element_count;
    }

    /**
     * Get render data.
     *
     * @return string
     */
    public function getRendered()
    {
        return $this->rendered;
    }

    /**
     * Get attribute value.
     *
     * @param string $attribute
     * @return string|array|bool
     */
    public function getAttribute(string $attribute)
    {
        return $this->attributes->has($attribute) ?
            $this->attributes->get($attribute)->getValue()
            : false;
    }

    /**
     * Parent link.
     */
    public function _()
    {
        return $this->parent;
    }

    /**
     * Auto create normal id.
     *
     * @return string
     * @throws \Exception
     */
    public function n_id()
    {
        if ($this->hasAttribute('id')) {
            $selector = $this->getAttribute('id');

            return  "#{$selector}";
        } else {
            $id = Str::snake($this->name ?? static::class);

            $id = str_replace('\\', '', $id);

            $selector = $id;

            $this->attr([$selector => null]);

            return  "[{$selector}]";
        }
    }

    /**
     * Generate and get super name.
     *
     * @return string
     */
    public function super_name()
    {
        $id = Str::snake($this->name ?? static::class);

        return str_replace('\\', '', $id);
    }

    /**
     * Get external link to the object.
     *
     * @param array $params
     * @return array
     * @throws \Exception
     */
    public function getExternalLink(array $params = [])
    {
        $this->generateExternalLink($params, false);

        return $this->external_link;
    }

    /**
     * Handler root name getter.
     *
     * @return string
     */
    public function handler_name()
    {
        return $this->getHandlerName();
    }

    /**
     * Handler name getter.
     *
     * @return string
     */
    public function getHandlerName()
    {
        return $this->handler_name;
    }

    /**
     * Get unique identifier.
     *
     * @return string
     */
    public function getUnique()
    {
        if (! $this->unique) {
            $this->createUnique();
        }

        return $this->unique;
    }

    /**
     * Get tag hashes.
     *
     * @return array
     */
    public function getHashes()
    {
        return $this->hashes;
    }

    /**
     * Render content getter.
     *
     * @return string
     */
    public function getRenderContent()
    {
        if (empty($this->wrapper)) {
            return '';
        }

        $lens = explode('><', $this->wrapper);

        return substr($this->rendered, strlen($lens[0]) + 1, -(strlen($lens[1]) + 1));
    }

    /**
     * Root wrapper.
     *
     * @param \Closure|array $call
     * @return Tag
     */
    public function root($call)
    {
        $test = $this;

        while ($test->hasParent()) {
            $test = $test->getParent();
        }

        if (is_embedded_call($call)) {
            call_user_func($call, $test);
        }

        /** @var Tag $this */
        return $this;
    }

    /**
     * Inner render helper.
     *
     * @param string $attributes
     * @param string $content
     * @return string
     */
    protected function getNeedlePattern(string $attributes, string $content)
    {
        if (static::$open_mode || $this->opened_mode) {
            return static::$loes.$this->element.$attributes.static::$rces.$content;
        } elseif ($this->isClosingTag()) {
            return static::$loes.$this->element.$attributes.static::$rces.$content.$this->element_closer();
        } else {
            return static::$loes.$this->element.$attributes.static::$recoe.$content;
        }
    }

    /**
     * @return string
     */
    public function element_closer()
    {
        return static::$lecoe.$this->element.static::$rces;
    }

    /**
     * @return int
     */
    public function contentCount()
    {
        return $this->content->count();
    }

    /**
     * @return mixed
     */
    public function last()
    {
        /** @var Content $last */
        $last = $this->content->last();

        if ($last) {
            $last = $last->getOriginalValue();
        }

        return $last;
    }

    /**
     * Find tags in content.
     *
     * @param string $selector
     * @return $this|\Lar\Tagable\Core\FindCollection
     * @throws \Exception
     */
    public function find(string $selector)
    {
        $find_collection = new FindCollection();

        if (! $this->content) {
            return $find_collection;
        }

        $select = new Selector();

        $condition = $select->pars($selector);

        $this->content->map(function ($item) use ($find_collection, $select, $selector, $condition) {
            if ($item instanceof ContentObject) {
                $originalValue = $item->getOriginalValue();

                if ($originalValue instanceof Tag) {
                    if (count($condition[$selector]) == 1 && isset($condition[$selector]['element']) && $originalValue->getElement() == $condition[$selector]['element']) {
                        $find_collection->put($originalValue['id'], $originalValue);
                    } elseif (isset($condition[$selector]['element']) && $originalValue->getElement() == $condition[$selector]['element'] && $select->compare($condition[$selector], $originalValue)) {
                        $find_collection->put($originalValue['id'], $originalValue);
                    } elseif ($select->compare($condition[$selector], $originalValue)) {
                        $find_collection->put($originalValue['id'], $originalValue);
                    }
                }
            }
        });

        if ($find_collection->count() == 1) {
            return $find_collection->first();
        } else {
            return $find_collection;
        }
    }
}
