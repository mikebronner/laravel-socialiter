<?php

declare(strict_types=1);

namespace GeneaLabs\LaravelSocialiter\Facades;

use Illuminate\Support\Facades\Facade;

class Socialiter extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'socialiter';
    }
}
