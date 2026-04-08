<?php

use Philip\LaravelInstagres\Instagres as InstagresService;
use Philip\LaravelInstagres\Support\EnvManager;

function fakeClaimableDatabaseResponse(): array
{
    return [
        'id' => '11111111-1111-1111-1111-111111111111',
        'status' => 'ready',
        'neon_project_id' => 'neon-proj',
        'connection_string' => 'postgresql://u:p@pool.example.com:5432/neondb?sslmode=require',
        'pooled_connection_string' => 'postgresql://u:p@pool.example.com:5432/neondb?sslmode=require',
        'direct_connection_string' => 'postgresql://u:p@direct.example.com:5432/neondb?sslmode=require',
        'claim_url' => 'https://neon.new/claim/11111111-1111-1111-1111-111111111111',
        'expires_at' => '2099-01-01T00:00:00Z',
    ];
}

it('writes direct connection to DB_URL when using --set-default --url --direct-url', function () {
    $fake = fakeClaimableDatabaseResponse();

    $parser = new InstagresService([]);
    $mock = Mockery::mock(InstagresService::class);
    $mock->shouldReceive('create')->once()->with(null, false)->andReturn($fake);
    $mock->shouldReceive('parseConnection')->andReturnUsing(fn (string $s) => $parser->parseConnection($s));
    $this->app->instance('instagres', $mock);

    $envMock = Mockery::mock(EnvManager::class);
    $envMock->shouldReceive('setMultiple')
        ->once()
        ->withArgs(function (array $vars, bool $backup) use ($fake) {
            return $backup === true
                && ($vars['DB_URL'] ?? null) === $fake['direct_connection_string']
                && ($vars['DB_CONNECTION'] ?? null) === 'pgsql'
                && ($vars['INSTAGRES_CLAIM_URL'] ?? null) === $fake['claim_url'];
        })
        ->andReturn(true);

    $this->app->instance(EnvManager::class, $envMock);

    $this->artisan('instagres:create', [
        '--set-default' => true,
        '--url' => true,
        '--direct-url' => true,
        '--force' => true,
    ])->assertSuccessful();
});

it('writes direct connection to DB_* when using --set-default --direct-url without --url', function () {
    $fake = fakeClaimableDatabaseResponse();

    $parser = new InstagresService([]);
    $mock = Mockery::mock(InstagresService::class);
    $mock->shouldReceive('create')->once()->with(null, false)->andReturn($fake);
    $mock->shouldReceive('parseConnection')->andReturnUsing(fn (string $s) => $parser->parseConnection($s));
    $this->app->instance('instagres', $mock);

    $envMock = Mockery::mock(EnvManager::class);
    $envMock->shouldReceive('setMultiple')
        ->once()
        ->withArgs(function (array $vars, bool $backup) {
            return $backup === true
                && ($vars['DB_HOST'] ?? null) === 'direct.example.com'
                && ($vars['DB_DATABASE'] ?? null) === 'neondb';
        })
        ->andReturn(true);

    $this->app->instance(EnvManager::class, $envMock);

    $this->artisan('instagres:create', [
        '--set-default' => true,
        '--direct-url' => true,
        '--force' => true,
    ])->assertSuccessful();
});

it('fails when --direct-url is set but direct_connection_string is missing', function () {
    $fake = fakeClaimableDatabaseResponse();
    unset($fake['direct_connection_string']);

    $parser = new InstagresService([]);
    $mock = Mockery::mock(InstagresService::class);
    $mock->shouldReceive('create')->once()->andReturn($fake);
    $mock->shouldReceive('parseConnection')->andReturnUsing(fn (string $s) => $parser->parseConnection($s));
    $this->app->instance('instagres', $mock);

    $envMock = Mockery::mock(EnvManager::class);
    $envMock->shouldReceive('setMultiple')->never();
    $this->app->instance(EnvManager::class, $envMock);

    $this->artisan('instagres:create', [
        '--set-default' => true,
        '--direct-url' => true,
        '--force' => true,
    ])->assertFailed();
});

it('enables logical replication when --logical-replication is passed', function () {
    $fake = fakeClaimableDatabaseResponse();

    $parser = new InstagresService([]);
    $mock = Mockery::mock(InstagresService::class);
    $mock->shouldReceive('create')->once()->with(null, true)->andReturn($fake);
    $mock->shouldReceive('parseConnection')->andReturnUsing(fn (string $s) => $parser->parseConnection($s));
    $this->app->instance('instagres', $mock);

    $this->artisan('instagres:create', ['--logical-replication' => true])->assertSuccessful();
});

it('saves pooled connection for --save-as when --direct-url is set without --set-default', function () {
    $fake = fakeClaimableDatabaseResponse();

    $parser = new InstagresService([]);
    $mock = Mockery::mock(InstagresService::class);
    $mock->shouldReceive('create')->once()->andReturn($fake);
    $mock->shouldReceive('parseConnection')->andReturnUsing(fn (string $s) => $parser->parseConnection($s));
    $this->app->instance('instagres', $mock);

    $envMock = Mockery::mock(EnvManager::class);
    $envMock->shouldReceive('setMultiple')
        ->once()
        ->withArgs(function (array $vars, bool $backup) use ($fake) {
            return $backup === true
                && ($vars['STAGING_CONNECTION_STRING'] ?? null) === $fake['connection_string']
                && ($vars['STAGING_CLAIM_URL'] ?? null) === $fake['claim_url'];
        })
        ->andReturn(true);

    $this->app->instance(EnvManager::class, $envMock);

    $this->artisan('instagres:create', [
        '--save-as' => 'staging',
        '--direct-url' => true,
        '--force' => true,
    ])->assertSuccessful();
});
