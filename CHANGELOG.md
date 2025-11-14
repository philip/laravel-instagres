# Changelog

All notable changes to `laravel-instagres` will be documented in this file.

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
