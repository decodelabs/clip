<?php
/**
 * This is a stub file for IDE compatibility only.
 * It should not be included in your projects.
 */
namespace DecodeLabs;

use DecodeLabs\Veneer\Proxy as Proxy;
use DecodeLabs\Veneer\ProxyTrait as ProxyTrait;
use DecodeLabs\Clip\Controller as Inst;

class Clip implements Proxy
{
    use ProxyTrait;

    public const Veneer = 'DecodeLabs\\Clip';
    public const VeneerTarget = Inst::class;

    protected static Inst $_veneerInstance;

    public static function run(string $action, string ...$args): bool {
        return static::$_veneerInstance->run(...func_get_args());
    }
    public static function hasAction(string $name): bool {
        return static::$_veneerInstance->hasAction(...func_get_args());
    }
    public static function runAction(string $name, array $args = []): ?bool {
        return static::$_veneerInstance->runAction(...func_get_args());
    }
    public static function getActionClass(string $name): ?string {
        return static::$_veneerInstance->getActionClass(...func_get_args());
    }
};
