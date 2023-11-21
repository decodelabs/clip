<?php

/**
 * @package Clip
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Clip;

use DecodeLabs\Archetype;
use DecodeLabs\Archetype\Normalizer as NormalizerInterface;
use DecodeLabs\Dictum;

class Normalizer implements NormalizerInterface
{
    protected static bool $registered = false;

    /**
     * Ensure is registered
     */
    public static function ensureRegistered(): void
    {
        if (self::$registered) {
            return;
        }

        Archetype::register(new static());
        self::$registered = true;
    }

    final public function __construct()
    {
    }

    /**
     * @return class-string
     */
    public function getInterface(): string
    {
        return Task::class;
    }

    public function getPriority(): int
    {
        return 1;
    }

    public function normalize(
        string $name
    ): ?string {
        $parts = explode('/', $name);

        foreach ($parts as $i => $part) {
            $parts[$i] = Dictum::id($part);
        }

        return implode('\\', $parts);
    }
}
