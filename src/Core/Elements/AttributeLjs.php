<?php

namespace Lar\Tagable\Core\Elements;

use Illuminate\Support\Collection;
use Lar\Layout\Respond;
use Lar\LJS\LJS;
use Lar\Tagable\Core\Extension\Attributable;
use Lar\Tagable\Tag;

class AttributeLjs extends Collection implements Attributable
{
    /**
     * Parent tag class.
     *
     * @var null | Tag
     */
    protected $tag = null;

    /**
     * @var LJS
     */
    protected $js;

    protected $unique;

    /**
     * Attributable constructor.
     *
     * @param mixed $value
     */
    public function __construct($value = '')
    {
        parent::__construct([]);
    }

    /**
     * Attribute value getter.
     *
     * @return string
     */
    public function getValue(): string
    {
        $return = [];

        foreach ($this->items as $key => $item) {
            $return[] = ($key === 'begin' ? '' : "{$key}:").json_encode($item, JSON_FORCE_OBJECT);
        }

        $data = implode('||', $return);

        $data = base64_encode($data);

        $this->tag->js()->line("ljs._special_object(\"{$this->unique}\", \"".$data.'")');

        return $this->unique;
    }

    /**
     * Value setter.
     *
     * @param mixed $value
     * @throws \Exception
     */
    public function setValue($value)
    {
        $val = [];

        $event = null;

        if (is_array($value)) {
            $event = $value['event'];

            if ($value['exec'] instanceof Respond) {
                if ($this->has($value['event'])) {
                    $old = $this->get($value['event']);

                    $val = $old->merge([$value['exec']]);
                } else {
                    $val = $value['exec'];
                }
            } else {
                throw new \Exception('Must be ['.Respond::class.'] object');
            }
        }

        if ($event) {
            $this->put($event, $val);
        }
    }

    /**
     * Parent tag setter.
     *
     * @param Tag|null $tag
     * @return $this
     */
    public function setTag(?Tag $tag = null)
    {
        $this->tag = $tag;

        $this->js = $tag->js();

        $this->unique = $tag->getUnique();

        return $this;
    }
}
