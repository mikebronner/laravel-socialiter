<?php

namespace GeneaLabs\LaravelSocialiter\Traits;

use GeneaLabs\LaravelSocialiter\SocialCredentials as GeneaLabsSocialCredentials;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait SocialCredentials
{
    public function socialCredentials(): HasMany
    {
        return $this->hasMany(GeneaLabsSocialCredentials::class);
    }
}
