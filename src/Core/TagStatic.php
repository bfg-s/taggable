<?php

namespace Lar\Tagable\Core;

use Illuminate\Support\Collection;
use Lar\Tagable\Tag;

class TagStatic
{
    /**
     * HTML5 doctype.
     *
     * @var string
     */
    public static $doctype = '<!DOCTYPE html>';

    /**
     * Left opening element symbol.
     *
     * @var string
     */
    public static $loes = '<';

    /**
     * Right closing element symbol.
     *
     * @var string
     */
    public static $rces = '>';

    /**
     * Right end character of element.
     *
     * @var string
     */
    public static $recoe = '/>';

    /**
     * Left end character of element.
     *
     * @var string
     */
    public static $lecoe = '</';

    /**
     * Tags collection.
     *
     * @var TagCollection
     */
    public static $collect;

    /**
     * @var array
     */
    public static $groups = [];

    /**
     * Open tag mode render.
     *
     * @var bool
     */
    public static $open_mode = false;

    /**
     * Tag names collection.
     *
     * @var Collection
     */
    public static $names;

    /**
     * Collection of components.
     *
     * @var Collection
     */
    public static $components;

    /**
     * Collection of complex components.
     *
     * @var Collection
     */
    public static $complex_components;

    /**
     * Tag Cashed storage.
     *
     * @var Collection
     */
    public static $storage;

    /**
     * Events collection.
     *
     * @var EventsCollection
     */
    protected static $events;

    /**
     * Cached tag collection.
     *
     * @var TagCollection
     */
    protected static $cached;

    /**
     * Global executor.
     *
     * @var array
     */
    public static $global_executor = [];

    /**
     * Injected file list.
     *
     * @var array
     */
    public static $injected_files = [];

    /**
     * @var string
     */
    public static $_tmp_handler_name = null;

    /**
     * TagStatic constructor.
     */
    public function __construct()
    {
        static::initCollections();
    }

    /**
     * @return array
     */
    public static function getMacros()
    {
        return static::$macros;
    }

    /**
     * Cache dom collection.
     */
    public static function cacheCollect()
    {
        if (static::$collect) {
            if (\Auth::guest()) {
                \Cache::forever(\App::getLocale().':tag:collect', static::$collect);
            } else {
                \Cache::add(\App::getLocale().':'.\Auth::id().':tag:collect', static::$collect, now()->addMinutes(30));
            }
        }
    }

    /**
     * Restore tag collection.
     */
    public static function restoreCollectFromCache()
    {
        if (\Auth::guest()) {
            static::$cached = \Cache::get(\App::getLocale().':tag:collect');
        } else {
            static::$cached = \Cache::get(\App::getLocale().':'.\Auth::id().':tag:collect');
        }
    }

    /**
     * Reset collection.
     */
    public static function resetCollect()
    {
        static::$collect = new TagCollection();
    }

    /**
     * Selector.
     *
     * @param string $selector
     * @return \Illuminate\Support\Collection|\Lar\Tagable\Core\FindCollection
     * @throws \Exception
     */
    public static function selector(string $selector)
    {
        if ($tag = static::$names->has($selector)) {
            return static::$names->get($selector);
        }

        $tags = (new Selector())->parser($selector)->find();

        if ($tags->count() == 1) {
            if ($tags->first() instanceof Collection && $tags->first()->count() == 1) {
                return $tags->first()->first();
            } else {
                return $tags->first();
            }
        } else {
            return $tags;
        }
    }

    /**
     * Init all collections on process.
     */
    private static function initCollections()
    {
        if (! static::$collect instanceof TagCollection) {
            static::$collect = new TagCollection();
        }

        if (! static::$events instanceof EventsCollection) {
            static::$events = new EventsCollection();
        }

        if (! static::$names instanceof Collection) {
            static::$names = new Collection();
        }
    }

    /**
     * Add tag width name.
     *
     * @param string $name
     * @param Tag $tag
     */
    public static function addName(string $name, Tag $tag)
    {
        static::$names->put($name, $tag);
    }

    /**
     * Get Caller.
     *
     * @param int $offset
     * @return array
     */
    protected static function getCaller($offset = 0)
    {
        $baseOffset = 2;
        $offset += $baseOffset;
        $backtrace = debug_backtrace();
        $caller = [];
        if (isset($backtrace[$offset])) {
            $backtrace = $backtrace[$offset];
            if (isset($backtrace['class'])) {
                $caller['class'] = $backtrace['class'];
            }
            if (isset($backtrace['function'])) {
                $caller['function'] = $backtrace['function'];
            }
        }

        return $caller;
    }

    /**
     * Static alias from method when.
     *
     * @return Tag|static
     */
    public static function cover()
    {
        $newObject = new static();

        if (count(func_get_args())) {
            $newObject->when(...func_get_args());
        }

        return $newObject;
    }

    /**
     * Registration component.
     *
     * @param string $name
     * @param \Closure|array|string $component
     * @throws \Exception
     */
    public static function registerComponent(string $name, $component)
    {
        if (is_string($component) || is_embedded_call($component)) {
            if (! static::$components) {
                static::$components = new Collection();
            }

            static::$components->put($name, $component);
        } else {
            throw new \Exception('Undefined type component');
        }
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public static function getComponents()
    {
        if (! static::$components) {
            static::$components = new Collection();
        }

        return static::$components;
    }

    /**
     * Check if item exists or not.
     *
     * @param string $name
     * @return bool
     */
    public static function hasComponent(string $name)
    {
        return static::$components->has($name);
    }

    /**
     * Inject collection from file.
     *
     * @param $file
     * @throws \Exception
     */
    public static function injectFile($file)
    {
        $data = require $file;

        static::$injected_files[] = $file;

        if (! is_array($data)) {
            throw new \Exception('File data must be component array');
        }

        static::injectCollection($data);
    }

    /**
     * Inject collection in to component collection.
     *
     * @param array $collection
     * @param bool $complex
     */
    public static function injectCollection($collection = [], $complex = false)
    {
        if (is_array($collection) || $collection instanceof Collection) {
            if (! static::$components) {
                static::$components = new Collection();
            }

            if (! static::$complex_components) {
                static::$complex_components = new Collection([]);
            }

            if ($complex) {
                static::$complex_components = static::$complex_components->merge($collection);
            }

            static::$components = static::$components->merge($collection);
        }
    }

    /**
     * Component getter.
     *
     * @param string $name
     * @return mixed
     * @throws \Exception
     */
    public static function getComponent(string $name)
    {
        if (static::$components->has($name)) {
            $component = static::$components->get($name);

            if (is_embedded_call($component)) {
                return call_user_func($component);
            } elseif (is_string($component)) {
                return new $component;
            }
        } else {
            throw new \Exception("Component [{$name}] not found!");
        }
    }

    /**
     * Static create.
     *
     * @param array $data
     * @return static
     */
    public static function create(...$data)
    {
        return new static(...$data);
    }
}
