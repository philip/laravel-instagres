<?php

namespace Philip\LaravelInstagres\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Philip\LaravelInstagres\Instagres
 *
 * @method static array create(string $referrer = null, ?string $dbId = null)
 * @method static string claimUrl(string $dbId)
 * @method static array parseConnection(string $connectionString)
 * @method static string generateUuid()
 */
class Instagres extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'instagres';
    }
}
