<?php

/**
 * @package Clip
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Clip;

use DecodeLabs\Clip;
use DecodeLabs\Commandment\Action;
use DecodeLabs\Terminus\Session;
use DecodeLabs\Veneer;

interface Controller
{
    public function run(
        string $action,
        string ...$args
    ): bool;

    public function hasAction(
        string $name
    ): bool;

    /**
     * @param list<string> $args
     */
    public function runAction(
        string $name,
        array $args = []
    ): ?bool;

    /**
     * @return class-string<Action>|null
     */
    public function getActionClass(
        string $name
    ): ?string;

    public function getIoSession(): Session;
}

Veneer::register(
    Controller::class,
    Clip::class
);
