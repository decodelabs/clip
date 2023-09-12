<?php

/**
 * @package Clip
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Clip\Task;

interface AfterHook
{
    public function afterExecute(): bool;
}
