<?php

use GeneaLabs\LaravelSocialiter\SocialCredentials;
use GeneaLabs\LaravelSocialiter\Socialiter;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Str;
use Laravel\Socialite\Two\User as SocialiteUser;

beforeEach(function () {
    config(['auth.providers.users.model' => CustomTestUser::class]);
    Socialiter::createUsersUsingDefault();

    // Add custom_field column to users table for testing
    if (! \Illuminate\Support\Facades\Schema::hasColumn('users', 'custom_field')) {
        \Illuminate\Support\Facades\Schema::table('users', function ($table) {
            $table->string('custom_field')->nullable();
        });
    }
});

afterEach(function () {
    Socialiter::createUsersUsingDefault();
});

function makeCustomSocialiteUser(array $overrides = []): SocialiteUser
{
    $user = new SocialiteUser;
    $user->id = $overrides['id'] ?? 'custom-provider-123';
    $user->name = $overrides['name'] ?? 'Custom User';
    $user->email = $overrides['email'] ?? 'custom@example.com';
    $user->token = $overrides['token'] ?? 'access-token-custom';
    $user->refreshToken = $overrides['refreshToken'] ?? 'refresh-token-custom';
    $user->expiresIn = $overrides['expiresIn'] ?? 3600;
    $user->avatar = $overrides['avatar'] ?? 'https://example.com/custom-avatar.jpg';
    $user->nickname = $overrides['nickname'] ?? 'customuser';

    return $user;
}

it('uses a custom user-creation callback when registered', function () {
    Socialiter::createUsersUsing(function (SocialiteUser $socialiteUser) {
        return CustomTestUser::create([
            'name' => $socialiteUser->getName(),
            'email' => $socialiteUser->getEmail(),
            'password' => 'custom-password',
            'custom_field' => 'custom-value',
        ]);
    });

    $socialiteUser = makeCustomSocialiteUser([
        'email' => 'callback@example.com',
        'name' => 'Callback User',
    ]);

    $socialiter = new Socialiter;
    $reflection = new ReflectionClass($socialiter);

    $driverProp = $reflection->getProperty('driver');
    $driverProp->setAccessible(true);
    $driverProp->setValue($socialiter, 'google');

    $method = $reflection->getMethod('createCredentials');
    $method->setAccessible(true);
    $credential = $method->invoke($socialiter, $socialiteUser);

    $user = CustomTestUser::where('email', 'callback@example.com')->first();

    expect($user)->not->toBeNull()
        ->and($user->name)->toBe('Callback User')
        ->and($user->custom_field)->toBe('custom-value')
        ->and($user->password)->toBe('custom-password')
        ->and($credential->user_id)->toBe($user->id);
});

it('falls back to default user creation when no callback is registered', function () {
    $socialiteUser = makeCustomSocialiteUser([
        'email' => 'default@example.com',
        'name' => 'Default User',
    ]);

    $socialiter = new Socialiter;
    $reflection = new ReflectionClass($socialiter);

    $driverProp = $reflection->getProperty('driver');
    $driverProp->setAccessible(true);
    $driverProp->setValue($socialiter, 'github');

    $method = $reflection->getMethod('createCredentials');
    $method->setAccessible(true);
    $credential = $method->invoke($socialiter, $socialiteUser);

    $user = CustomTestUser::where('email', 'default@example.com')->first();

    expect($user)->not->toBeNull()
        ->and($user->name)->toBe('Default User')
        ->and($user->custom_field)->toBeNull()
        ->and($credential->user_id)->toBe($user->id);
});

it('returns a valid persisted user model from the custom callback', function () {
    Socialiter::createUsersUsing(function (SocialiteUser $socialiteUser) {
        return CustomTestUser::create([
            'name' => $socialiteUser->getName(),
            'email' => $socialiteUser->getEmail(),
            'password' => Str::random(64),
            'custom_field' => 'persisted',
        ]);
    });

    $socialiteUser = makeCustomSocialiteUser([
        'email' => 'persisted@example.com',
        'name' => 'Persisted User',
    ]);

    $socialiter = new Socialiter;
    $reflection = new ReflectionClass($socialiter);

    $driverProp = $reflection->getProperty('driver');
    $driverProp->setAccessible(true);
    $driverProp->setValue($socialiter, 'google');

    $method = $reflection->getMethod('createCredentials');
    $method->setAccessible(true);
    $credential = $method->invoke($socialiter, $socialiteUser);

    $user = $credential->user;

    expect($user)->toBeInstanceOf(CustomTestUser::class)
        ->and($user->exists)->toBeTrue()
        ->and($user->id)->not->toBeNull()
        ->and($user->email)->toBe('persisted@example.com')
        ->and($user->custom_field)->toBe('persisted');
});

it('still finds existing user by email even with custom callback registered', function () {
    Socialiter::createUsersUsing(function (SocialiteUser $socialiteUser) {
        return CustomTestUser::create([
            'name' => $socialiteUser->getName(),
            'email' => $socialiteUser->getEmail(),
            'password' => 'should-not-be-called',
            'custom_field' => 'should-not-exist',
        ]);
    });

    $existingUser = CustomTestUser::create([
        'name' => 'Existing',
        'email' => 'exists@example.com',
        'password' => 'original',
    ]);

    $socialiteUser = makeCustomSocialiteUser([
        'email' => 'exists@example.com',
        'id' => 'provider-existing',
    ]);

    $socialiter = new Socialiter;
    $reflection = new ReflectionClass($socialiter);

    $driverProp = $reflection->getProperty('driver');
    $driverProp->setAccessible(true);
    $driverProp->setValue($socialiter, 'google');

    $method = $reflection->getMethod('createCredentials');
    $method->setAccessible(true);
    $credential = $method->invoke($socialiter, $socialiteUser);

    expect($credential->user_id)->toBe($existingUser->id)
        ->and(CustomTestUser::count())->toBe(1)
        ->and($existingUser->fresh()->password)->toBe('original');
});

it('resets to default behavior after calling createUsersUsingDefault', function () {
    Socialiter::createUsersUsing(function (SocialiteUser $socialiteUser) {
        return CustomTestUser::create([
            'name' => $socialiteUser->getName(),
            'email' => $socialiteUser->getEmail(),
            'password' => 'custom',
            'custom_field' => 'was-custom',
        ]);
    });

    Socialiter::createUsersUsingDefault();

    $socialiteUser = makeCustomSocialiteUser([
        'email' => 'reset@example.com',
        'name' => 'Reset User',
    ]);

    $socialiter = new Socialiter;
    $reflection = new ReflectionClass($socialiter);

    $driverProp = $reflection->getProperty('driver');
    $driverProp->setAccessible(true);
    $driverProp->setValue($socialiter, 'google');

    $method = $reflection->getMethod('createCredentials');
    $method->setAccessible(true);
    $credential = $method->invoke($socialiter, $socialiteUser);

    $user = CustomTestUser::where('email', 'reset@example.com')->first();

    expect($user)->not->toBeNull()
        ->and($user->custom_field)->toBeNull();
});

// Test model with custom field
class CustomTestUser extends Authenticatable
{
    protected $table = 'users';
    protected $guarded = [];
}
