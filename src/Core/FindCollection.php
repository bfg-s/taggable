<?php

namespace Lar\Tagable\Core;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Support\Collection;
use Lar\Tagable\Tag;

class FindCollection extends Collection implements Renderable
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

    /**
     * Find tags in in result collection.
     *
     * @param string $selector
     * @return $this|FindCollection
     * @throws \Exception
     */
    public function find(string $selector)
    {
        $find_collection = new static();

        if ($this->count() == 1) {
            $first = $this->first();
            $return = $first->find($selector);

            return $return;
        }

        $select = new Selector();

        $condition = $select->pars($selector);

        $this->map(function ($item) use ($find_collection, $select, $selector, $condition) {
            if ($item instanceof Tag && $select->compare($condition[$selector], $item)) {
                $find_collection->put($item['id'], $item);
            }
        });

        if ($find_collection->count() == 1) {
            return $find_collection->first();
        } else {
            return $find_collection;
        }
    }

    /**
     * A wrapper over all components of the collection.
     *
     * @param $data
     * @return $this
     */
    public function cover($data)
    {
        $this->map(function ($item) use ($data) {
            $item->when([$data]);
        });

        return $this;
    }

    /**
     * Get the evaluated contents of the object.
     *
     * @return string
     */
    public function render()
    {
        return $this->map(function ($item) {
            if ($item instanceof Renderable) {
                return $item->render();
            }

            return '';
        })->implode('');
    }
}
