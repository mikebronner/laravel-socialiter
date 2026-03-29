<?php

use GeneaLabs\LaravelSocialiter\SocialCredentials;
use GeneaLabs\LaravelSocialiter\Socialiter;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Two\User as SocialiteUser;

beforeEach(function () {
    config(['auth.providers.users.model' => TestUser::class]);
});

function makeSocialiteUser(array $overrides = []): SocialiteUser
{
    $user = new SocialiteUser;
    $user->id = $overrides['id'] ?? 'provider-123';
    $user->name = $overrides['name'] ?? 'Jane Doe';
    $user->email = $overrides['email'] ?? 'jane@example.com';
    $user->token = $overrides['token'] ?? 'access-token-abc';
    $user->refreshToken = $overrides['refreshToken'] ?? 'refresh-token-xyz';
    $user->expiresIn = $overrides['expiresIn'] ?? 3600;
    $user->avatar = $overrides['avatar'] ?? 'https://example.com/avatar.jpg';
    $user->nickname = $overrides['nickname'] ?? 'janedoe';

    return $user;
}

it('does not change password for existing user on first social login', function () {
    $hashedPassword = Hash::make('original-secret');
    $user = TestUser::create([
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'password' => $hashedPassword,
    ]);

    $socialiteUser = makeSocialiteUser();

    $socialiter = new Socialiter;
    $reflection = new ReflectionClass($socialiter);

    $driverProp = $reflection->getProperty('driver');
    $driverProp->setAccessible(true);
    $driverProp->setValue($socialiter, 'google');

    $method = $reflection->getMethod('createCredentials');
    $method->setAccessible(true);
    $credential = $method->invoke($socialiter, $socialiteUser);

    $user->refresh();

    expect($user->password)->toBe($hashedPassword)
        ->and($credential->user_id)->toBe($user->id)
        ->and($credential->provider_name)->toBe('google')
        ->and($credential->provider_id)->toBe('provider-123');
});

it('creates a new user when no existing user matches the email', function () {
    $socialiteUser = makeSocialiteUser([
        'email' => 'newuser@example.com',
        'name' => 'New User',
    ]);

    $socialiter = new Socialiter;
    $reflection = new ReflectionClass($socialiter);

    $driverProp = $reflection->getProperty('driver');
    $driverProp->setAccessible(true);
    $driverProp->setValue($socialiter, 'github');

    $method = $reflection->getMethod('createCredentials');
    $method->setAccessible(true);
    $credential = $method->invoke($socialiter, $socialiteUser);

    $user = TestUser::where('email', 'newuser@example.com')->first();

    expect($user)->not->toBeNull()
        ->and($user->name)->toBe('New User')
        ->and($credential->user_id)->toBe($user->id)
        ->and($credential->provider_name)->toBe('github');
});

it('creates a social credential linked to existing user by email', function () {
    $user = TestUser::create([
        'name' => 'Existing User',
        'email' => 'existing@example.com',
        'password' => Hash::make('my-password'),
    ]);

    $socialiteUser = makeSocialiteUser([
        'email' => 'existing@example.com',
        'id' => 'provider-456',
    ]);

    $socialiter = new Socialiter;
    $reflection = new ReflectionClass($socialiter);

    $driverProp = $reflection->getProperty('driver');
    $driverProp->setAccessible(true);
    $driverProp->setValue($socialiter, 'google');

    $method = $reflection->getMethod('createCredentials');
    $method->setAccessible(true);
    $credential = $method->invoke($socialiter, $socialiteUser);

    expect($credential->user_id)->toBe($user->id)
        ->and(SocialCredentials::where('user_id', $user->id)->count())->toBe(1);
});

it('authenticates normally for users with existing social credential', function () {
    $user = TestUser::create([
        'name' => 'Returning User',
        'email' => 'returning@example.com',
        'password' => Hash::make('password'),
    ]);

    $credentialModel = SocialCredentials::model();
    (new $credentialModel)->create([
        'user_id' => $user->id,
        'provider_id' => 'provider-789',
        'provider_name' => 'google',
        'access_token' => 'old-token',
        'refresh_token' => 'old-refresh',
        'expires_at' => now()->addHour(),
        'avatar' => 'https://example.com/avatar.jpg',
        'email' => 'returning@example.com',
        'name' => 'Returning User',
        'nickname' => 'returner',
    ]);

    $socialiteUser = makeSocialiteUser([
        'email' => 'returning@example.com',
        'id' => 'provider-789',
        'token' => 'new-token',
        'refreshToken' => 'new-refresh',
    ]);

    $socialiter = new Socialiter;
    $reflection = new ReflectionClass($socialiter);

    $driverProp = $reflection->getProperty('driver');
    $driverProp->setAccessible(true);
    $driverProp->setValue($socialiter, 'google');

    $method = $reflection->getMethod('createCredentials');
    $method->setAccessible(true);
    $credential = $method->invoke($socialiter, $socialiteUser);

    expect($credential->user_id)->toBe($user->id)
        ->and($credential->access_token)->toBe('new-token')
        ->and($credential->refresh_token)->toBe('new-refresh')
        ->and(TestUser::count())->toBe(1);
});

it('does not create duplicate users on subsequent social logins', function () {
    $socialiteUser = makeSocialiteUser([
        'email' => 'unique@example.com',
        'id' => 'provider-abc',
    ]);

    $socialiter = new Socialiter;
    $reflection = new ReflectionClass($socialiter);

    $driverProp = $reflection->getProperty('driver');
    $driverProp->setAccessible(true);
    $driverProp->setValue($socialiter, 'google');

    $method = $reflection->getMethod('createCredentials');
    $method->setAccessible(true);

    // First login
    $method->invoke($socialiter, $socialiteUser);
    // Second login
    $method->invoke($socialiter, $socialiteUser);

    expect(TestUser::where('email', 'unique@example.com')->count())->toBe(1)
        ->and(SocialCredentials::where('provider_id', 'provider-abc')->count())->toBe(1);
});

// Test model used for testing
class TestUser extends Authenticatable
{
    protected $table = 'users';
    protected $guarded = [];
}
