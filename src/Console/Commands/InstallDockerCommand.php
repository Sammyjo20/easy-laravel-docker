<?php

declare(strict_types=1);

namespace Sammyjo20\EasyLaravelDocker\Console\Commands;

use const PHP_EOL;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;
use function base_path;
use function basename;
use function config;
use function filled;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\info;
use function Laravel\Prompts\select;
use function Laravel\Prompts\spin;
use function Laravel\Prompts\text;

class InstallDockerCommand extends Command
{
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
    public function handle(): int
    {
        info('🐳 Howdy! ~ sammyjo20/easy-laravel-docker ~');

        if (! confirm('Are you sure you want to run the install:docker command?')) {
            return self::SUCCESS;
        }

        $applicationName = text('Please enter your application\'s name in slug-case', default: Str::slug(config('app.name')), required: true);

        $applicationPort = text('What port would you like your web server to run on?', default: '8080', required: true);

        $databaseEngine = select('What database engine would you like to use?', options: ['MySQL', 'SQLite', 'None'], required: true);

        $phpExtensions = text('Would you like to install any PHP extensions? Type them separated with commas', default: 'intl');

        $stubPath = __DIR__ . '/../../../stubs/';

        // Start with copying over the common files used in the root of the project

        $commonFiles = [
            $stubPath . '.dockerignore',
            $stubPath . 'deploy.sh',
            $stubPath . 'Dockerfile',
            $stubPath . 'config/trustedproxy.php',
        ];

        foreach ($commonFiles as $commonFile) {
            $destination = base_path(Str::remove($stubPath, $commonFile));

            if (File::exists($destination) && ! confirm(sprintf('The file at "%s" already exists in the directory. Replace?', $destination))) {
                continue;
            }

            $content = Str::replace('application-name', $applicationName, File::get($commonFile));

            if (basename($commonFile) === 'Dockerfile') {
                $phpExtensions = Str::of($phpExtensions)->replace(', ', ' ')->lower()->trim();

                if (filled($phpExtensions)) {
                    $content = Str::replace(
                        search: '# RUN install-php-extensions intl',
                        replace: 'RUN install-php-extensions ' . $phpExtensions,
                        subject: $content
                    );
                }
            }

            File::put($destination, $content);
        }

        // Next, continue with appending the environment variables to the .env and .env.example files

        $environmentVariables = Str::replace(
            search: 'WEB_PORT=',
            replace: 'WEB_PORT=' . $applicationPort,
            subject: File::get($stubPath . '.env.template'),
        );

        $environmentVariables = PHP_EOL . $environmentVariables;

        File::append(base_path('.env'), $environmentVariables);
        File::append(base_path('.env.example'), $environmentVariables);

        // Next Find the docker-compose file to copy

        $dockerComposeFile = match ($databaseEngine) {
            'MySQL' => $stubPath . 'docker-compose.mysql.yml',
            'SQLite' => $stubPath . 'docker-compose.sqlite.yml',
            'None' => $stubPath . 'docker-compose.no-database.yml',
        };

        $content = Str::replace('application-name', $applicationName, File::get($dockerComposeFile));

        File::put(base_path('docker-compose.yml'), $content);

        // When the database engine is MySQL add the env templates for MySQL

        if ($databaseEngine === 'MySQL') {
            $databaseEnvironmentVariables = Str::replace('application-name', $applicationName, File::get($stubPath . '.env.mysql.template'));

            File::append(base_path('.env'), $databaseEnvironmentVariables);
            File::append(base_path('.env.example'), $databaseEnvironmentVariables);
        }

        // Wrap up

        info('✅ All done!');

        info(sprintf('1. Run "docker build -t %s ." to build the image.', $applicationName));
        info('2. Run "docker compose up" to spin up the container.');

        if (! confirm('Would you like to remove the sammyjo20/easy-laravel-docker package?')) {
            return self::SUCCESS;
        }

        spin(function () {
            $command = 'composer remove sammyjo20/easy-laravel-docker';

            if (Process::run($command . ' --dev')->failed()) {
                Process::run($command)->throw();
            }
        });

        info('ℹ️ sammyjo20/easy-laravel-docker package removed. Thanks for using it!');

        return self::SUCCESS;
    }
}
