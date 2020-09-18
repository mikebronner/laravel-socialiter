<?php

namespace GeneaLabs\LaravelSocialiter;

use GeneaLabs\LaravelOverridableModel\Traits\Overridable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SocialCredentials extends Model
{
    use Overridable;

    protected $dates = [
        "expires_at",
    ];
    protected $fillable = [
        "access_token",
        "avatar",
        "email",
        "expires_at",
        "name",
        "nickname",
        "provider_id",
        "provider_name",
        "refresh_token",
        "user_id",
    ];

    public function user() : BelongsTo
    {
        $userClass = config("auth.providers.users.model");

        return $this->belongsTo($userClass);
    }
}
