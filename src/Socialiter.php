<?php

namespace GeneaLabs\LaravelSocialiter;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Laravel\Socialite\AbstractUser;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User;

class Socialiter
{
    public static $runsMigrations = true;

    public static function ignoreMigrations()
    {
        static::$runsMigrations = false;
    }

    public function authenticate(
        string $driver,
        array $scopes = [],
        bool $isStateless = false,
        array $parameters = []
    ) : RedirectResponse {
        $socialite = Socialite::driver($driver);

        if ($isStateless) {
            $socialite = $socialite->stateless();
        }
        
        return $socialite->scopes($scopes)
            ->with($parameters)
            ->redirect();
    }

    public function login(string $driver) : Model
    {
        $socialiteUser = Socialite::driver($driver)
            ->user();
        $user = $this->getUser($socialiteUser, $driver);
        $user->load("socialCredentials");

        auth()->login($user);
    
        return $user;
    }

    protected function getUser(AbstractUser $socialiteUser, string $driver) : Model
    {
        dump($socialiteUser->refreshToken, $socialiteUser);

        $socialiteCredentials = $this->createCredentials(
            $socialiteUser,
            $driver
        );

        return $socialiteCredentials->user;
    }

    protected function createUser(AbstractUser $socialiteUser) : Model
    {
        $userClass = config("auth.providers.users.model");
        $user = (new $userClass)
            ->fill([
                "name" => $socialiteUser->getName(),
                "email" => $socialiteUser->getEmail(),
                "password" => Str::random(64),
            ]);
        $user->save();

        return $user;
    }

    protected function createCredentials(
        AbstractUser $socialiteUser,
        string $driver
    ) : SocialCredentials {
        dump($socialiteUser->refreshToken);
        $socialiteCredentials = (new SocialCredentials)
            ->with("user")
            ->firstOrNew([
                "provider_id" => $socialiteUser->getId(),
                "provider_name" => $driver,
            ])
            ->fill([
                "access_token" => $socialiteUser->token,
                "avatar" => $socialiteUser->getAvatar(),
                "email" => $socialiteUser->getEmail(),
                "expires_at" => (new Carbon)->now()->addSeconds($socialiteUser->expiresIn),
                "name" => $socialiteUser->getName(),
                "nickname" => $socialiteUser->getNickname(),
                "provider_id" => $socialiteUser->getId(),
                "provider_name" => $driver,
                "refresh_token" => $socialiteUser->refreshToken,
            ]);

        if (! $socialiteCredentials->exists) {
            $user = $this->createUser($socialiteUser);
            $socialiteCredentials->user()->associate($user);
        }

        $socialiteCredentials->save();

        return $socialiteCredentials;
    }
}
