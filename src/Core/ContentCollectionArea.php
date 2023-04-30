<?php

namespace Lar\Tagable\Core;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Support\Collection;
use Lar\Tagable\Core\Elements\ContentObject;
use Lar\Tagable\Core\Elements\ContentText;
use Lar\Tagable\Core\Extension\Tagable;
use Lar\Tagable\Tag;

class ContentCollectionArea extends Collection implements Tagable
{
    /**
     * Affiliation of contents.
     *
     * @var array
     */
    protected $affiliation = [
        'string' => ContentText::class,
        'object' => ContentObject::class,
        '*' => ContentText::class,
    ];

    /**
     * Parent tag class.
     *
     * @var null | Tag
     */
    protected $tag = null;

    /**
     * Add data in to collection.
     *
     * @param $value
     * @param bool $prepend
     * @return mixed
     */
    public function add($value, $prepend = false)
    {
        $this->tag->unRender();

        $new_value = null;

        if (isset($this->tag->execute['content']) && is_string($this->tag->execute['content'])) {
            $new_value = $this->tag->{$this->tag->execute['content']}($value);
        }

        if ((is_string($value) || is_numeric($value)) && isset($this->tag->execute['content'][$value]) && is_string($this->tag->execute['content'][$value])) {
            $new_value = $this->tag->{$this->tag->execute['content'][$value]}($value);
        }

        if (! is_null($new_value) && $new_value !== false) {
            $value = $new_value;
        }

        if ((is_string($value) || is_numeric($value)) && Aliases::getAliases()->has((string) $value)) {
            $value = Aliases::getAliases()->get((string) $value);
        }

        $type = gettype($value);

        if ($value instanceof Collection) {
            $value->map(function ($item) use ($prepend) {
                $this->add($item, $prepend);
            });

            return $this;
        }

        if (isset($this->affiliation[$type])) {
            $class = $this->affiliation[$type];
        } else {
            $class = $this->affiliation['*'];
        }

        if ($value instanceof Tag) {
            $value->unRender();
        }

        if (! $prepend) {
            $this->push(new $class($value, $this->tag));
        } else {
            $this->prepend(new $class($value, $this->tag));
        }

        return $this;
    }

    /**
     * To tag data.
     *
     * @param string $impl_simbol
     * @return string
     */
    public function toTag($impl_simbol = '')
    {
        return $this->map(function ($item) {
            if ($item instanceof Renderable) {
                return $item->render();
            } else {
                if (is_string($item)) {
                    return $item;
                }

                return $item->getValue();
            }
        })->implode($impl_simbol);
    }

    /**
     * Parent tag setter.
     *
     * @param Tag|null $tag
     * @return \Lar\Tagable\Core\ContentCollectionArea
     */
    public function setTag(?Tag $tag = null)
    {
        $this->tag = $tag;

        return $this;
    }

    /**
     * @param  array  $items
     * @return $this
     */
    public function setItems(array $items)
    {
        $this->items = $items;

        return $this;
    }

    public function __debugInfo()
    {
        return $this->items;
    }
}
