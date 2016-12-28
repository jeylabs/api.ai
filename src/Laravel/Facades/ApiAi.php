<?php

namespace Jeylabs\ApiAi\Laravel\Facades;

use Illuminate\Support\Facades\Facade;

class ApiAi extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'apiai';
    }
}
