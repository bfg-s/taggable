<?php

namespace Lar\Tagable\Core\Traits;

use Lar\Layout\Abstracts\Component;

/**
 * Trait MagicMethods.
 * @package Lar\Tagable\Core\Traits
 */
trait MagicMethods
{
    /**
     * Wrap data.
     *
     * @param string $tag
     * @param array $contents
     * @param array $attributes
     * @return $this
     */
    public function wrapTo(string $tag, array $contents = [], array $attributes = [])
    {
        foreach ($contents as $content) {
            $this->{$tag}($content)->attr($attributes);
        }

        return $this;
    }

    /**
     * Say to do that.
     *
     * @param $object
     * @param string|null $method
     * @param array $params
     * @return $this
     */
    public function do($object, string $method = null, array $params = [])
    {
        $params = array_merge([$this], $params);

        if (is_embedded_call($object)) {
            call_user_func_array($object, $params);

            return $this;
        } elseif (is_string($object)) {
            $object = $method ? new $object() : new $object(...$params);

            if ($method) {
                $object->{$method}(...$params);
            }
        } elseif (is_object($object)) {
            if ($method) {
                $object->{$method}(...$params);
            } else {
                $object->__invoke(...$params);
            }
        }

        return $this;
    }

    /**
     * Apply parent methods in the component.
     *
     * @param string|array ...$methods
     * @return $this
     */
    public function next(...$methods)
    {
        foreach ($methods as $method) {
            if (is_array($method)) {
                $next = $this;

                foreach ($method as $item) {
                    $result = $this->getRoot()->{$item}($next);

                    if ($result instanceof Component) {
                        $next = $result;
                    }
                }
            } else {
                $this->getRoot()->{$method}($this);
            }
        }

        return $this;
    }

    /**
     * Apply parent methods in the component if $eq equal a true.
     *
     * @param $eq
     * @param string|array ...$methods
     * @return static
     */
    public function nextIf($eq, ...$methods)
    {
        return $eq ? $this->next(...$methods) : $this;
    }

    /**
     * @param $eq
     * @param string $method
     * @param mixed ...$params
     * @return static
     */
    public function callIf($eq, string $method, ...$params)
    {
        return $eq ? $this->call($method, ...$params) : $this;
    }

    /**
     * @param string $method
     * @param mixed ...$params
     * @return $this
     */
    public function call(string $method, ...$params)
    {
        $this->{$method}(...$params);

        return $this;
    }

    /**
     * Set a reference to this object of a particular variable also by reference.
     *
     * @param $link
     * @return $this
     */
    public function haveLink(&$link)
    {
        $link = $this;

        return $this;
    }

    /**
     * Generate external link on component.
     *
     * @param array $params
     * @param bool $rewrite
     * @return $this
     * @throws \Exception
     */
    public function generateExternalLink(array $params = [], $rewrite = true)
    {
        $gen = function () use ($params) {
            $this->external_link = [$this->name ?? static::class => [array_map('ee_model', $params), $this->n_id()]];
        };

        if ($rewrite) {
            $gen();
        } elseif (! $this->external_link) {
            $gen();
        }

        return $this;
    }

    /**
     * Ignore this component if variable $eq equal a boolean true.
     *
     * @param bool $eq
     * @return $this
     */
    public function ignore($eq = 1)
    {
        if ($eq) {
            $this->ignore = true;
        } else {
            $this->ignore = false;
        }

        return $this;
    }

    /**
     * Ignore content of this component if variable $eq equal a boolean true.
     *
     * @param bool $eq
     * @return $this
     */
    public function ignoreContent($eq = 1)
    {
        if ($eq) {
            $this->ignoreContent = true;
        } else {
            $this->ignoreContent = true;
        }

        return $this;
    }

    /**
     * Ignore tag wrapper of this component if variable $eq equal a boolean true.
     *
     * @param bool $eq
     * @return $this
     */
    public function ignoreWrapper($eq = 1)
    {
        if ($eq) {
            $this->ignoreWrapper = true;
        } else {
            $this->ignoreWrapper = false;
        }

        return $this;
    }
}
