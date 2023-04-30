<?php

if (! function_exists('is_tag')) {

    /**
     * Check whether the text is a true HTML tag.
     *
     * @param string $tag
     * @return bool
     */
    function is_tag($tag)
    {
        return \Lar\Tagable\Core\HTML5Library::getTags()->has($tag);
    }
}

if (! function_exists('is_tag_closing')) {

    /**
     * Check is closing tag or whole.
     *
     * @param string $tag
     * @return bool
     */
    function is_tag_closing($tag)
    {
        if (! is_tag($tag)) {
            return true;
        } else {
            return \Lar\Tagable\Core\HTML5Library::getTags()->get($tag) ? true : false;
        }
    }
}

if (! function_exists('tag')) {

    /**
     * Tag helper.
     *
     * @param $element
     * @param $attributes
     * @return \Lar\Tagable\Tag
     * @throws Exception
     */
    function tag($element = null, $attributes = []) : Lar\Tagable\Tag
    {
        return new \Lar\Tagable\Tag($element, $attributes);
    }
}

if (! function_exists('_s')) {

    /**
     * Selector helper.
     *
     * @param mixed|string
     * @return \Lar\Tagable\Core\FindCollection|\Lar\Layout\LarDoc
     * @throws Exception
     */
    function _s()
    {
        return \Lar\Tagable\Tag::selector(implode(';', func_get_args()));
    }
}
