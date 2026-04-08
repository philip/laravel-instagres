# Changelog

All notable changes to `laravel-instagres` will be documented in this file.

## 0.3.0 - 2026-04-08

### Breaking

- **`Instagres::create()`** now matches **`philip/instagres` ^0.2**: signature is **`create(?string $ref = null, ?bool $logicalReplication = null)`**. The old second argument **`$dbId`** is removed (the API assigns database ids).
- Claim URLs use **`https://neon.new/claim/{id}`** (Claimable Postgres). Values already stored in `.env` remain valid URLs.

### Added

- Dependency **`philip/instagres`: `^0.2`** (Claimable Postgres API).
- Config **`ref`** / env **`INSTAGRES_REF`** (optional); effective API ref is non-empty **`ref`**, then **`referrer`** / **`INSTAGRES_REFERRER`**.
- Config **`logical_replication`** / env **`INSTAGRES_LOGICAL_REPLICATION`**; Artisan **`--logical-replication`** ORs with config for a single run.
- **`instagres:create --direct-url`**: with **`--set-default`**, write **`direct_connection_string`** to **`DB_URL`** or parsed **`DB_*`** (default remains pooled **`connection_string`**). Without **`--set-default`**, the flag is ignored with a warning (named **`--save-as`** connections still store the pooled URL).
- Tests for **`--direct-url`** and missing direct string handling.
- **Laravel 13** support: **`illuminate/contracts` ^13.0** (new apps default to Laravel 13).
- **CI** runs tests on **Laravel 10** (Orchestra Testbench 8) as well as 11–13.

### Changed

- **`instagres:create`** avoids the Laravel 11+ console **`Success`** component on **Laravel 10** (falls back to **`info`**), so output works on all supported versions.
- **`instagres:create`** output includes database **`id`**, pooled and direct connection strings, and notes which URL **`--set-default`** would apply.
- Command description documents using **`--force`** in CI when updating `.env`.
- **`instagres:claim-url`** copy and **`composer.json`** metadata align with **Neon Claimable Postgres** naming.
- Root **`minimum-stability`** is now **`stable`** (with **`prefer-stable`** unchanged).

## 0.2.2 - 2025-11-14

### Changed
- Removed unused `auto_configure` config option
- Clarified `claim_url_var` documentation to specify it only affects the default connection

## 0.2.1 - 2025-11-14

### Fixed
- **BREAKING**: `--url` flag now correctly sets `DB_CONNECTION=pgsql` and uses `DB_URL` instead of `DATABASE_URL` to work with Laravel's default config
- Named connections (`--save-as`) now store their own claim URLs (e.g., `STAGING_CLAIM_URL`) instead of overwriting the default `INSTAGRES_CLAIM_URL`

### Changed
- When using both `--set-default` and `--save-as`, both the default claim URL and prefixed claim URL are now saved
- `instagres:claim-url` now displays all claim URLs in a table format by default (instead of only showing the default one)

### Added
- `--name` option to `instagres:claim-url` command to show a specific connection's claim URL (use `--name=default` for the default connection, or any custom name for named connections)

## 0.2.0 - 2025-11-14

### Changed
- Simplified `instagres:claim-url` command - removed `--db-id` option
- Refactored `EnvManager` to use Laravel's `File` facade and `Str` helpers for better consistency
- Improved config access in `Instagres` class using `data_get()` helper
- Enhanced error handling and validation across all commands

### Added
- `--force` flag to `instagres:create` command to skip confirmation prompts (useful for automation)
- Confirmation prompt before modifying .env file (can be skipped with `--force`)
- Improved test coverage

## 0.1.1 - 2025-11-13

### Added
- Support for `DB_*` environment variables (in addition to `DATABASE_URL`)
- `--url` flag to use `DATABASE_URL` format when using `--set-default`

### Changed
- Simplified CI matrix to test only PHP 8.3 with Laravel 11-12 on Ubuntu
- Removed Larastan dependency for better Laravel 12 compatibility

### Fixed
- `EnvManager` now correctly handles commented-out environment variables

## 0.1.0 - 2025-11-13

### Added
- Initial release
- Artisan commands (`instagres:create`, `instagres:claim-url`)
- Instagres facade for programmatic access
- Automatic .env file management with backup
- Service provider with Laravel package discovery
- Configuration file for customization
- EnvManager helper for safe environment variable updates
- Support for default and named database connections
- Comprehensive test suite with Pest
- Full integration with `philip/instagres` SDK
- Laravel 10.x, 11.x, and 12.x support
- PHP 8.2, 8.3, and 8.4 support
