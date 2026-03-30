<?php

namespace GeneaLabs\LaravelSocialiter\Tests;

use GeneaLabs\LaravelSocialiter\Providers\ServiceProvider;
use Orchestra\Testbench\TestCase;

class ServiceProviderTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [ServiceProvider::class];
    }

    public function test_service_provider_boots_without_error(): void
    {
        $this->assertTrue(
            $this->app->providerIsLoaded(ServiceProvider::class)
        );
    }

    public function test_socialiter_facade_is_bound(): void
    {
        $this->assertTrue($this->app->bound('socialiter'));
    }
}
