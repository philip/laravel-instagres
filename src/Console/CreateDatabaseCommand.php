<?php

namespace Philip\LaravelInstagres\Console;

use Illuminate\Console\Command;
use Philip\Instagres\Exception\InstagresException;
use Philip\LaravelInstagres\Facades\Instagres;
use Philip\LaravelInstagres\Support\EnvManager;

class CreateDatabaseCommand extends Command
{
    protected $signature = 'instagres:create
                            {--set-default : Set this database as the default Laravel database connection}
                            {--url : Use DATABASE_URL instead of DB_* variables (when using --set-default)}
                            {--save-as= : Save connection string with a custom prefix (e.g., "STAGING" creates STAGING_CONNECTION_STRING)}';

    protected $description = 'Create a new instant Neon PostgreSQL database';

    public function handle(EnvManager $envManager): int
    {
        $this->info('Creating a new Instagres database...');
        $this->newLine();

        try {
            // Create the database
            $database = Instagres::create();

            // Display the database information
            $this->displayDatabaseInfo($database);

            // Save to .env if requested
            if ($this->option('set-default') || $this->option('save-as')) {
                $this->saveDatabaseToEnv($database, $envManager);
            } else {
                $this->newLine();
                $this->comment('💡 Tip: Use --set-default to automatically configure this as your default database');
                $this->comment('   Or use --save-as=NAME to save it as a named connection');
            }

            return self::SUCCESS;

        } catch (InstagresException $e) {
            $this->error('Failed to create database: '.$e->getMessage());

            return self::FAILURE;
        }
    }

    /**
     * Display database information in a formatted way
     */
    protected function displayDatabaseInfo(array $database): void
    {
        $this->components->success('Database created successfully!');
        $this->newLine();

        // Parse connection string for easier reading
        $parsed = Instagres::parseConnection($database['connection_string']);

        $this->components->twoColumnDetail('Host', $parsed['host']);
        $this->components->twoColumnDetail('Database', $parsed['database']);
        $this->components->twoColumnDetail('User', $parsed['user']);
        $this->components->twoColumnDetail('Port', $parsed['port']);
        $this->newLine();

        $this->components->twoColumnDetail('Connection String', $database['connection_string']);
        $this->newLine();

        $this->components->twoColumnDetail('Claim URL', $database['claim_url']);
        $this->components->twoColumnDetail('Expires At', $database['expires_at']);

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

        // Determine which variables to set based on options
        if ($this->option('set-default')) {
            if ($this->option('url')) {
                // Use DATABASE_URL (common in production/Heroku/Forge)
                $variables['DATABASE_URL'] = $database['connection_string'];
                $this->line('  • Set DATABASE_URL');
            } else {
                // Parse connection string to set individual DB_* variables (Laravel default)
                $parsed = Instagres::parseConnection($database['connection_string']);
                
                $variables['DB_CONNECTION'] = 'pgsql';
                $variables['DB_HOST'] = $parsed['host'];
                $variables['DB_PORT'] = $parsed['port'];
                $variables['DB_DATABASE'] = $parsed['database'];
                $variables['DB_USERNAME'] = $parsed['user'];
                $variables['DB_PASSWORD'] = $parsed['password'];
                
                $this->line('  • Set DB_CONNECTION=pgsql');
                $this->line('  • Set DB_HOST=' . $parsed['host']);
                $this->line('  • Set DB_PORT=' . $parsed['port']);
                $this->line('  • Set DB_DATABASE=' . $parsed['database']);
                $this->line('  • Set DB_USERNAME=' . $parsed['user']);
                $this->line('  • Set DB_PASSWORD (hidden)');
            }
        }

        if ($saveAs = $this->option('save-as')) {
            $prefix = strtoupper($saveAs);
            $variables["{$prefix}_CONNECTION_STRING"] = $database['connection_string'];
            $this->line("  • Set {$prefix}_CONNECTION_STRING");
        }

        // Always save the claim URL for reference
        $claimUrlKey = config('instagres.claim_url_var', 'INSTAGRES_CLAIM_URL');

        $variables[$claimUrlKey] = $database['claim_url'];
        $this->line("  • Set {$claimUrlKey}");

        // Create backup and save
        if ($envManager->setMultiple($variables, true)) {
            $this->newLine();
            $this->components->success('.env file updated successfully!');
            $this->comment('  (A backup was saved to .env.backup)');
        } else {
            $this->newLine();
            $this->components->error('Failed to update .env file');
        }
    }
}
