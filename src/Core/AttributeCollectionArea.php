<?php

namespace Lar\Tagable\Core;

use Illuminate\Support\Collection;
use Lar\Tagable\Core\Elements\AttributeClass;
use Lar\Tagable\Core\Elements\AttributeId;
use Lar\Tagable\Core\Elements\AttributeLjs;
use Lar\Tagable\Core\Elements\AttributeText;
use Lar\Tagable\Core\Extension\Attributable;
use Lar\Tagable\Core\Extension\Tagable;
use Lar\Tagable\Tag;

class AttributeCollectionArea extends Collection implements Tagable
{
    /**
     * Affiliation of tags.
     *
     * @var array
     */
    protected $affiliation = [
        'id' => AttributeId::class,
        'class' => AttributeClass::class,
        'ljs' => AttributeLjs::class,
        '*' => AttributeText::class,
    ];

    /**
     * ReName map.
     *
     * @var array
     */
    protected $name_map = [
        'ljs' => 'data-ljs',
    ];

    /**
     * Global Affiliation map.
     *
     * @var array
     */
    private static $global_affiliation = [];

    /**
     * Parent tag class.
     *
     * @var null | Tag
     */
    protected $tag = null;

    /**
     * Add attribute in to collection.
     *
     * @param $attribute
     * @param string $value
     * @return AttributeCollectionArea
     * @throws \Exception
     */
    public function add($attribute, $value = ''): self
    {
        $new_value = null;

        if (isset($this->tag->execute['attribute']) && is_string($this->tag->execute['attribute'])) {
            $new_value = $this->tag->{$this->tag->execute['attribute']}($attribute, $value);
        }

        if (isset($this->tag->execute[$attribute]) && is_string($this->tag->execute[$attribute])) {
            $new_value = $this->tag->{$this->tag->execute[$attribute]}($attribute, $value);
        }

        if ((is_string($value) || is_numeric($value)) && isset($this->tag->execute[$attribute][$value]) && is_string($this->tag->execute[$attribute][$value])) {
            $new_value = $this->tag->{$this->tag->execute[$attribute][$value]}($attribute, $value);
        }

        if (! is_null($new_value) && $new_value !== false) {
            $value = $new_value;
        }

        if (isset($this->affiliation[$attribute])) {
            $class = $this->affiliation[$attribute];
        } else {
            $class = $this->affiliation['*'];
        }

        if (is_string($value) && Aliases::getAliases()->has($value)) {
            $value = Aliases::getAliases()->get((string) $value);
        }

        if ($this->has($attribute)) {
            $class = $this->get($attribute);
        } else {
            $class = (new $class())->setTag($this->tag);
        }

        if (! $class instanceof Attributable) {
            throw new \Exception('Undefined attribute type');
        }

        $class->setValue($value);

        $this->put(isset($this->name_map[$attribute]) ? $this->name_map[$attribute] : $attribute, $class);

        return $this;
    }

    /**
     * Remove attribute.
     *
     * @param $name
     * @return $this
     */
    public function remove($name)
    {
        $this->items = $this->except($name)->all();

        return $this;
    }

    /**
     * Get render string data for tag.
     *
     * @return string
     */
    public function toTag(): string
    {
        $return = $this->count() ? ' '.$this->map(function ($item, $key) {
            $val = $item->getValue();

            if ($val != '') {
                if (strpos($val, '"') === false) {
                    $val = "\"{$val}\"";
                } else {
                    $val = "'".str_replace("'", '', $val)."'";
                }

                return "{$key}={$val}";
            } else {
                return "{$key}";
            }
        })->implode(' ') : '';

        return $return;
    }

    /**
     * Parent tag setter.
     *
     * @param Tag|null $tag
     * @return AttributeCollectionArea
     */
    public function setTag(?Tag $tag = null)
    {
        $this->tag = $tag;

        return $this;
    }

    /**
     * Magic debug.
     *
     * @return array
     */
    public function __debugInfo()
    {
        return $this->items;
    }

    /**
     * Add class in to affiliation list.
     *
     * @param string $name
     * @param string $class_name
     * @return array
     */
    public static function affiliation(string $name, string $class_name)
    {
        static::$global_affiliation[$name] = $class_name;

        return static::$global_affiliation;
    }
}
