<?php

use Illuminate\Support\Facades\File;

beforeEach(function () {
    $fixturePath = __DIR__ . '/../Fixtures/Application';

    File::cleanDirectory($fixturePath);
    File::ensureDirectoryExists($fixturePath);

    $this->app->setBasePath($fixturePath);
});

describe('run the install:docker command', function () {
    test('with mysql', function () {
        $this->artisan('install:docker')
            ->expectsConfirmation('Are you sure you want to run the install:docker command?', 'yes')
            ->expectsQuestion('Please enter your application\'s name in slug-case', 'my-app')
            ->expectsQuestion('What port would you like your web server to run on?', '8080')
            ->expectsQuestion('What database engine would you like to use?', 'MySQL')
            ->assertOk();
    });

    test('with sqlite', function () {

    });

    test('without database', function () {

    });
});

describe('exiting the install:docker command early', function () {
    test('due to failed confirmation', function () {
        $this->artisan('install:docker')
            ->expectsConfirmation('Are you sure you want to run the install:docker command?', false)
            ->assertExitCode(0);
    });
});
