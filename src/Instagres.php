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
     * Create a new claimable Neon database (Claimable Postgres API).
     *
     * @param  string|null  $ref  Optional API ref; when empty, uses config "ref" then "referrer"
     * @param  bool|null  $logicalReplication  When null, uses config logical_replication; otherwise this value is sent to the API
     * @return array{
     *     id: string,
     *     status: string,
     *     neon_project_id: string,
     *     connection_string: string,
     *     pooled_connection_string: string,
     *     direct_connection_string: string,
     *     claim_url: string,
     *     expires_at: string,
     *     created_at?: string,
     *     updated_at?: string
     * }
     *
     * Note: connection_string is the pooled URL (same as pooled_connection_string from the API).
     */
    public function create(?string $ref = null, ?bool $logicalReplication = null): array
    {
        $effectiveRef = $this->resolveRef($ref);
        $enableLogicalReplication = $logicalReplication !== null
            ? $logicalReplication
            : (bool) data_get($this->config, 'logical_replication', false);

        return Client::createClaimableDatabase($effectiveRef, $enableLogicalReplication);
    }

    /**
     * Resolve API ref: argument wins, then non-empty config ref, then referrer.
     */
    protected function resolveRef(?string $ref): string
    {
        if (is_string($ref) && $ref !== '') {
            return $ref;
        }

        $configRef = data_get($this->config, 'ref');
        if (is_string($configRef) && $configRef !== '') {
            return $configRef;
        }

        return (string) data_get($this->config, 'referrer', 'laravel-instagres');
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
