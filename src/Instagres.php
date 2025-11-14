<?php

namespace Philip\LaravelInstagres;

use Philip\Instagres\Client;

/**
 * Laravel wrapper for the Instagres PHP SDK
 */
class Instagres
{
    protected array $config;

    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    /**
     * Create a new claimable Instagres database
     *
     * @param  string|null  $referrer  Optional referrer (uses config default if not provided)
     * @param  string|null  $dbId  Optional database ID (auto-generated if not provided)
     * @return array{connection_string: string, claim_url: string, expires_at: string}
     */
    public function create(?string $referrer = null, ?string $dbId = null): array
    {
        $referrer = $referrer ?? data_get($this->config, 'referrer', 'laravel-instagres');

        return Client::createClaimableDatabase($referrer, $dbId);
    }

    /**
     * Get the claim URL for a database
     *
     * @param  string  $dbId  The database UUID
     * @return string The claim URL
     */
    public function claimUrl(string $dbId): string
    {
        return Client::getClaimUrl($dbId);
    }

    /**
     * Parse a PostgreSQL connection string
     *
     * @param  string  $connectionString  PostgreSQL connection string
     * @return array{host: string, port: string, database: string, user: string, password: string, dsn: string, options: array<string, string>}
     */
    public function parseConnection(string $connectionString): array
    {
        return Client::parseConnectionString($connectionString);
    }

    /**
     * Generate a UUID v4
     *
     * @return string A random UUID v4 string
     */
    public function generateUuid(): string
    {
        return Client::generateUuid();
    }
}
