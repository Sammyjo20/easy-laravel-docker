<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->storage = Storage::fake('local');
    $fixturePath = $this->storage->path('');

    File::deleteDirectory($fixturePath);
    File::ensureDirectoryExists($fixturePath);
    File::ensureDirectoryExists($fixturePath.'/config');

    $this->app->setBasePath($fixturePath);
});

describe('run the install:docker command', function () {
    test('with mysql', function () {
        $this->artisan('install:docker')
            ->expectsConfirmation('Are you sure you want to run the install:docker command?', 'yes')
            ->expectsQuestion('Please enter your application\'s name in slug-case', 'my-app')
            ->expectsQuestion('What port would you like your web server to run on?', '9999')
            ->expectsQuestion('What database engine would you like to use?', 'MySQL')
            ->expectsQuestion('Would you like to install any PHP extensions? Type them separated with commas', 'intl sqlite')
            ->expectsPromptsInfo('✅ All done!')
            ->expectsConfirmation('Would you like to remove the sammyjo20/easy-laravel-docker package?', 'no')
            ->assertSuccessful();

        $storage = $this->storage;

        foreach (['.env', '.env.example'] as $env) {
            expect($storage->get($env))->toContain('WEB_PORT=9999');
            expect($storage->get($env))->toContain('TRUSTED_PROXIES=0.0.0.0/0');
            expect($storage->get($env))->toContain('DOCKER_DB_DATABASE="${DB_DATABASE}"');
            expect($storage->get($env))->toContain('DOCKER_DB_PASSWORD="secret"');
        }

        expect($storage->get('docker-compose.yml'))->toContain('mysql:latest');

        // Ensure we have replaced all instanced of "application-name"

        expect($storage->get('docker-compose.yml'))->not->toContain('application-name');
        expect($storage->get('deploy.sh'))->not->toContain('application-name');

        // Ensure PHP extensions are valid

        expect($storage->get('Dockerfile'))->toContain('RUN install-php-extensions intl sqlite');
    });

    test('with sqlite', function () {
        $this->artisan('install:docker')
            ->expectsConfirmation('Are you sure you want to run the install:docker command?', 'yes')
            ->expectsQuestion('Please enter your application\'s name in slug-case', 'my-app')
            ->expectsQuestion('What port would you like your web server to run on?', '9999')
            ->expectsQuestion('What database engine would you like to use?', 'SQLite')
            ->expectsQuestion('Would you like to install any PHP extensions? Type them separated with commas', 'intl sqlite')
            ->expectsPromptsInfo('✅ All done!')
            ->expectsConfirmation('Would you like to remove the sammyjo20/easy-laravel-docker package?', 'no')
            ->assertSuccessful();

        expect($this->storage->get('docker-compose.yml'))->toContain('database:/var/www/html/database/sqlite');
    });

    test('without database and removing command', function () {
        Process::fake();

        $this->artisan('install:docker')
            ->expectsConfirmation('Are you sure you want to run the install:docker command?', 'yes')
            ->expectsQuestion('Please enter your application\'s name in slug-case', 'my-app')
            ->expectsQuestion('What port would you like your web server to run on?', '9999')
            ->expectsQuestion('What database engine would you like to use?', 'None')
            ->expectsQuestion('Would you like to install any PHP extensions? Type them separated with commas', 'intl sqlite')
            ->expectsPromptsInfo('✅ All done!')
            ->expectsConfirmation('Would you like to remove the sammyjo20/easy-laravel-docker package?', 'yes')
            ->assertSuccessful();

        $dockerCompose = $this->storage->get('docker-compose.yml');

        expect($dockerCompose)->not->toContain('mysql:latest');
        expect($dockerCompose)->not->toContain('database:/var/www/html/database/sqlite');
        expect($dockerCompose)->toContain('AUTORUN_LARAVEL_MIGRATION=false');

        Process::assertRan('composer remove sammyjo20/easy-laravel-docker --dev');
    });

    afterEach(function () {
        $this->storage->assertExists('.env');
        $this->storage->assertExists('.env.example');
        $this->storage->assertExists('deploy.sh');
        $this->storage->assertExists('Dockerfile');
        $this->storage->assertExists('docker-compose.yml');
        $this->storage->assertExists('config/trustedproxy.php');
    });
});

describe('exiting the install:docker command early', function () {
    test('due to failed confirmation', function () {
        $this->artisan('install:docker')
            ->expectsConfirmation('Are you sure you want to run the install:docker command?', false)
            ->assertExitCode(0);
    });
});
