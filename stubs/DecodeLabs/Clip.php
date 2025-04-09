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

    public static function run(string $arg, string ...$args): bool {
        return static::$_veneerInstance->run(...func_get_args());
    }
    public static function hasTask(string $name): bool {
        return static::$_veneerInstance->hasTask(...func_get_args());
    }
    public static function runTask(string $name, array $args = []): ?bool {
        return static::$_veneerInstance->runTask(...func_get_args());
    }
    public static function getTaskClass(string $name): ?string {
        return static::$_veneerInstance->getTaskClass(...func_get_args());
    }
};
