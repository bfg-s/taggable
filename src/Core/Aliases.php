<?php

namespace Lar\Tagable\Core;

use Illuminate\Support\Collection;

/**
 * Class Aliases.
 *
 * @package Lar\Tagable\Core
 */
class Aliases
{
    /**
     * Aliases.
     *
     * @var Collection
     */
    public static $aliases;

    /**
     * Aliases constructor.
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
        if (! static::$aliases) {
            static::$aliases = new Collection(require __DIR__.'/../HTML5Library/aliases.php');
        }
    }

    /**
     * Library aliases getter method.
     *
     * @return \Illuminate\Support\Collection
     */
    public static function getAliases(): Collection
    {
        static::init();

        return static::$aliases;
    }

    /**
     * Make alias.
     *
     * @param $proto
     * @param $value
     * @return \Illuminate\Support\Collection
     */
    public function makeAlias($proto, $value)
    {
        if (! preg_match('/\:.*/', $proto)) {
            $proto = ':'.$proto;
        }

        static::init();

        static::$aliases->put($proto, $value);

        return static::$aliases;
    }
}
