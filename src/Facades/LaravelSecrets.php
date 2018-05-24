<?php

namespace Alavida\LaravelSecrets\Facades;

use Illuminate\Support\Facades\Facade;

class LaravelSecrets extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'laravelsecrets';
    }
}
