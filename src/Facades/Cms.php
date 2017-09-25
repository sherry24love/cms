<?php

namespace Sherry\Cms\Facades;

use Illuminate\Support\Facades\Facade;

class Cms extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Sherry\Cms\Cms::class;
    }
}
