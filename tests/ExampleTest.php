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
