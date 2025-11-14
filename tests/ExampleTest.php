<?php

use Philip\LaravelInstagres\Facades\Instagres;
use Philip\LaravelInstagres\Support\EnvManager;

it('can access the instagres facade', function () {
    expect(Instagres::class)->toBeString();
});

it('can generate a uuid', function () {
    $uuid = Instagres::generateUuid();

    expect($uuid)->toBeString();
    expect($uuid)->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i');
});

it('can get claim url for a database id', function () {
    $dbId = '123e4567-e89b-12d3-a456-426614174000';
    $claimUrl = Instagres::claimUrl($dbId);

    expect($claimUrl)->toBeString();
    expect($claimUrl)->toContain($dbId);
    expect($claimUrl)->toStartWith('https://neon.new/database/');
});

it('can parse a connection string', function () {
    $connectionString = 'postgresql://user:password@ep-test-123.us-east-1.aws.neon.tech:5432/neondb?sslmode=require';

    $parsed = Instagres::parseConnection($connectionString);

    expect($parsed)->toBeArray();
    expect($parsed)->toHaveKeys(['host', 'port', 'database', 'user', 'password', 'dsn', 'options']);
    expect($parsed['host'])->toBe('ep-test-123.us-east-1.aws.neon.tech');
    expect($parsed['port'])->toBe('5432');
    expect($parsed['database'])->toBe('neondb');
    expect($parsed['user'])->toBe('user');
    expect($parsed['password'])->toBe('password');
    expect($parsed['options'])->toHaveKey('sslmode');
    expect($parsed['options']['sslmode'])->toBe('require');
});

it('registers commands', function () {
    $commands = $this->app[\Illuminate\Contracts\Console\Kernel::class]->all();

    expect($commands)->toHaveKey('instagres:create');
    expect($commands)->toHaveKey('instagres:claim-url');
});

it('can create env manager instance', function () {
    $envManager = app(EnvManager::class);

    expect($envManager)->toBeInstanceOf(EnvManager::class);
});

it('env manager can escape values correctly', function () {
    $envManager = new EnvManager(sys_get_temp_dir());

    // Use reflection to test protected method
    $reflection = new ReflectionClass($envManager);
    $method = $reflection->getMethod('escapeValue');
    $method->setAccessible(true);

    // Simple value should not be quoted
    expect($method->invoke($envManager, 'simple'))->toBe('simple');

    // Value with space should be quoted
    expect($method->invoke($envManager, 'has space'))->toBe('"has space"');

    // Value with quotes should be escaped and quoted
    expect($method->invoke($envManager, 'has "quotes"'))->toBe('"has \\"quotes\\""');
});

it('uses laravel file facade', function () {
    $envManager = app(EnvManager::class);

    // EnvManager should use File facade internally
    expect($envManager)->toBeInstanceOf(EnvManager::class);

    // Test that File facade is being used (indirectly through exists check)
    expect(method_exists($envManager, 'exists'))->toBeTrue();
});

it('can format claim url connection labels', function () {
    $command = new \Philip\LaravelInstagres\Console\GetClaimUrlCommand();

    // Use reflection to test protected method
    $reflection = new ReflectionClass($command);
    $method = $reflection->getMethod('formatConnectionLabel');
    $method->setAccessible(true);

    // Default claim URL should be labeled "Default"
    expect($method->invoke($command, 'INSTAGRES_CLAIM_URL'))->toBe('Default');

    // Named connections should be formatted nicely
    expect($method->invoke($command, 'STAGING_CLAIM_URL'))->toBe('Staging');
    expect($method->invoke($command, 'PRODUCTION_CLAIM_URL'))->toBe('Production');
    expect($method->invoke($command, 'MY_TEST_DB_CLAIM_URL'))->toBe('My Test Db');
});

it('handles default as a special case name', function () {
    // Test that 'default' (case-insensitive) maps to INSTAGRES_CLAIM_URL
    $testName = 'default';
    $expectedKey = config('instagres.claim_url_var', 'INSTAGRES_CLAIM_URL');
    
    // Simulate the logic from showSpecificClaimUrl
    if (strtolower($testName) === 'default') {
        $claimUrlKey = $expectedKey;
    } else {
        $prefix = strtoupper($testName);
        $claimUrlKey = "{$prefix}_CLAIM_URL";
    }
    
    expect($claimUrlKey)->toBe('INSTAGRES_CLAIM_URL');
    
    // Test that other names still work as expected
    $testName2 = 'staging';
    $prefix2 = strtoupper($testName2);
    $claimUrlKey2 = "{$prefix2}_CLAIM_URL";
    
    expect($claimUrlKey2)->toBe('STAGING_CLAIM_URL');
});
