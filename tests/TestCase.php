<?php

declare(strict_types=1);

namespace Sammyjo20\EasyLaravelDocker\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Sammyjo20\EasyLaravelDocker\EasyLaravelDockerServiceProvider;

abstract class TestCase extends Orchestra
{
    /**
     * Get the package providers
     */
    protected function getPackageProviders($app): array
    {
        return [
            EasyLaravelDockerServiceProvider::class,
        ];
    }

    /**
     * Define environment setup.
     */
    protected function defineEnvironment($app): void
    {
        $app->useAppPath(__DIR__.'../../../tests/Fixtures');
    }
}
