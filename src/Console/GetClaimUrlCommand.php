<?php

namespace Philip\LaravelInstagres\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Philip\LaravelInstagres\Support\EnvManager;

class GetClaimUrlCommand extends Command
{
    protected $signature = 'instagres:claim-url
                            {--name= : Show claim URL for a specific named connection (e.g., "staging" for STAGING_CLAIM_URL)}';

    protected $description = 'Display claim URLs for your Instagres databases';

    public function handle(EnvManager $envManager): int
    {
        // If --name is provided, show specific connection
        if ($name = $this->option('name')) {
            return $this->showSpecificClaimUrl($name, $envManager);
        }

        // Otherwise, show all claim URLs
        return $this->showAllClaimUrls($envManager);
    }

    /**
     * Show claim URL for a specific named connection
     */
    protected function showSpecificClaimUrl(string $name, EnvManager $envManager): int
    {
        // Handle 'default' as a special case for the default claim URL
        if (strtolower($name) === 'default') {
            $claimUrlKey = config('instagres.claim_url_var', 'INSTAGRES_CLAIM_URL');
            $displayName = 'Default';
        } else {
            $prefix = strtoupper($name);
            $claimUrlKey = "{$prefix}_CLAIM_URL";
            $displayName = ucfirst($name);
        }

        $claimUrl = $envManager->get($claimUrlKey);

        if (! $claimUrl) {
            $this->error("{$claimUrlKey} not found in .env");
            $this->newLine();

            if (strtolower($name) === 'default') {
                $this->comment('Create a default connection first:');
                $this->line('  php artisan instagres:create --set-default');
            } else {
                $this->comment('Create a named connection first:');
                $this->line("  php artisan instagres:create --save-as={$name}");
            }

            $this->newLine();
            $this->comment('Or view all claim URLs:');
            $this->line('  php artisan instagres:claim-url');

            return self::FAILURE;
        }

        $this->components->info("Claim URL for {$displayName}");
        $this->line($claimUrl);

        $this->displayClaimInstructions();

        return self::SUCCESS;
    }

    /**
     * Show all claim URLs found in .env
     */
    protected function showAllClaimUrls(EnvManager $envManager): int
    {
        $claimUrls = $this->findAllClaimUrls($envManager);

        if (empty($claimUrls)) {
            $this->error('No claim URLs found in .env');
            $this->newLine();
            $this->comment('Create a database first:');
            $this->line('  php artisan instagres:create --set-default');
            $this->line('  php artisan instagres:create --save-as=staging');

            return self::FAILURE;
        }

        $this->components->info('Instagres Claim URLs');
        $this->newLine();

        // Display in a nice table format
        $rows = [];
        foreach ($claimUrls as $key => $url) {
            $label = $this->formatConnectionLabel($key);
            $rows[] = [$label, $url];
        }

        $this->table(['Connection', 'Claim URL'], $rows);

        $this->displayClaimInstructions();

        return self::SUCCESS;
    }

    /**
     * Find all claim URLs in .env file
     */
    protected function findAllClaimUrls(EnvManager $envManager): array
    {
        $envPath = base_path('.env');

        if (! File::exists($envPath)) {
            return [];
        }

        $envContent = File::get($envPath);
        $claimUrls = [];

        // Match all lines with *_CLAIM_URL pattern
        preg_match_all('/^([A-Z_]+_CLAIM_URL)=(.+)$/m', $envContent, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $key = $match[1];
            $value = trim($match[2], '"\'');
            $claimUrls[$key] = $value;
        }

        return $claimUrls;
    }

    /**
     * Format connection label for display
     */
    protected function formatConnectionLabel(string $key): string
    {
        // Convert INSTAGRES_CLAIM_URL -> Default
        // Convert STAGING_CLAIM_URL -> Staging
        // Convert PRODUCTION_CLAIM_URL -> Production

        $defaultKey = config('instagres.claim_url_var', 'INSTAGRES_CLAIM_URL');

        if ($key === $defaultKey) {
            return 'Default';
        }

        // Remove _CLAIM_URL suffix and convert to title case
        $name = str_replace('_CLAIM_URL', '', $key);
        $name = str_replace('_', ' ', $name);

        return ucwords(strtolower($name));
    }

    /**
     * Display claim instructions
     */
    protected function displayClaimInstructions(): void
    {
        $this->newLine();

        $this->components->bulletList([
            'Visit the claim URL to claim your database',
            'Sign in to your Neon account (or create one)',
            'Your database will become permanent after claiming',
        ]);

        $this->newLine();
        $this->warn('⏱️  Unclaimed databases expire after 72 hours');
    }
}
