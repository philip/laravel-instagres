# Artisan commands

## `instagres:create`

Run:

```bash
php artisan instagres:create [options]
```

| Option | Description |
|--------|-------------|
| `--set-default` | Set this database as the default Laravel connection using `DB_*` or `DB_URL`. |
| `--url` | With `--set-default`, write a single `DB_URL` instead of separate `DB_*` keys. |
| `--direct-url` | With `--set-default`, write the direct (non-pooled) connection string to `DB_URL` or `DB_*`. The flag does nothing without `--set-default` and triggers a warning. Named `--save-as` values still store the pooled URL. |
| `--logical-replication` | Create the database with logical replication enabled. This flag ORs with config and `INSTAGRES_LOGICAL_REPLICATION`. |
| `--save-as=NAME` | Save the pooled connection string and claim URL under a prefix (for example `STAGING_`). |
| `--force` | Skip the confirmation step before changing `.env`. Use this in CI when you pass `--set-default` or `--save-as`. |

Without `--set-default` or `--save-as`, the command prints connection details only. It does not write to `.env`.

### Examples

```bash
php artisan instagres:create
php artisan instagres:create --set-default
php artisan instagres:create --set-default --force
php artisan instagres:create --set-default --url
php artisan instagres:create --set-default --direct-url
php artisan instagres:create --set-default --url --direct-url --force
php artisan instagres:create --save-as=testing
php artisan instagres:create --set-default --save-as=backup --force
php artisan instagres:create --set-default --logical-replication --force
```

### Named connection

```bash
php artisan instagres:create --save-as=staging
```

This creates `STAGING_CONNECTION_STRING` and `STAGING_CLAIM_URL` using the pooled URL.

## `instagres:claim-url`

Run:

```bash
php artisan instagres:claim-url [options]
```

| Option | Description |
|--------|-------------|
| `--name=NAME` | Show the claim URL for a connection. `default` maps to `INSTAGRES_CLAIM_URL`. Other names map to `{NAME}_CLAIM_URL` in uppercase (for example `staging` maps to `STAGING_CLAIM_URL`). |

### Example output

```
Connection    Claim URL
Default       https://neon.new/claim/123e4567-e89b-12d3-a456-426614174000
Staging       https://neon.new/claim/987fcdeb-51a2-4b3c-9d0e-123456789abc
```

## Common workflows

### Local development

```bash
php artisan instagres:create --set-default
php artisan migrate
php artisan db:seed
```

### CI and CD

```bash
php artisan instagres:create --set-default --direct-url --force
php artisan test
```

Drop `--direct-url` when pooled connections work for your jobs (for example tests without heavy migrations).

### Claiming a database

```bash
php artisan instagres:claim-url
```

Open the printed URL, sign in to Neon, and claim the project if you want to keep the database past the trial window.

See [configuration.md](configuration.md) for how pooled and direct strings land in `.env`.
