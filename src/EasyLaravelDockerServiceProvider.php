<?php

namespace Sammyjo20\EasyLaravelDocker;

use Illuminate\Support\ServiceProvider;
use Sammyjo20\EasyLaravelDocker\Console\Commands\InstallDockerCommand;

class EasyLaravelDockerServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any package services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([InstallDockerCommand::class]);
        }
    }
}
