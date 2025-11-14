<?php

namespace Philip\LaravelInstagres\Console;

use Illuminate\Console\Command;
use Philip\LaravelInstagres\Support\EnvManager;

class GetClaimUrlCommand extends Command
{
    protected $signature = 'instagres:claim-url';

    protected $description = 'Display the claim URL for your Instagres database';

    public function handle(EnvManager $envManager): int
    {
        $claimUrlKey = config('instagres.claim_url_var', 'INSTAGRES_CLAIM_URL');
        $claimUrl = $envManager->get($claimUrlKey);

        if (! $claimUrl) {
            $this->error("{$claimUrlKey} not found in .env");
            $this->newLine();
            $this->comment('Create a database first:');
            $this->line('  php artisan instagres:create --set-default');
            $this->newLine();
            $this->comment("Or manually set {$claimUrlKey} in your .env file");

            return self::FAILURE;
        }

        $this->components->info('Claim URL');
        $this->line($claimUrl);

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
