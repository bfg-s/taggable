<?php

namespace Lar\Tagable\Core;

use Illuminate\Support\Collection;

final class EventsCollection extends Collection
{
    /**
     * TagCollection constructor.
     *
     * @param array $items
     */
    public function __construct($items = [])
    {
        parent::__construct($items);
    }
}
