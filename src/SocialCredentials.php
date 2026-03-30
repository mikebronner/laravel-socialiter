<?php

declare(strict_types=1);

namespace GeneaLabs\LaravelSocialiter;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SocialCredentials extends Model
{
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

    protected function casts(): array
    {
        return [
            "expires_at" => "datetime",
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(config("auth.providers.users.model"));
    }
}
