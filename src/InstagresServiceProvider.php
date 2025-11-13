<?php

namespace Philip\LaravelInstagres;

use Philip\LaravelInstagres\Console\CreateDatabaseCommand;
use Philip\LaravelInstagres\Console\GetClaimUrlCommand;
use Philip\LaravelInstagres\Support\EnvManager;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class InstagresServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-instagres')
            ->hasConfigFile()
            ->hasCommands([
                CreateDatabaseCommand::class,
                GetClaimUrlCommand::class,
            ]);
    }

    public function packageRegistered(): void
    {
        // Register EnvManager as a singleton
        $this->app->singleton(EnvManager::class, function ($app) {
            return new EnvManager($app->basePath());
        });

        // Register the Instagres client wrapper as a singleton
        $this->app->singleton('instagres', function ($app) {
            return new Instagres($app['config']->get('instagres'));
        });
    }
}
