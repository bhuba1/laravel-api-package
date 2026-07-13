<?php

declare(strict_types=1);

namespace Bhuba\AuthProfilePackage\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;

final class InstallCommand extends Command
{
    protected $signature = 'auth-profile:install
                    {--force : Overwrite published files}
                    {--run-migrations : Run pending migrations without prompting}
                    {--without-migration-prompt : Do not prompt to run pending migrations}';

    protected $description = 'Publish the Auth Profile Package configuration and migrations';

    public function handle(): int
    {
        $this->publishConfiguration();
        $this->publishMigrations();

        if ($this->option('run-migrations')) {
            $this->call('migrate', ['--force' => true]);
        } elseif (! $this->option('without-migration-prompt') && $this->input->isInteractive()) {
            if ($this->confirm('Would you like to run all pending database migrations?', true)) {
                $this->call('migrate');
            }
        }

        $this->components->info('Auth Profile Package installed successfully.');
        $this->components->warn('Configure user_model and profile_fields in config/auth-profile-package.php.');

        return self::SUCCESS;
    }

    private function publishConfiguration(): void
    {
        $configPath = config_path('auth-profile-package.php');

        if ($this->option('force') || ! is_file($configPath)) {
            $this->callSilent('vendor:publish', [
                '--tag' => 'auth-profile-package-config',
                '--force' => $this->option('force'),
            ]);

            $this->components->info('Configuration published.');
        }
    }

    private function publishMigrations(): void
    {
        if ($this->option('force') || ! $this->migrationAlreadyPublished()) {
            $this->callSilent('vendor:publish', [
                '--tag' => 'auth-profile-package-migrations',
                '--force' => $this->option('force'),
            ]);

            $this->components->info('Migrations published.');
        }
    }

    private function migrationAlreadyPublished(): bool
    {
        $migrationsPath = database_path('migrations');

        if (! is_dir($migrationsPath)) {
            return false;
        }

        return (new Collection(scandir($migrationsPath)))->contains(function (string $migration): bool {
            return (bool) preg_match('/\d{4}_\d{2}_\d{2}_\d{6}_create_auth_profile_tokens_table\.php$/', $migration);
        });
    }
}
