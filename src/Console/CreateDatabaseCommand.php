<?php

namespace Philip\LaravelInstagres\Console;

use Illuminate\Console\Command;
use Illuminate\Console\View\Components\Success;
use Philip\Instagres\Exception\InstagresException;
use Philip\LaravelInstagres\Facades\Instagres;
use Philip\LaravelInstagres\Support\EnvManager;

class CreateDatabaseCommand extends Command
{
    protected $signature = 'instagres:create
                            {--set-default : Set this database as the default Laravel database connection}
                            {--url : Use DB_URL instead of DB_* variables (when using --set-default)}
                            {--save-as= : Save connection string with a custom prefix (e.g., "STAGING" creates STAGING_CONNECTION_STRING)}
                            {--force : Skip confirmation prompt when modifying .env}
                            {--logical-replication : Enable logical replication on create (OR with INSTAGRES_LOGICAL_REPLICATION / config)}
                            {--direct-url : Use the direct (non-pooled) connection string for DB_URL and DB_* when using --set-default}';

    protected $description = 'Create a new Neon Claimable Postgres database. For CI or non-interactive runs, use --force with --set-default or --save-as so .env updates do not prompt.';

    public function handle(EnvManager $envManager): int
    {
        $this->info('Creating a new Claimable Postgres database...');
        $this->newLine();

        if ($this->option('direct-url') && ! $this->option('set-default')) {
            $this->warn('The --direct-url option only applies with --set-default. It will be ignored for this run.');
            $this->newLine();
        }

        $enableLogicalReplication = (bool) config('instagres.logical_replication', false)
            || (bool) $this->option('logical-replication');

        try {
            $database = Instagres::create(null, $enableLogicalReplication);

            $this->displayDatabaseInfo($database);

            if ($this->option('set-default') || $this->option('save-as')) {
                if (! $this->option('force')) {
                    if (! $this->confirm('This will modify your .env file (a backup will be created). Continue?', true)) {
                        $this->info('Operation cancelled.');

                        return self::SUCCESS;
                    }
                }

                $this->saveDatabaseToEnv($database, $envManager);
            } else {
                $this->newLine();
                $this->comment('💡 Tip: Use --set-default to automatically configure this as your default database');
                $this->comment('   Or use --save-as=NAME to save it as a named connection');
            }

            return self::SUCCESS;

        } catch (InstagresException $e) {
            $this->error('Failed to create database: '.$e->getMessage());
            $this->newLine();
            $this->comment('Please check your network connection and try again.');

            return self::FAILURE;
        }
    }

    /**
     * Connection string used for Laravel default DB when --set-default is set.
     */
    protected function connectionStringForSetDefault(array $database): string
    {
        if (! $this->option('direct-url')) {
            return $database['connection_string'];
        }

        $direct = $database['direct_connection_string'] ?? null;
        if (! is_string($direct) || $direct === '') {
            throw new InstagresException('API response did not include a direct connection string; cannot use --direct-url.');
        }

        return $database['direct_connection_string'];
    }

    /**
     * Display database information in a formatted way
     */
    protected function displayDatabaseInfo(array $database): void
    {
        $this->lineSuccess('Database created successfully!');
        $this->newLine();

        $this->components->twoColumnDetail('Database ID', $database['id'] ?? '—');
        $this->newLine();

        $parsed = Instagres::parseConnection($database['connection_string']);

        $this->components->twoColumnDetail('Host (pooled)', $parsed['host']);
        $this->components->twoColumnDetail('Database', $parsed['database']);
        $this->components->twoColumnDetail('User', $parsed['user']);
        $this->components->twoColumnDetail('Port', $parsed['port']);
        $this->newLine();

        $this->components->twoColumnDetail('Pooled connection string', $database['connection_string']);
        $this->newLine();

        $directForDisplay = $database['direct_connection_string'] ?? null;
        if (is_string($directForDisplay) && $directForDisplay !== '') {
            $this->components->twoColumnDetail('Direct connection string', $directForDisplay);
            $this->newLine();
        }

        $this->components->twoColumnDetail('Claim URL', $database['claim_url']);
        $this->components->twoColumnDetail('Expires At', $database['expires_at']);

        $this->newLine();

        if ($this->option('set-default')) {
            $label = $this->option('direct-url') ? 'direct (non-pooled)' : 'pooled';
            $this->comment("With --set-default, your .env will use the {$label} connection string for DB_URL / DB_*.");
        }

        $this->newLine();
        $this->warn('⏱️  This database will expire in 72 hours unless claimed!');
        $this->line('   Visit the claim URL to make it permanent.');
    }

    /**
     * Save database connection to .env file
     */
    protected function saveDatabaseToEnv(array $database, EnvManager $envManager): void
    {
        $this->newLine();
        $this->info('Updating .env file...');

        $variables = [];

        $setDefaultString = $this->option('set-default')
            ? $this->connectionStringForSetDefault($database)
            : null;

        if ($this->option('set-default')) {
            if ($this->option('url')) {
                $variables['DB_CONNECTION'] = 'pgsql';
                $variables['DB_URL'] = $setDefaultString;
                $this->line('  • Set DB_CONNECTION=pgsql');
                $this->line('  • Set DB_URL');
            } else {
                $parsed = Instagres::parseConnection($setDefaultString);

                if (empty($parsed['host']) || empty($parsed['database']) || empty($parsed['user'])) {
                    $this->error('Failed to parse connection string properly.');

                    return;
                }

                $variables['DB_CONNECTION'] = 'pgsql';
                $variables['DB_HOST'] = $parsed['host'];
                $variables['DB_PORT'] = $parsed['port'];
                $variables['DB_DATABASE'] = $parsed['database'];
                $variables['DB_USERNAME'] = $parsed['user'];
                $variables['DB_PASSWORD'] = $parsed['password'];

                $this->line('  • Set DB_CONNECTION=pgsql');
                $this->line('  • Set DB_HOST='.$parsed['host']);
                $this->line('  • Set DB_PORT='.$parsed['port']);
                $this->line('  • Set DB_DATABASE='.$parsed['database']);
                $this->line('  • Set DB_USERNAME='.$parsed['user']);
                $this->line('  • Set DB_PASSWORD (hidden)');
            }
        }

        if ($saveAs = $this->option('save-as')) {
            $prefix = strtoupper($saveAs);
            $variables["{$prefix}_CONNECTION_STRING"] = $database['connection_string'];
            $variables["{$prefix}_CLAIM_URL"] = $database['claim_url'];
            $this->line("  • Set {$prefix}_CONNECTION_STRING");
            $this->line("  • Set {$prefix}_CLAIM_URL");
        }

        if ($this->option('set-default')) {
            $claimUrlKey = config('instagres.claim_url_var', 'INSTAGRES_CLAIM_URL');
            $variables[$claimUrlKey] = $database['claim_url'];
            $this->line("  • Set {$claimUrlKey}");
        }

        try {
            if ($envManager->setMultiple($variables, true)) {
                $this->newLine();
                $this->lineSuccess('.env file updated successfully!');
                $this->comment('  (A backup was saved to .env.backup)');
            } else {
                $this->newLine();
                $this->components->error('Failed to update .env file');
                $this->comment('  Please check file permissions and try again.');
            }
        } catch (\Exception $e) {
            $this->newLine();
            $this->components->error('Failed to update .env file: '.$e->getMessage());
            $this->comment('  Please check file permissions and try again.');
        }
    }

    /**
     * Laravel 10 has no Console "success" component; use styled info there.
     */
    protected function lineSuccess(string $message): void
    {
        if (class_exists(Success::class)) {
            $this->components->success($message);
        } else {
            $this->info($message);
        }
    }
}
