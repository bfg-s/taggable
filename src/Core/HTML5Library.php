<?php

namespace Lar\Tagable\Core;

use Illuminate\Support\Collection;

/**
 * Class HTML5Library.
 *
 * @package Lar\Tagable\Core
 */
class HTML5Library
{
    /**
     * Tags collection.
     *
     * @var Collection
     */
    public static $tags;

    /**
     * Tags extend collection.
     *
     * @var Collection
     */
    public static $tags_extends = [];

    /**
     * Tag attribute collection.
     *
     * @var Collection
     */
    public static $attributes;

    /**
     * Dom events collection.
     *
     * @var Collection
     */
    public static $events;

    /**
     * HTML5Library constructor.
     */
    public function __construct()
    {
        static::init();
    }

    /**
     * Initialization library.
     */
    public static function init()
    {
        if (! static::$tags) {
            static::$tags = new Collection(require __DIR__.'/../HTML5Library/tags.php');
        }

        if (! static::$attributes) {
            static::$attributes = new Collection(require __DIR__.'/../HTML5Library/attribute.php');
        }

        if (! static::$events) {
            static::$events = new Collection(require __DIR__.'/../HTML5Library/events.php');
        }
    }

    /**
     * Library tags getter method.
     *
     * @return \Illuminate\Support\Collection
     */
    public static function getTags(): Collection
    {
        static::init();

        return static::$tags->merge(static::$tags_extends);
    }

    /**
     * Library tag attribute getter method.
     *
     * @return \Illuminate\Support\Collection
     */
    public static function getAttributes(): Collection
    {
        static::init();

        return static::$attributes;
    }

    /**
     * Library tag event getter method.
     *
     * @return \Illuminate\Support\Collection
     */
    public static function getEvents(): Collection
    {
        static::init();

        return static::$events;
    }

    /**
     * Add tag in to default HTML5 list.
     *
     * @param $name
     * @param int $closed
     * @return \Illuminate\Support\Collection
     */
    public static function addTag($name, $closed = 1)
    {
        static::init();

        static::$tags->put($name, $closed);

        return static::$tags;
    }

    /**
     * Add attribute in to default HTML5 list.
     *
     * @param $name
     * @param array $belongs_to
     * @return \Illuminate\Support\Collection
     */
    public static function addAttribute($name, $belongs_to = ['*'])
    {
        static::init();

        static::$attributes->put($name, $belongs_to);

        return static::$attributes;
    }

    /**
     * Add event name in to default HTML5 list.
     *
     * @param $name
     * @return \Illuminate\Support\Collection
     */
    public static function addEvent($name)
    {
        static::init();

        static::$events->put($name, 1);

        return static::$events;
    }
}
