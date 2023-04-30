<?php

namespace Lar\Tagable\Core\Traits;

use Illuminate\Contracts\Support\Renderable;
use Lar\Layout\Abstracts\Component;
use Lar\Layout\Respond;
use Lar\LJS\LJS;
use Lar\Tagable\Core\ContentCollectionArea;
use Lar\Tagable\Tag;

/**
 * Trait SetterMethods.
 *
 * @package Lar\Tagable\Core\Traits
 */
trait SetterMethods
{
    /**
     * Add group attribute.
     *
     * @param string $group
     * @param array $data
     * @return static
     */
    public function attrGroup(string $group, array $data)
    {
        $groupData = [];

        foreach ($data as $key => $datum) {
            $groupData[$group.'-'.$key] = $datum;
        }

        return $this->attr($groupData);
    }

    /**
     * @return $this
     */
    public function openMode()
    {
        $this->opened_mode = true;

        return $this;
    }

    /**
     * JS Core setter.
     *
     * @return \Lar\LJS\LJS
     * @deprecated dont use!
     */
    public function setJs()
    {
        if (! $this->js instanceof LJS) {
            $this->js = new LJS();

            $this->js->group(static::class);
        }

        return $this->js;
    }

    /**
     * Hide component.
     *
     * @param bool $eq
     * @return $this
     */
    public function hide($eq = true)
    {
        if ($eq) {
            $this->attr('style', 'display: none');
        }

        return $this;
    }

    /**
     * Set unique default tag attribute ID.
     *
     * @return $this
     * @throws \Exception
     */
    public function setUID()
    {
        $this->attr('id', $this->unique);

        return $this;
    }

    /**
     * Name setter.
     *
     * @param string $name
     * @return $this
     */
    public function name(string $name)
    {
        /*if ($this->hasParent()) {

            $name = $this->parent->getName().":".$name;
        }*/

        if ($this->name != $name) {
            $this->name = $name;
        }

        static::addName($this->name, $this);

        return $this;
    }

    /**
     * Debug mode setter.
     *
     * @return Tag
     */
    public function setDebug()
    {
        $this->debug = true;

        /** @var Tag $this */
        return $this;
    }

    /**
     * Parent setter.
     *
     * @param Tag $tag
     * @return Tag
     */
    public function setParent(Tag $tag)
    {
        $this->parent = $tag;

        $this->setChildElementCount($tag->getChildElementCount() + 1);

        if ($tag->isDebug()) {
            $this->setDebug();
        }

        /** @var Tag $this */
        return $this;
    }

    /**
     * Set the values of the attribute "data-*".
     *
     * @param array $datas
     * @return Tag
     */
    public function setDatas(array $datas)
    {
        foreach ($datas as $key => $data) {
            $this->attr("data-{$key}", is_array($data) ? implode(' && ', $data) : $data);
        }

        /** @var Tag $this */
        return $this;
    }

    /**
     * @param  array  $rules
     * @return $this
     */
    public function setRules(array $rules)
    {
        foreach ($rules as $key => $rule) {
            if (is_numeric($key)) {
                $this->attr("data-rule-{$rule}", '');
            } else {
                $this->attr("data-rule-{$key}", $rule);
            }
        }

        return $this;
    }

    /**
     * Attribute setter.
     *
     * @param $name
     * @param null $value
     * @return $this
     */
    public function attr($name, $value = null)
    {
        if (is_array($name)) {
            foreach ($name as $key => $item) {
                if ($key === 'data' && is_array($item)) {
                    $this->setDatas($item);
                } elseif ($key === 'rule' && is_array($item)) {
                    $this->setRules($item);
                } elseif (is_array($item)) {
                    $this->attr($item);
                } else {
                    $this->attr($key, $item);
                }
            }
        } else {
            if (! is_numeric($name)) {
                $this->attributes->add($name, $value);
            } else {
                if (preg_match('/^(\#|\.)(.*)$/', $value, $matches)) {
                    $this->attributes->add($matches[1] == '#' ? 'id' : 'class', $matches[2]);
                } else {
                    if (preg_match('/^\[([a-zA-Z\-\_]+)\s*\=?\s*(.+)?\]$/', $value, $matches)) {
                        $this->attributes->add($matches[1], isset($matches[2]) ? $matches[2] : '');
                    } else {
                        $this->attributes->add('class', $value);
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Content setter.
     *
     * @param $value
     * @param array $values
     * @return mixed
     */
    public function text($value, ...$values)
    {
        $this->content->add($value);

        if (count($values)) {
            foreach ($values as $value) {
                if ($value instanceof Tag && ! $value->hasParent()) {
                    $value->setParent($this);
                }

                $this->content->add($value);
            }
        }

        return $this;
    }

    /**
     * Add class in to tag.
     *
     * @param string|array $class
     * @return $this
     */
    public function addClass(...$class)
    {
        $this->attr($class);

        return $this;
    }

    /**
     * Add class in to tag if $eq == true.
     *
     * @param $eq
     * @param string|array $class
     * @return $this
     */
    public function addClassIf($eq, ...$class)
    {
        if ($eq) {
            $this->attr($class);
        }

        return $this;
    }

    /**
     * App end data if...
     *
     * @param $eq
     * @param mixed ...$data
     * @return $this
     */
    public function appEndIf($eq, ...$data)
    {
        if (is_embedded_call($eq)) {
            $eq = call_user_func($eq, $this);
        }

        if ($eq) {
            $this->appEnd(...$data);
        }

        return $this;
    }

    /**
     * Content append setter.
     *
     * @param array $data
     * @return Tag
     */
    public function appEnd(...$data)
    {
        if (count($data)) {
            foreach ($data as $value) {
                if (is_embedded_call($value)) {
                    $new_obj = new static();
                    call_user_func($value, $new_obj);
                    $value = $new_obj;
                }

                if ($value instanceof Tag) {
                    $value = $value->getRoot();

                    if (! $value->hasParent()) {
                        $value->setParent($this);
                    }
                }

                $this->content->add($value);
            }

            /** @var Tag $this */
            return $this;
        }

        /** @var Tag $new_obj */
        $new_obj = new Component();

        $this->content->add($new_obj);
        $new_obj->setParent($this);

        return $new_obj;
    }

    /**
     * Prep end data if...
     *
     * @param $eq
     * @param mixed ...$data
     * @return $this
     * @throws \Exception
     */
    public function prepEndIf($eq, ...$data)
    {
        if (is_embedded_call($eq)) {
            $eq = call_user_func($eq, $this);
        }

        if ($eq) {
            $this->prepEnd(...$data);
        }

        return $this;
    }

    /**
     * Content prepend setter.
     *
     * @param array $data
     * @return Tag
     */
    public function prepEnd(...$data)
    {
        if (count($data)) {
            foreach ($data as $value) {
                if (is_embedded_call($value)) {
                    $new_obj = new static();
                    call_user_func($value, $new_obj);
                    $value = $new_obj;
                }

                if ($value instanceof Tag) {
                    $value = $value->getRoot();

                    if (! $value->hasParent()) {
                        $value->setParent($this);
                    }
                }

                $this->content->add($value, true);
            }

            /** @var Tag $this */
            return $this;
        }

        /** @var Tag $new_obj */
        $new_obj = new Component();

        $this->content->add($new_obj, true);
        $new_obj->setParent($this);

        return $new_obj;
    }

    /**
     * Clear content and adds data if that to need.
     *
     * @param array|string|\Closure $value
     * @return $this
     * @throws \Exception
     */
    public function content(...$value)
    {
        $this->content = (new ContentCollectionArea())->setTag($this);

        if ($value && ! empty($value)) {
            $this->appEnd($value);
        }

        return $this;
    }

    /**
     * Set anchor.
     *
     * @param string $name
     * @param array $data
     * @return $this
     */
    public function anchor(string $name = '')
    {
        $this->setHref('#'.$name);

        return $this;
    }

    /**
     * Set External Link.
     *
     * @param string $class
     * @param array $params
     * @param string $position
     * @return $this
     */
    public function setExternalLink(string $class, array $params = [], string $position = null)
    {
        $this->external_link = [$class => [array_map('ee_model', $params), $position ? $position : $this->haveAndGetSelectorID()]];

        return $this;
    }

    /**
     * LJS Worker.
     *
     * @param string $event
     * @return Respond
     */
    public function lj($event = 'begin')
    {
        if ($this->hasAttribute('data-ljs')) {
            $attr = $this->attributes->get('data-ljs');

            if ($attr->has($event)) {
                $exec = $attr->get($event);
            } else {
                $exec = new Respond($this);

                $this->attr('data-ljs', [
                    'event' => $event,
                    'exec' => $exec,
                ]);
            }
        } else {
            $exec = new Respond($this);

            $this->attr('ljs', [
                'event' => $event,
                'exec' => $exec,
            ]);
        }

        return $exec;
    }

    /**
     * Set Handler name.
     *
     * @param string $handler_name
     * @return $this
     */
    public function setHandlerName(string $handler_name)
    {
        $this->handler_name = $handler_name;

        return $this;
    }

    /**
     * Add ljs executor.
     *
     * @param $exec
     * @param null $params
     * @param string $event
     * @return Respond|$this
     * @throws \Exception
     */
    public function ljs($exec = null, $params = null, $event = 'click')
    {
        if ($this->hasAttribute('data-ljs')) {
            $attr = $this->attributes->get('ljs');

            if ($attr->has($event)) {
                $attr->get($event)->put($exec, $params);
            } else {
                $this->attr('data-ljs', [
                    'event' => $event,
                    'exec' => (new Respond($this))->put($exec, $params),
                ]);
            }
        } else {
            $this->attr('ljs', [
                'event' => $event,
                'exec' => (new Respond($this))->put($exec, $params),
            ]);
        }

        return $this;
    }

    /**
     * Add new child.
     *
     * @param string $element
     * @param array $arguments
     * @return $this
     * @throws \Exception
     */
    public function add(string $element, array $arguments = [])
    {
        if (! $this->isElement()) {
            throw new \Exception('Element not initialized! Clall "initTag"');
        }

        static::$_tmp_handler_name = $this->handler_name;

        $new = new static();

        if ($new->isElement()) {
            $new->clear();
        }

        $new->initTag($element);

        /** @var Tag $this */
        $new->setParent($this);

        foreach ($arguments as $arg) {
            if (is_embedded_call($arg)) {
                if ($arg instanceof Renderable) {
                    $new->text($arg);
                } else {
                    call_user_func($arg, $new);
                }
            } elseif (is_array($arg)) {
                $new->attr($arg);
            } elseif (is_string($arg) || is_numeric($arg)) {
                $new->text($arg);
            } else {
                throw new \Exception('Error!');
            }
        }

        $this->text($new);

        return $new;
    }

    /**
     * Paste this component into parent N times. Return parent.
     *
     * @param int $time
     * @return $this
     */
    public function repeat(int $time)
    {
        for ($i = 0; $i < $time; $i++) {
            $this->parent->appEnd(clone $this);
        }

        return $this->parent;
    }

    /**
     * When element.
     *
     * @param mixed ...$arguments
     * @return static
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function _when(...$arguments)
    {
        $arguments = $arguments[0];

        if (! is_array($arguments)) {
            $arguments = func_get_args();
        }

        foreach ($arguments as $key => $arg) {
            if (is_string($arg) || is_numeric($arg)) {
                $this->text($arg);
            } elseif (is_array($arg)) {
                $this->attr($arg);
            } elseif (is_embedded_call($arg)) {
                call_user_func($arg, $this);
            } elseif ($arg instanceof Renderable) {
                $this->appEnd($arg);
            }
        }

        return $this;
    }

    /**
     * Make when if $eq == true.
     *
     * @param $eq
     * @param mixed ...$data
     * @return $this
     */
    public function _whenIf($eq, ...$data)
    {
        if ($eq) {
            $this->when($data);
        }

        return $this;
    }

    /**
     * Create unique attribute from tag.
     *
     * @return $this
     */
    public function createUniqueAttribute()
    {
        $unique = $this->getUnique();

        return $this->attr($unique);
    }

    /**
     * Create unique inner identifier from tag.
     *
     * @param  string  $prefix
     * @return $this
     */
    public function createUnique(string $prefix = 'tag')
    {
        $this->unique = uniqid($prefix.spl_object_id($this));

        return $this;
    }

    /**
     * Set when render closure.
     *
     * @param \Closure|array $call
     * @return $this
     */
    public function whenRender($call)
    {
        if (is_embedded_call($call)) {
            $this->wrc = $call;
        }

        return $this;
    }

    /**
     * Add name in app end.
     *
     * @param string $name_append
     * @return $this
     */
    public function nameAppEnd(string $name_append)
    {
        $this->name = $name_append.$this->name;

        return $this;
    }

    /**
     * Add name prepend.
     *
     * @param string $name_prepend
     * @return $this
     */
    public function namePrepEnd(string $name_prepend)
    {
        $this->name = $this->name.$name_prepend;

        return $this;
    }

    /**
     * Disable bottom content mode.
     *
     * @return $this
     */
    public function offBottom()
    {
        $this->bottom_content_mode = false;

        return $this;
    }

    /**
     * Type to bottom mode.
     *
     * @param array $data
     * @return $this
     */
    public function toBottom(...$data)
    {
        $tmp_content = $this->content;

        $tmp_content_bottom = $this->bottom_content;

        if (! $this->bottom_content_mode) {
            $this->bottom_content_mode = true;
        } else {
            $this->bottom_content_mode = false;
        }

        $this->content = $tmp_content_bottom;

        $this->bottom_content = $tmp_content;

        $this->when($data);

        return $this;
    }

    /**
     * Insert lang key.
     *
     * @param $key
     * @param array $params
     * @param string $provider
     */
    public function l($key, array $params = [], $provider = 'text')
    {
        $this->{$provider}(__($key, $params));
    }

    /**
     * Add method or methods to execute list.
     *
     * @param $data
     * @return $this
     */
    public function toExecute(...$data)
    {
        $this->execute = array_merge_recursive($this->execute, $data);

        return $this;
    }

    /**
     * Add method or methods to constructor list.
     *
     * @param $data
     * @return $this
     */
    public function toConstruct(...$data)
    {
        $this->constructors = array_merge_recursive($this->constructors, $data);

        return $this;
    }

    /**
     * Add method or methods to global execute list.
     *
     * @param $data
     * @return $this
     */
    public function toGlobalExecute($data)
    {
        if (! is_array($data)) {
            $data = func_get_args();
        }

        static::$global_executor = array_merge_recursive(static::$global_executor, $data);

        return $this;
    }

    /**
     * Quick infusion tag width data.
     *
     * @param array $data
     * @return $this
     */
    public function quickInfusion(array $data)
    {
        foreach ($data as $method => $datum) {
            $this->{$method}($datum);
        }

        return $this;
    }

    /**
     * Create name prefix.
     *
     * @param null $prefix
     */
    public function prefixName($prefix = null)
    {
        if (! $prefix) {
            $prefix = spl_object_id($this);
        }

        $this->name = $prefix.'_'.$this->name;
    }

    /**
     * Initial tag script.
     *
     * @param $script
     * @param null $params
     * @return $this
     */
    public function initialScript($script, $params = null)
    {
        $this->js()->exec($script, $params);

        return $this;
    }

    /**
     * Insert blade in tag.
     *
     * @param $path
     * @param array $data
     * @param array $mergeData
     * @return $this
     */
    public function view($path, $data = [], $mergeData = [])
    {
        $this->appEnd(
            view($path, $data, $mergeData)
        );

        return $this;
    }

    /**
     * Child element count setter.
     *
     * @param int $child_element_count
     * @return Tag
     */
    public function setChildElementCount(int $child_element_count)
    {
        $this->child_element_count = $child_element_count;

        /** @var Tag $this */
        return $this;
    }
}
