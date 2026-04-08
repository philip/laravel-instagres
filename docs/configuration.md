# Configuration

## Publish the config file

Run:

```bash
php artisan vendor:publish --tag="instagres-config"
```

Laravel copies `config/instagres.php` into your app. Open that file for the full structure and inline comments.

## What each key does

- **`ref`** reads `INSTAGRES_REF`. The API receives this value as `ref` when you set it. When it stays empty, Laravel sends **`referrer`** instead.
- **`referrer`** reads `INSTAGRES_REFERRER` and defaults to `laravel-instagres`. Use it to label where create requests originate.
- **`logical_replication`** reads `INSTAGRES_LOGICAL_REPLICATION` and defaults to false. New databases use logical replication when this is true or when you pass `--logical-replication` on the command (the flag ORs with the config value).
- **`claim_url_var`** names the environment variable that stores the default claim URL after `--set-default`. It defaults to `INSTAGRES_CLAIM_URL`. Named saves from `--save-as` always use `{PREFIX}_CLAIM_URL`.

## Environment variables

| Variable | Role |
|----------|------|
| `INSTAGRES_REF` | Optional API `ref` override. |
| `INSTAGRES_REFERRER` | Default `ref` when `INSTAGRES_REF` is empty. |
| `INSTAGRES_LOGICAL_REPLICATION` | Default logical replication flag for new databases. |
| Package-written `DB_*`, `DB_URL`, `*_CONNECTION_STRING`, `*_CLAIM_URL` | See the sections below. |

## `.env` after `--set-default` (separate `DB_*` keys)

```env
DB_CONNECTION=pgsql
DB_HOST=ep-test-123.us-east-1.aws.neon.tech
DB_PORT=5432
DB_DATABASE=neondb
DB_USERNAME=username
DB_PASSWORD=password

INSTAGRES_CLAIM_URL=https://neon.new/claim/123e4567-e89b-12d3-a456-426614174000
```

## `.env` after `--set-default --url`

```env
DB_CONNECTION=pgsql
DB_URL=postgresql://user:pass@host:5432/db?sslmode=require

INSTAGRES_CLAIM_URL=https://neon.new/claim/123e4567-e89b-12d3-a456-426614174000
```

## `.env` after `--save-as=staging`

```env
STAGING_CONNECTION_STRING=postgresql://user:pass@host/db?sslmode=require
STAGING_CLAIM_URL=https://neon.new/claim/123e4567-e89b-12d3-a456-426614174000
```

## Pooled versus direct strings

By default `DB_*` and `DB_URL` use the **pooled** connection string from the API (`connection_string`). Pass `--direct-url` with `--set-default` when you need the **direct** (non-pooled) endpoint in your default connection (for example some migration or tooling setups).

`--save-as` always stores the **pooled** connection string in `{PREFIX}_CONNECTION_STRING`.

## Database details

Typical Claimable Postgres traits appear in the [Neon Claimable Postgres docs](https://neon.com/docs/reference/claimable-postgres). Expect Neon serverless PostgreSQL, SSL required, and about a 72 hour lifespan for unclaimed databases. Confirm current defaults in Neon documentation.
