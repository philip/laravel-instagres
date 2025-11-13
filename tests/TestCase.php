<?php

namespace Philip\LaravelInstagres\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Philip\LaravelInstagres\InstagresServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app): array
    {
        return [
            InstagresServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'testing');
        config()->set('instagres.referrer', 'laravel-instagres-test');
    }
}
