<?php

namespace Lar\Tagable\Core\Elements;

use Carbon\Carbon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\Support\Renderable;
use Lar\Tagable\Core\Extension\Content;

/**
 * Class ContentText.
 *
 * @package Lar\Tagable\Core
 */
class ContentObject extends Content
{
    /**
     * Value getter.
     *
     * @return string
     * @throws \Exception
     */
    public function getValue(): string
    {
        if ($this->value instanceof Renderable) {
            return $this->value->render();
        } elseif ($this->value instanceof  Htmlable) {
            return $this->value->toHtml();
        } else {
            return $this->value;
        }
    }

    /**
     * @param mixed $value
     * @throws \Exception
     */
    public function setValue($value): void
    {
        if ($value instanceof Renderable || $value instanceof Htmlable || $value instanceof Carbon) {
            $this->value = $value;
        } else {
            if ($value instanceof \Exception) {
                throw $value;
            }

            throw new \Exception('Only Renderable or Htmlable objects!');
        }
    }
}
