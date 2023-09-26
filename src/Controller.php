<?php

/**
 * @package Clip
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Clip;

interface Controller
{
    /**
     * Run controller
     */
    public function run(
        string $arg,
        string ...$args
    ): bool;

    /**
     * Has task
     */
    public function hasTask(
        string $name
    ): bool;

    /**
     * Run command
     * @param array<string> $args
     */
    public function runTask(
        string $name,
        array $args = []
    ): ?bool;

    /**
     * Get command class
     *
     * @return class-string<Task>|null
     */
    public function getTaskClass(
        string $name
    ): ?string;
}
