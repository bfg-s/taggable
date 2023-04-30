<?php

namespace Lar\Tagable\Core;

use Illuminate\Contracts\Support\Renderable;
use Lar\Tagable\Tag;

final class TagCollection extends FindCollection implements Renderable
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
     * Registration tag in to dom.
     *
     * @param $tag
     * @param Tag $self
     * @return mixed
     * @throws \Exception
     */
    public function registrationTag(Tag $self)
    {
        $id = $this->count();

        $this->put($id, $self);

        return $id;
    }

    /**
     * @param $id
     * @return $this
     */
    public function drop($id)
    {
        $this->forget($id);

        return $this;
    }

    /**
     * Get the evaluated contents of the object.
     *
     * @return string
     */
    public function render()
    {
        return '';
    }
}
