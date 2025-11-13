<?php

namespace Philip\LaravelInstagres\Console;

use Illuminate\Console\Command;
use Philip\LaravelInstagres\Facades\Instagres;
use Philip\LaravelInstagres\Support\EnvManager;

class GetClaimUrlCommand extends Command
{
    protected $signature = 'instagres:claim-url
                            {--db-id= : Database ID (will read from claim URL in .env if not provided)}';

    protected $description = 'Get the claim URL for an Instagres database';

    public function handle(EnvManager $envManager): int
    {
        $dbId = $this->option('db-id');

        // Try to read from .env if not provided
        if (! $dbId) {
            $claimUrlKey = config('instagres.claim_url_var', 'INSTAGRES_CLAIM_URL');
            $claimUrl = $envManager->get($claimUrlKey);

            if (! $claimUrl) {
                $this->error("No database ID provided and {$claimUrlKey} not found in .env");
                $this->newLine();
                $this->comment('Usage:');
                $this->line('  php artisan instagres:claim-url --db-id=<uuid>');
                $this->line("  Or set {$claimUrlKey} in your .env file");

                return self::FAILURE;
            }

            // Display the stored claim URL
            $this->comment('Using claim URL from .env');
            $this->newLine();

            $this->components->info('Claim URL');
            $this->line($claimUrl);
        } else {
            // Generate claim URL from provided DB ID
            $claimUrl = Instagres::claimUrl($dbId);

            $this->components->info('Claim URL');
            $this->line($claimUrl);
        }

        $this->newLine();

        $this->components->bulletList([
            'Visit this URL to claim your database',
            'Sign in to your Neon account (or create one)',
            'Your database will become permanent after claiming',
        ]);

        $this->newLine();
        $this->warn('⏱️  Unclaimed databases expire after 72 hours');

        return self::SUCCESS;
    }
}
