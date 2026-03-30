<?php

namespace GeneaLabs\LaravelSocialiter;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Laravel\Socialite\AbstractUser;
use Laravel\Socialite\Facades\Socialite;

class Socialiter
{
    public static $runsMigrations = true;

    /** @var callable|null */
    protected static $userCreator = null;

    protected $isStateless = false;
    protected $config;
    protected $driver;
    protected $apiToken;

    public static function ignoreMigrations(): void
    {
        static::$runsMigrations = false;
    }

    /**
     * Register a custom user-creation callback.
     *
     * The callback receives the Socialite user and should return an
     * Eloquent User model instance (persisted).
     *
     * @param  callable(AbstractUser): Model  $callback
     */
    public static function createUsersUsing(callable $callback): void
    {
        static::$userCreator = $callback;
    }

    /**
     * Reset the custom user-creation callback to the default behavior.
     */
    public static function createUsersUsingDefault(): void
    {
        static::$userCreator = null;
    }

    public function driver(string $driver): self
    {
        $this->driver = $driver;

        return $this;
    }

    public function login(): Model
    {
        $socialite = Socialite::driver($this->driver);

        if ($this->config) {
            $socialite = $socialite
                ->setConfig($this->config);
        }

        if ($this->isStateless) {
            $socialite = $socialite->stateless();
        }

        $socialiteUser = $socialite->user();

        return $this->performLogin($socialiteUser);
    }

    public function apiLogin(AbstractUser $socialiteUser, string $apiToken): Model
    {
        $this->apiToken = $apiToken;

        return $this->performLogin($socialiteUser);
    }

    protected function performLogin(AbstractUser $socialiteUser): Model
    {
        $user = $this
            ->getUser($socialiteUser, $this->driver);
        $user->load("socialCredentials");

        auth()->login($user);

        return $user;
    }

    protected function getUser(AbstractUser $socialiteUser): Model
    {
        return $this
            ->createCredentials($socialiteUser)
            ->user;
    }

    protected function createUser(AbstractUser $socialiteUser): Model
    {
        $userClass = config("auth.providers.users.model");
        $user = (new $userClass)
            ->where("email", $socialiteUser->getEmail())
            ->first();

        if ($user) {
            return $user;
        }

        if (static::$userCreator) {
            return call_user_func(static::$userCreator, $socialiteUser);
        }

        return (new $userClass)->create([
            "email" => $socialiteUser->getEmail(),
            "name" => $socialiteUser->getName(),
            "password" => Str::random(64),
        ]);
    }

    protected function createCredentials(AbstractUser $socialiteUser): SocialCredentials
    {
        $credentialsModel = SocialCredentials::model();
        $socialiteCredentials = (new $credentialsModel)
            ->with("user")
            ->firstOrNew([
                "provider_id" => $socialiteUser->getId(),
                "provider_name" => $this->driver,
            ])
            ->fill([
                "access_token" => $socialiteUser->token,
                "avatar" => $socialiteUser->getAvatar(),
                "email" => $socialiteUser->getEmail(),
                "expires_at" => (new Carbon)->now()->addSeconds($socialiteUser->expiresIn),
                "name" => $socialiteUser->getName(),
                "nickname" => $socialiteUser->getNickname(),
                "provider_id" => $socialiteUser->getId(),
                "provider_name" => $this->driver,
                "refresh_token" => $socialiteUser->refreshToken,
            ]);

        if (! $socialiteCredentials->exists) {
            $user = $this->createUser($socialiteUser);
            $socialiteCredentials->user()->associate($user);
        }

        $socialiteCredentials->save();

        return $socialiteCredentials;
    }

    public function setConfig($config): self
    {
        $this->config = $config;

        return $this;
    }

    public function stateless(): self
    {
        $this->isStateless = true;

        return $this;
    }
}
