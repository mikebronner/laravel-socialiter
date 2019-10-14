<?php

namespace GeneaLabs\LaravelSocialiter\Facades;

use Illuminate\Support\Facades\Facade;

class Socialiter extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'socialiter';
    }
}
