<?php

namespace Sammyjo20\EasyLaravelDocker\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use function app;
use function base_path;
use function basename;
use function config;
use function dd;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\text;
use function Laravel\Prompts\select;
use function ray;
use const PHP_EOL;

class InstallDockerCommand extends Command
{
    // Todo: Check Laravel Version, Must be >=12
    // application nane
    // php extensions required
    // port
    // mysql, sqlite or no database

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'install:docker';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install and configure a lightweight Docker framework for Laravel';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        if (!confirm('Are you sure you want to run the install:docker command?')) {
            return;
        }

        $applicationName = text('Please enter your application\'s name in slug-case', default: Str::slug(config('app.name')), required: true);

        $applicationPort = text('What port would you like your web server to run on?', default: 8080, required: true);

        $databaseEngine = select('What database engine would you like to use?', options: ['MySQL', 'SQLite', 'None'], required: true);

        // dd($databaseEngine);

        // Todo: PHP extensions
        // Todo: Database preference

        $stubPath = __DIR__ . '/../../../stubs';

        // Start with copying over the common files used in the root of the project

        $commonFiles = [
            $stubPath . '/.dockerignore',
            $stubPath . '/deploy.sh',
            $stubPath . '/Dockerfile',
            $stubPath . '/trustedproxy.php',
        ];

        foreach ($commonFiles as $commonFile) {
            $destination = base_path(basename($commonFile));

            if (File::exists($destination) && !confirm(sprintf('The file at "%s" already exists in the directory. Replace?', $destination))) {
                continue;
            }

            $content = Str::replace('application-name', $applicationName, File::get($commonFile));

            File::put($destination, $content);
        }

        // Next, continue with appending the environment variables to the .env and .env.example files

        $environmentVariables = Str::replace(
            search: 'WEB_PORT=',
            replace: 'WEB_PORT=' . $applicationPort,
            subject: File::get($stubPath . '/.env.template'),
        );

        $environmentVariables = PHP_EOL . $environmentVariables;

        File::append(base_path('.env'), $environmentVariables);
        File::append(base_path('.env.example'), $environmentVariables);

        // Todo: Remove itself once installed
    }
}
