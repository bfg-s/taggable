<?php

namespace Lar\Tagable\Core;

/**
 * Class MatchesCollection.
 *
 * @package Lar\Tagable\Core
 */
class MatchesCollection extends FindCollection
{
    /**
     * MatchesCollection constructor.
     *
     * @param array $items
     */
    public function __construct($items = [])
    {
        parent::__construct($items);
    }
}
