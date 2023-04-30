<?php

namespace Lar\Tagable\Core\Elements;

use Lar\Tagable\Core\Extension\Attribute;

class AttributeId extends Attribute
{
    /**
     * Events.
     *
     * @var array
     */
    protected static $events = [];

    /**
     * {@inheritdoc}
     * @throws \Exception
     */
    public function setValue($value): void
    {
        parent::setValue($value);

        if (is_string($this->value) && isset(static::$events[$this->value]) && is_embedded_call(static::$events[$this->value])) {
            $event = static::$events[$this->value];

            call_user_func($event, $this->tag);
        }
    }

    /**
     * Add attr event.
     *
     * @param string $event_name
     * @param \Closure|array $call
     */
    public static function addEvent(string $event_name, $call)
    {
        if (is_embedded_call($call)) {
            static::$events[$event_name] = $call;
        }
    }
}
