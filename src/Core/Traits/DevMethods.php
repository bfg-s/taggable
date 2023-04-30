<?php

namespace Lar\Tagable\Core\Traits;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\Support\Renderable;
use Lar\Tagable\Events\onRender;
use Lar\Tagable\Tag;

trait DevMethods
{
    /**
     * Dump this tag.
     *
     * @return $this
     */
    public function dump()
    {
        dump($this);

        return $this;
    }

    /**
     * @var bool
     */
    protected $only_content = false;

    /**
     * @var null
     */
    protected $after = null;

    /**
     * @return $this
     */
    public function only_content()
    {
        $this->only_content = true;

        return $this;
    }

    /**
     * @var \Closure[]|array[]
     */
    protected $renderd = [];

    /**
     * Event after render.
     * @param  \Closure|array  $call
     * @return $this
     */
    public function rendered($call)
    {
        if (is_embedded_call($call)) {
            $this->renderd[] = $call;
        }

        return $this;
    }

    /**
     * Events after render.
     * @param  array  $call
     * @return $this
     */
    public function merge_rendered(array $call)
    {
        $this->renderd = array_merge($this->renderd, $call);

        return $this;
    }

    /**
     * Insert extended data.
     *
     * @param  mixed  $component
     */
    public function slot($component)
    {
        $this->appEnd($component);
    }

    /**
     * Get the evaluated contents of the object.
     *
     * @return string
     */
    public function render()
    {
        if ($this->bottom_content_mode) {
            $this->toBottom();
        }

        if (is_embedded_call($this->wrc)) {
            call_user_func($this->wrc, $this);
        }

        foreach ($this->execute as $key => $item) {
            if (is_numeric($key) && is_string($item)) {
                $this->{$item}();
            }
        }

        if ($this instanceof onRender) {
            $this->onRender();
        }

        $attributes = $this->attributes->toTag();
        $content = $this->ignoreContent ? '' : $this->content->toTag();
        $content .= implode('', array_map(function (Renderable $i) {
            return $i->render();
        }, $this->injected_groups));
        $content .= $this->bottom_content->toTag();

        if ($this->only_content) {
            $this->rendered = $content;
            $this->wrapper = '';
        }

        if (! $this->only_content && $this->ignore !== true) {
            $this->rendered = $this->isElement() && ! $this->ignoreWrapper ? $this->getNeedlePattern($attributes, $content) : $content;
            $this->wrapper = $this->isElement() ? $this->getNeedlePattern($attributes, '') : '';
        } elseif (! $this->only_content) {
            $this->rendered = '';
            $this->wrapper = '';
        }

        $this->hashes['WRAPPER'] = md5($this->wrapper);
        $this->hashes['RENDERED'] = md5($this->rendered);
        $this->hashes['CONTENT'] = $content != '' ? md5($content) : false;
        $this->hashes['ATTRIBUTES'] = $attributes != '' ? md5($attributes) : false;

        foreach ($this->renderd as $item) {
            call_user_func($item, $this);
        }

        if ($this->extend) {

            /** @var Tag $wrap */
            $wrap = new $this->extend();
            $wrap->{$this->slot}($this->rendered);
            $this->rendered = $wrap->render();
        }

        return $this->rendered;
    }

    /**
     * @param $data
     * @return $this
     */
    public function appEndToRendered($data)
    {
        if ($data instanceof Renderable) {
            $data = $data->render();
        }

        if ($data instanceof Htmlable) {
            $data = $data->toHtml();
        }

        $this->rendered .= (string) $data;

        return $this;
    }
}
