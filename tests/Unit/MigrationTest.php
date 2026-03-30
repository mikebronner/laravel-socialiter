<?php

use Illuminate\Support\Facades\Schema;

it('creates the social_credentials table on fresh migration', function () {
    expect(Schema::hasTable('social_credentials'))->toBeTrue();
});

it('has all expected columns on the social_credentials table', function () {
    $columns = Schema::getColumnListing('social_credentials');

    expect($columns)->toContain('id')
        ->toContain('user_id')
        ->toContain('provider_name')
        ->toContain('provider_id')
        ->toContain('access_token')
        ->toContain('refresh_token')
        ->toContain('expires_at')
        ->toContain('avatar')
        ->toContain('email')
        ->toContain('name')
        ->toContain('nickname')
        ->toContain('created_at')
        ->toContain('updated_at');
});

it('can rollback and re-run migrations without errors', function () {
    $this->artisan('migrate:rollback')->assertSuccessful();
    $this->artisan('migrate')->assertSuccessful();

    expect(Schema::hasTable('social_credentials'))->toBeTrue();
});

it('publishes migrations with the socialiter-migrations tag', function () {
    $this->artisan('vendor:publish', [
        '--provider' => 'GeneaLabs\\LaravelSocialiter\\Providers\\ServiceProvider',
        '--tag' => 'socialiter-migrations',
    ])->assertSuccessful();
});
