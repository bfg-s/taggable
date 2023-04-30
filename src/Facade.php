<?php

namespace Lar\Tagable;

use Illuminate\Support\Facades\Facade as FacadeIlluminate;

class Facade extends FacadeIlluminate
{
    protected static function getFacadeAccessor()
    {
        return Tag::class;
    }
}
