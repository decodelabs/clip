<?php

/**
 * @package Clip
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Clip\Task\Effigy;

use DecodeLabs\Clip;
use DecodeLabs\Clip\Task;
use DecodeLabs\Coercion;
use DecodeLabs\Terminus as Cli;

class HasTask implements Task
{
    public function execute(): bool
    {
        Cli::$command
            ->addArgument('name', 'Task name to check');

        $name = Coercion::asString(Cli::$command['name']);

        if(class_exists(Clip::class)) {
            Cli::write(Clip::hasTask($name) ? 'true' : 'false');
        } else {
            Cli::write('false');
        }

        return true;
    }
}
