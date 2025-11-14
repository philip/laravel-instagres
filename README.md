# Instagres for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/philip/laravel-instagres.svg?style=flat-square)](https://packagist.org/packages/philip/laravel-instagres)
[![Total Downloads](https://img.shields.io/packagist/dt/philip/laravel-instagres.svg?style=flat-square)](https://packagist.org/packages/philip/laravel-instagres)

Laravel integration for [Neon Instagres](https://neon.com/docs/reference/neon-launchpad) - create instant, claimable PostgreSQL databases with zero configuration. Perfect for development, testing, CI/CD, and quick prototyping.

## Features

- 🚀 **Instant databases** - PostgreSQL databases in seconds, no account needed
- 🎨 **Laravel-native** - Artisan commands, facades, and automatic .env management
- ⏱️ **72-hour lifespan** - Claimable for permanent use
- 🔧 **Zero configuration** - Works immediately after installation
- 🧪 **Perfect for testing** - Ideal for CI/CD pipelines and temporary environments
- 📦 **Laravel 10-12** - Full support for Laravel 10.x, 11.x, and 12.x

## Installation

Install the package via Composer:

```bash
composer require philip/laravel-instagres
```

The package will automatically register itself via Laravel's package discovery.

### Publish Configuration (Optional)

If you want to customize the package configuration:

```bash
php artisan vendor:publish --tag="instagres-config"
```

This will create a `config/instagres.php` file where you can customize settings like the referrer name and environment variable names.

## Quick Start

### Using Artisan Commands

Create a new database and set it as your default connection:

```bash
php artisan instagres:create --set-default
```

This will:
- Create a new Instagres database
- Update database connection variables in your `.env` file
- Display connection details and claim URL
- Save claim URL for future reference

### Alternative: Named Connection

Save the database with a custom name for multiple databases:

```bash
php artisan instagres:create --save-as=staging
```

This creates `STAGING_CONNECTION_STRING` in your `.env` file.

### Get Claim URL

Display your stored claim URL (or generate one from a database ID):

```bash
php artisan instagres:claim-url
```

Or generate a claim URL from a specific database ID:

```bash
php artisan instagres:claim-url --db-id=your-uuid-here
```

## Usage

### Artisan Commands

#### `instagres:create`

Create a new instant PostgreSQL database.

```bash
php artisan instagres:create [options]
```

**Options:**
- `--set-default` - Set this database as the default Laravel database connection
- `--url` - Use `DATABASE_URL` instead of individual `DB_*` variables (only with `--set-default`)
- `--save-as=NAME` - Save connection with a custom prefix (e.g., `STAGING` creates `STAGING_CONNECTION_STRING`)

> **Note:** By default, `--set-default` uses Laravel's standard `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, etc. variables. Add `--url` to use `DATABASE_URL` instead (common in production/Heroku/Forge environments).

**Examples:**

```bash
# Create database and display info (manual configuration)
php artisan instagres:create

# Create and set as default connection (using DB_* variables)
php artisan instagres:create --set-default

# Create and set as default using DATABASE_URL
php artisan instagres:create --set-default --url

# Create and save as named connection
php artisan instagres:create --save-as=testing

# Create and save both ways
php artisan instagres:create --set-default --save-as=backup
```

#### `instagres:claim-url`

Display your stored claim URL or generate one from a database ID.

```bash
php artisan instagres:claim-url [options]
```

**Options:**
- `--db-id=UUID` - Generate claim URL from a specific database ID (reads from `INSTAGRES_CLAIM_URL` in `.env` if not provided)

**Examples:**

```bash
# Display claim URL from .env
php artisan instagres:claim-url

# Generate claim URL from a database ID
php artisan instagres:claim-url --db-id=123e4567-e89b-12d3-a456-426614174000
```

### Facade Usage

You can also use the `Instagres` facade programmatically:

```php
use Philip\LaravelInstagres\Facades\Instagres;

// Create a database
$database = Instagres::create();

// Access database information
echo $database['connection_string'];
echo $database['claim_url'];
echo $database['expires_at'];

// Get claim URL for a database ID
$claimUrl = Instagres::claimUrl($dbId);

// Parse a connection string
$parsed = Instagres::parseConnection($database['connection_string']);
echo $parsed['host'];
echo $parsed['database'];
echo $parsed['user'];
echo $parsed['password'];
echo $parsed['dsn']; // Ready for PDO

// Generate a UUID
$uuid = Instagres::generateUuid();
```

### Using with Laravel Database

Once you've created a database and saved it with `--set-default`, you can use it like any Laravel database:

```php
use Illuminate\Support\Facades\DB;

// Run migrations
php artisan migrate

// Query the database
$users = DB::table('users')->get();

// Use Eloquent
User::create(['name' => 'John Doe']);
```

## Configuration

The `config/instagres.php` file contains the following options:

```php
return [
    // Referrer identifier (helps track where databases are created from)
    'referrer' => env('INSTAGRES_REFERRER', 'laravel-instagres'),

    // Auto-configure Laravel database connection (future feature)
    'auto_configure' => env('INSTAGRES_AUTO_CONFIGURE', false),

    // Customize the environment variable name used to store the claim URL
    'claim_url_var' => 'INSTAGRES_CLAIM_URL',
];
```

## Environment Variables

After creating a database with the Artisan command, these variables are automatically added to your `.env`:

**Using `--set-default` (without `--url`):**
```env
DB_CONNECTION=pgsql
DB_HOST=ep-test-123.us-east-1.aws.neon.tech
DB_PORT=5432
DB_DATABASE=neondb
DB_USERNAME=username
DB_PASSWORD=password
```

**Using `--set-default --url`:**
```env
DATABASE_URL=postgresql://user:pass@host:5432/db?sslmode=require
```

**Always saved:**
```env
# Claim URL (saved with any option)
INSTAGRES_CLAIM_URL=https://neon.new/database/123e4567-e89b-12d3-a456-426614174000
```

**Using `--save-as=staging`:**
```env
# Named connection
STAGING_CONNECTION_STRING=postgresql://user:pass@host/db?sslmode=require
```

**Note:** 
- **DB_* variables** (default) work great for local development
- **DATABASE_URL** is preferred in production environments (Heroku, Forge, etc.)
- The claim URL variable name can be customized in `config/instagres.php`

## Common Workflows

### Development Environment Setup

```bash
# Create a fresh database for local development
php artisan instagres:create --set-default

# Run migrations
php artisan migrate

# Seed data
php artisan db:seed
```

### CI/CD Pipeline

```bash
# Create temporary test database
php artisan instagres:create --set-default

# Run tests
php artisan test

# Database automatically expires after 72 hours (no cleanup needed)
```

### Multiple Environments

```bash
# Create different databases for different purposes
php artisan instagres:create --save-as=development --set-default
php artisan instagres:create --save-as=staging
php artisan instagres:create --save-as=testing
```

### Claiming Your Database

```bash
# Get the claim URL
php artisan instagres:claim-url

# Visit the URL in your browser
# Sign in to Neon (or create account)
# Click to claim the database
# Database becomes permanent in your Neon account
```

## Database Details

- **Provider**: Neon Serverless PostgreSQL
- **Region**: AWS eu-central-1
- **PostgreSQL Version**: 17
- **Plan**: Neon Free tier
- **Lifespan**: 72 hours (claimable for permanent use)
- **Connection**: SSL required

## Error Handling

The package throws exceptions that extend `Philip\Instagres\Exception\InstagresException`:

```php
use Philip\Instagres\Exception\InstagresException;
use Philip\LaravelInstagres\Facades\Instagres;

try {
    $database = Instagres::create();
} catch (InstagresException $e) {
    // Handle network errors, API failures, etc.
    Log::error('Failed to create database: ' . $e->getMessage());
}
```

## Testing

```bash
composer test
```

## Use Cases

- 🧪 **CI/CD Pipelines** - Temporary databases for automated testing
- 💻 **Local Development** - Quick database setup without Docker
- 🎓 **Learning & Tutorials** - No-hassle database for demos
- 🚀 **Prototyping** - Rapid application development
- 🔬 **Testing** - Isolated test databases
- 📊 **Data Migration Testing** - Safe environment for migration testing

## Links

- **Core SDK**: [philip/instagres](https://github.com/philip/instagres-php)
- **Neon Documentation**: [Instagres (Launchpad)](https://neon.com/docs/reference/neon-launchpad)
- **Neon Website**: [neon.com](https://neon.com)

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## Security Vulnerabilities

If you discover a security vulnerability, please email philip@roshambo.org.

## Credits

- [Philip Olson](https://github.com/philip)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
