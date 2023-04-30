<?php

namespace Lar\Tagable;

use Closure;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Support\Str;
use Lar\LJS\LJS;
use Lar\Tagable\Core\AttributeCollectionArea;
use Lar\Tagable\Core\ContentCollectionArea;
use Lar\Tagable\Core\HTML5Library;
use Lar\Tagable\Core\TagStatic;
use Lar\Tagable\Core\Traits\BootstrapClassHelpers;
use Lar\Tagable\Core\Traits\DevMethods;
use Lar\Tagable\Core\Traits\ExcludingMethods;
use Lar\Tagable\Core\Traits\GetterMethods;
use Lar\Tagable\Core\Traits\LogicMethods;
use Lar\Tagable\Core\Traits\MagicMethods;
use Lar\Tagable\Core\Traits\SetterMethods;
use Lar\Tagable\Core\VueAttributeCollectionArea;
use Lar\Tagable\Interfaces\AuthUser;
use Lar\Tagable\Interfaces\GuestUser;
use Lar\Tagable\Interfaces\PartConditions;

/**
 * Class Tag.
 *
 * @package Lar\Tagable
 */
class Tag extends TagStatic implements Renderable, \ArrayAccess
{
    use LogicMethods,
        GetterMethods,
        SetterMethods,
        MagicMethods,
        ExcludingMethods,
        DevMethods,
        BootstrapClassHelpers;

    /**
     * Tag identifier.
     *
     * @var int
     */
    protected $id;

    /**
     * Tag element.
     *
     * @var string
     */
    protected $element;

    /**
     * Extended component class.
     *
     * @var string
     */
    protected $extend;

    /**
     * Slot method for extend.
     *
     * @var string
     */
    protected $slot = 'slot';

    /**
     * Attributes collection.
     *
     * @var AttributeCollectionArea
     */
    protected $attributes;

    /**
     * Tag name from selector.
     *
     * @var string
     */
    protected $name;

    /**
     * Content collection.
     *
     * @var ContentCollectionArea
     */
    protected $content;

    /**
     * Bottom content collection.
     *
     * @var ContentCollectionArea
     */
    protected $bottom_content;

    /**
     * Bottom content mode.
     *
     * @var bool
     */
    protected $bottom_content_mode = false;

    /**
     * Child element count.
     *
     * @var int
     */
    protected $child_element_count = 0;

    /**
     * Parent tag object.
     *
     * @var Tag
     */
    protected $parent;

    /**
     * Debug mode.
     *
     * @var bool
     */
    protected $debug = false;

    /**
     * Tag hash list.
     *
     * @var array
     */
    protected $hashes = [];

    /**
     * Check change tag or not.
     *
     * @var bool
     */
    protected $changed = true;

    /**
     * Hash change list.
     *
     * @var array
     */
    protected $changed_hashes = [];

    /**
     * Rendered tag wrapper.
     *
     * @var string
     */
    protected $wrapper = '';

    /**
     * A tag rendered data.
     *
     * @var string
     */
    protected $rendered = '';

    /**
     * Tag unique identifier.
     *
     * @var string
     */
    protected $unique;

    /**
     * When render closure.
     *
     * @var Closure|array|null
     */
    protected $wrc;

    /**
     * External link to the object.
     *
     * @var array
     */
    protected $external_link;

    /**
     * LJS Handler script name.
     *
     * @var string
     */
    protected $handler_name = null;

    /**
     * @var LJS
     */
    protected $js;

    /**
     * Ignore content of this component.
     *
     * @var bool
     */
    protected $ignoreContent = false;

    /**
     * Ignore tag wrapper of this component.
     *
     * @var bool
     */
    protected $ignoreWrapper = false;

    /**
     * Ignore this component.
     *
     * @var bool
     */
    protected $ignore = false;

    /**
     * Open tag mode.
     *
     * @var bool
     */
    public $opened_mode = false;

    /**
     * Execute inner methods before render.
     *
     * @var array
     */
    public $execute = [];

    /**
     * Perform internal methods during construction.
     *
     * @var array
     */
    public $constructors = [];

    /**
     * @var object
     */
    public $auth;

    /**
     * Vue attribute mode.
     *
     * @var bool
     */
    protected $vue = false;

    /**
     * @var array
     */
    protected $injected_groups = [];

    /**
     * Tag constructor.
     *
     * @param null|array|string $element
     * @param null|array|Closure $attributes
     */
    public function __construct($element = null, $attributes = null)
    {
        if (static::$_tmp_handler_name) {
            if (! $this->handler_name) {
                $this->handler_name = static::$_tmp_handler_name;
            }

            static::$_tmp_handler_name = null;
        }

        $this->attributes = (! $this->vue ? new AttributeCollectionArea() : new VueAttributeCollectionArea())->setTag($this);

        $this->content = (new ContentCollectionArea())->setTag($this);

        $this->bottom_content = (new ContentCollectionArea())->setTag($this);

        parent::__construct();

        if (! $this->auth) {
            $this->auth = app('auth');
        }

        if ($this->element) {
            $this->initTag($this->element);
        }

        if (is_string($element)) {
            $this->initTag($element);
        }

        if (is_embedded_call($element)) {
            $this->_when($element);
        }

        if (is_array($element)) {
            $this->_when($element);
        }

        $this->createUnique();

        $this->constructor_magic();

        if (isset($this->default_attrubutes) && is_array($this->default_attrubutes)) {
            $this->attr($this->default_attrubutes);
        }

        if (isset($this->props) && is_array($this->props)) {
            $this->attr($this->props);
        }
    }

    /**
     * @return $this
     */
    public function vue()
    {
        $this->attributes = (new VueAttributeCollectionArea())->setTag($this);

        return $this;
    }

    /**
     * Magic static.
     *
     * @param $name
     * @param $arguments
     * @return $this
     * @throws \Exception
     */
    public static function __callStatic($name, $arguments)
    {
        if (isset(static::$macros) && static::hasMacro($name)) {
            $macro = static::$macros[$name];

            if ($macro instanceof Closure) {
                return call_user_func_array(Closure::bind($macro, null, static::class), $arguments);
            }

            return $macro(...$arguments);
        } elseif (is_tag($name)) {
            $newObject = new static();

            $newObject->initTag($name)->when($arguments);

            return $newObject;
        } elseif (static::$components->has($name)) {
            $name = static::$components->get($name);

            $newObj = new $name(...$arguments);

            return $newObj;
        } elseif (substr($name, 0, 3) == 'set') {
            $newObject = new static();

            $clean_name = Str::snake(substr($name, 3));

            if (HTML5Library::getAttributes()->has($clean_name)) {
                $newObject->attr($clean_name, isset($arguments[0]) ? $arguments[0] : '');
            }

            return $newObject;
        } else {
            $new = new static();

            if (method_exists($new, "_{$name}")) {
                return $new->{"_{$name}"}(...$arguments);
            }
        }
    }

    /**
     * Magic call method.
     *
     * @param $name
     * @param $arguments
     * @return Tag|string
     * @throws \Exception
     */
    public function __call($name, $arguments)
    {
        if (isset(static::$groups[$name])) {
            return $this->if_there_is_a_group($name, $arguments);
        }

        if (isset(static::$macros) && static::hasMacro($name)) {
            $macro = static::$macros[$name];

            if ($macro instanceof Closure) {
                return call_user_func_array($macro->bindTo($this, static::class), $arguments);
            }

            return $macro(...$arguments);
        } elseif (static::$components->has($name)) {
            return $this->if_there_is_a_component($name, $arguments);
        } elseif (is_tag($name)) {
            return $this->if_there_is_in_the_declared_tags($name, $arguments);
        } elseif (preg_match('/^\_([a-z\_]{1}.*)/', $name, $m)) {
            return $this->parent ? $this->parent->{$m[1]}(...$arguments) : $this;
        } elseif (preg_match('/^set([A-Z]{1}[a-zA-Z]+)If$/', $name, $m)) {
            return $this->if_the_declared_attribute_is_found_with_the_condition($arguments, $m);
        } elseif (preg_match('/^set([A-Z]{1}[a-zA-Z]+)$/', $name, $m)) {
            return $this->if_the_declared_attribute_is_found($arguments, $m);
        } elseif (preg_match('/^get([A-Z]{1}[a-zA-Z]+)$/', $name, $m)) {
            return $this->hasAttribute(Str::snake($m[1])) ? $this->getAttribute(Str::snake($m[1])) : false;
        } elseif (preg_match('/^on([A-Z]{1}[a-zA-Z]+)If$/', $name, $m)) {
            return $this->if_set_event_attribute_eq($arguments[0], $m[1], $arguments[1]);
        } elseif (preg_match('/^on([A-Z]{1}[a-zA-Z]+)$/', $name, $m)) {
            return $this->if_set_event_attribute($m[1], $arguments);
        } elseif (preg_match('/^class([A-Z]{1}[a-zA-Z]+)If$/', $name, $m)) {
            return $this->if_there_is_a_class_setting_pattern_with_a_condition($arguments, $m);
        } elseif (preg_match('/^class([A-Z]{1}[a-zA-Z]+)$/', $name, $m)) {
            return $this->if_there_is_a_class_setting_pattern($arguments, $m);
        } else {
            if (method_exists($this, "_{$name}")) {
                return $this->{"_{$name}"}(...$arguments);
            }
        }

        return $this;
    }

    /**
     * Tag initialize.
     *
     * @param $element
     * @return Tag
     * @throws \Exception
     */
    public function initTag($element)
    {
        if (! $this->isElement() && $element) {
            $this->element = $element;
        }

        if ($this->name) {
            $this->name($this->name);
        }

        $this->id = static::$collect->registrationTag($this);

        /** @var Tag $this */
        return $this;
    }

    /**
     * @return $this
     */
    public function unregister()
    {
        static::$collect->drop($this->id);

        return $this;
    }

    /**
     * @param  null  $collect
     * @return $this
     */
    public function newContentCollection($collect = null)
    {
        if (! $collect) {
            $collect = (new ContentCollectionArea())->setTag($this);
        }

        $this->content = $collect;

        return $this;
    }

    /**
     * @return \Lar\Tagable\Core\ContentCollectionArea
     */
    public function contentCollection()
    {
        return $this->content;
    }

    /**
     * @param  string  $element
     * @return $this
     */
    public function setElement(string $element)
    {
        $this->element = $element;

        return $this;
    }

    /**
     * Apply all construct magics.
     *
     * @return $this
     */
    private function constructor_magic()
    {
        if ($this instanceof AuthUser && ! $this->auth->guest()) {
            $this->user = \Auth::user();

            $this->auth_user();
        }

        if ($this instanceof GuestUser && $this->auth->guest()) {
            $this->guest_user();
        }

        $part = $this instanceof PartConditions;

        foreach ($this->constructors as $key => $item) {
            if (is_numeric($key) && is_string($item)) {
                if ($part) {
                    $m = $item.'_test';

                    if (method_exists($this, $m)) {
                        if ($result = (bool) $this->{$m}()) {
                            $this->{$item}($result);
                        }
                    } else {
                        $this->{$item}();
                    }
                } else {
                    $this->{$item}();
                }
            }
        }

        return $this;
    }

    /**
     * Set event attribute if $eq == true.
     *
     * @param $eq
     * @param $event
     * @param $argument
     * @return $this
     * @throws \Exception
     */
    private function if_set_event_attribute_eq($eq, $event, $argument)
    {
        if (is_embedded_call($eq)) {
            $eq = call_user_func($eq, $this);
        }

        if ($eq) {
            $event = strtolower($event);

            if (! HTML5Library::$events->has($event)) {
                throw new \Exception("Event [{$event}] not found!");
            }

            $this->attr('on'.$event, $argument);
        }

        return $this;
    }

    /**
     * Set event attribute.
     *
     * @param $event
     * @param $arguments
     * @return $this
     * @throws \Exception
     */
    private function if_set_event_attribute($event, $arguments)
    {
        $event = strtolower($event);

        if (! HTML5Library::$events->has($event)) {
            throw new \Exception("Event [{$event}] not found!");
        }

        $this->attr('on'.$event, $arguments[0]);

        return $this;
    }

    /**
     * If there is in the declared tags.
     *
     * @param $name
     * @param $arguments
     * @return $this|Tag
     * @throws \Exception
     */
    private function if_there_is_in_the_declared_tags($name, $arguments)
    {
        if (! $this->element) {
            $this->initTag($name);

            $this->when($arguments);

            return $this;
        } else {
            return $this->add($name, $arguments);
        }
    }

    /**
     * If there is a component.
     *
     * @param $name
     * @param $arguments
     * @return mixed
     * @throws \Exception
     */
    protected function if_there_is_a_component($name, $arguments)
    {
        $object = static::$components->get($name);

        $object::$_tmp_handler_name = $this->handler_name;

        /** @var Tag $newObj */
        $newObj = new $object(...$arguments);

        $this->appEnd($newObj);

        return $newObj;
    }

    /**
     * If there is a group.
     *
     * @param $name
     * @param $arguments
     * @return mixed
     * @throws \Exception
     */
    protected function if_there_is_a_group($name, $arguments)
    {
        $object = static::$groups[$name];

        /** @var Tag $newObj */
        $newObj = new $object(...$arguments);

        $this->injected_groups[] = $newObj;

        return $newObj;
    }

    /**
     * If there is a class setting pattern with a condition.
     *
     * @param $arguments
     * @param $matches
     * @return $this|Tag
     * @throws \Exception
     */
    protected function if_there_is_a_class_setting_pattern_with_a_condition($arguments, $matches)
    {
        if (! isset($arguments[0])) {
            return $this;
        }

        if (is_embedded_call($arguments[0])) {
            $eq = call_user_func($arguments[0], $this);
        } else {
            $eq = $arguments[0];
        }

        if ($eq) {
            return $this->if_there_is_a_class_setting_pattern(isset($arguments[1]) ? [$arguments[1]] : [], $matches);
        }

        return $this;
    }

    /**
     * If there is a class setting pattern.
     *
     * @param $arguments
     * @param $matches
     * @return $this
     * @throws \Exception
     */
    protected function if_there_is_a_class_setting_pattern($arguments, $matches)
    {
        if ($arguments[0] == 'camel') {
            $class_name = lcfirst(Str::camel($matches[1]));
        } elseif ($arguments[0] == 'Camel') {
            $class_name = ucfirst(Str::camel($matches[1]));
        } else {
            $class_name = Str::snake($matches[1], isset($arguments[0]) ? $arguments[0] : '-');
        }

        $this->addClass($class_name);

        return $this;
    }

    /**
     * If the declared attribute is found with the condition.
     *
     * @param $arguments
     * @param array $matches
     * @return $this|Tag
     * @throws \Exception
     */
    protected function if_the_declared_attribute_is_found_with_the_condition($arguments, array $matches)
    {
        if (! isset($arguments[0])) {
            return $this;
        }

        if (is_embedded_call($arguments[0])) {
            $eq = call_user_func($arguments[0], $this);
        } else {
            $eq = $arguments[0];
        }

        if ($eq) {
            return $this->if_the_declared_attribute_is_found(isset($arguments[1]) ? [$arguments[1]] : [], $matches);
        }

        return $this;
    }

    /**
     * If the declared attribute is found.
     *
     * @param $arguments
     * @param array $matches
     * @return $this
     * @throws \Exception
     */
    protected function if_the_declared_attribute_is_found($arguments, array $matches)
    {
        $clean_name = Str::snake($matches[1]);

        if (HTML5Library::getAttributes()->has($clean_name)) {
            $this->attr($clean_name, isset($arguments[0]) ? $arguments[0] : '');
        }

        return $this;
    }

    /**
     * Whether a offset exists.
     *
     * The return value will be casted to boolean if non-boolean was returned.
     *
     * @link https://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset An offset to check for.
     * @return bool true on success or false on failure.
     * @since 5.0.0
     */
    public function offsetExists($offset)
    {
        return isset($this->{$offset});
    }

    /**
     * Offset to retrieve.
     *
     * @link https://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset The offset to retrieve.
     * @return mixed Can return all value types.
     * @since 5.0.0
     */
    public function offsetGet($offset)
    {
        return $this->{$offset};
    }

    /**
     * Offset to set.
     *
     * @link https://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset The offset to assign the value to.
     * @param mixed $value The value to set.
     * @return void
     * @since 5.0.0
     */
    public function offsetSet($offset, $value)
    {
        $this->{$offset} = $value;
    }

    /**
     * Offset to unset.
     *
     * @link https://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset The offset to unset.
     * @return void
     * @since 5.0.0
     */
    public function offsetUnset($offset)
    {
    }

    /**
     * @return \Lar\Tagable\Core\ContentCollectionArea
     */
    public function bottomContent()
    {
        return $this->bottom_content;
    }
}
