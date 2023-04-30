<?php

namespace Lar\Tagable;

use Illuminate\Http\Resources\Json\JsonResource;
use Lar\Layout\Abstracts\Component;
use Illuminate\Support\Str;

/**
 * Class Vue.
 *
 * @package Lar\Tagable
 */
class Vue extends Component
{
    /**
     * @var bool
     */
    protected $vue = true;

    /**
     * @var bool
     */
    public static $is_vue = true;

    /**
     * @var array
     */
    protected static $count = [];

    /**
     * Vue constructor.
     *
     * @param  string|null|array  $id
     * @param  array  $attrs
     * @param  array  $params
     */
    public function __construct($id = null, array $attrs = [], ...$params)
    {
        if (! $this->name && $this->element) {
            $this->name = $this->element;
        }

        if (! $this->handler_name && $this->element) {
            $this->handler_name = $this->element;
        }

        if (! isset(self::$count[$this->handler_name])) {
            self::$count[$this->handler_name] = 0;
        } else {
            self::$count[$this->handler_name]++;
        }

        $num = self::$count[$this->handler_name];

        parent::__construct();

        if (is_array($id)) {
            $attrs = array_merge($attrs, $id);
            $id = null;
        }

        if (! $id) {
            $id = Str::slug($this->handler_name, '_').($num ? '_'.$num : '');
        }

        $route = \Route::currentRouteName() ?? 'components';

        if ($id) {
            $this->setName($id);
        }

        $this->attr('id', (! empty($route) ? str_replace('.', '_', $route).'_' : '').($id ?? $this->getUnique()));

        foreach ($attrs as $key => $attr) {
            if ($attr instanceof JsonResource) {
                $attr = $attr->toArray(request());
            }
            $this->attr($key, $attr);
        }

        $this->when($params);

        $this->on_load('vue::init');
    }
}
