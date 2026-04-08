# Instagres for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/philip/laravel-instagres.svg?style=flat-square)](https://packagist.org/packages/philip/laravel-instagres)
[![Total Downloads](https://img.shields.io/packagist/dt/philip/laravel-instagres.svg?style=flat-square)](https://packagist.org/packages/philip/laravel-instagres)

This package connects Laravel to [Neon Claimable Postgres](https://neon.com/docs/reference/claimable-postgres). You can create a database and keep using it with the returned connection URLs without a Neon account or API auth. Sign in to Neon only when you claim a database into your account. The package builds on [`philip/instagres`](https://github.com/philip/instagres-php).

## Features

- Instant provisioning. **No Neon account or auth** to call the create API from Artisan or the facade
- Connect with the returned URLs. **No Neon login** for queries (only for **claim**)
- Immediate connection string availability. Artisan can write **`DB_*`**, **`DB_URL`**, or named keys to **`.env`**
- **72-hour** lifespan unless you **claim** the database for permanent use in Neon (`claim_url` in the response)
- Create a database with **`php artisan instagres:create`** or **`Instagres::create()`**
- Pooled and direct connection strings in the API response (`--direct-url` puts the direct string on the default connection when you use **`--set-default`**)
- **instagres-php** handles HTTP and helpers: create, **`Client::getDatabase($id)`** (same response shape as create), connection parsing, claim URLs, and UUID generation
- Optional logical replication on create (command flag or config)
- Laravel 10.x through 13.x, PHP `^8.2`

## Installation

```bash
composer require philip/laravel-instagres
```

Laravel loads the package through [package discovery](https://laravel.com/docs/packages#package-discovery).

Publish the config when you want to tune API ref, referrer, logical replication, or the default claim URL variable name:

```bash
php artisan vendor:publish --tag="instagres-config"
```

## Quick start

**Set the default database** (writes pooled `DB_*` keys to `.env`):

```bash
php artisan instagres:create --set-default
```

Pooled connections fit typical web and API traffic.

Add **`--url`** when you want one **`DB_URL`** line instead of separate **`DB_*`** keys.

**Run in CI or scripts** without prompts:

```bash
php artisan instagres:create --set-default --force
```

**Use a direct (non-pooled) endpoint** on the default connection when migrations or tooling require it:

```bash
php artisan instagres:create --set-default --direct-url --force
```

**Save a second database** without changing the default connection:

```bash
php artisan instagres:create --save-as=staging
```

**Print claim URLs** so you can keep a database past the trial window:

```bash
php artisan instagres:claim-url
```

If you skip **`--set-default`** and **`--save-as`**, the create command only prints details. It does not change `.env`.

## Verify the connection

```bash
php artisan db:show
```

## More detail

See the [documentation index](docs/README.md) for full command tables, configuration, the facade, upgrades, and workflows.

## Development

Clone the repository and run `composer test` if you contribute changes.

## Changelog

See [CHANGELOG.md](CHANGELOG.md).

## Contributing

Contributions are welcome. Open a pull request.

## Security

Email philip@roshambo.org for security issues.

## Links

- **SDK:** [philip/instagres](https://github.com/philip/instagres-php)
- **Neon:** [Claimable Postgres](https://neon.com/docs/reference/claimable-postgres)
- **Website:** [neon.com](https://neon.com)

## License

The MIT License (MIT). See [LICENSE.md](LICENSE.md).
