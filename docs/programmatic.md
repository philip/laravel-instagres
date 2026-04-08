# Programmatic API

Use the `Instagres` facade when you need to create databases or parse connection strings from PHP.

```php
use Philip\LaravelInstagres\Facades\Instagres;

// Create a database (ref and logical replication come from config unless you override)
$database = Instagres::create();
// $database['id'], $database['connection_string'] (pooled), $database['direct_connection_string'],
// $database['claim_url'], $database['expires_at'], ...

// Optional overrides
$database = Instagres::create(ref: 'my-app', logicalReplication: true);

$claimUrl = Instagres::claimUrl($database['id']);

$parsed = Instagres::parseConnection($database['connection_string']);

$uuid = Instagres::generateUuid();
```

See [upgrading.md](upgrading.md) if you upgrade across the 0.3 API change.

Need **`Client::getDatabase($id)`** or other SDK-only APIs? Use **`Philip\Instagres\Client`** from **instagres-php** directly.

## Error handling

All Instagres errors extend `Philip\Instagres\Exception\InstagresException`.

```php
use Philip\Instagres\Exception\InstagresException;
use Philip\LaravelInstagres\Facades\Instagres;

try {
    $database = Instagres::create();
} catch (InstagresException $e) {
    Log::error('Failed to create database: '.$e->getMessage());
}
```
