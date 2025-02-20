<?php

/**
 * @package Clip
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Clip\Task\Effigy;

use DecodeLabs\Clip\Controller;
use DecodeLabs\Clip\Task;
use DecodeLabs\Coercion;
use DecodeLabs\Genesis;
use DecodeLabs\Terminus as Cli;

class HasTask implements Task
{
    public function execute(): bool
    {
        Cli::$command
            ->addArgument('name', 'Task name to check');

        $name = Coercion::asString(Cli::$command['name']);
        $controller = Genesis::$container->get(Controller::class);
        Cli::write($controller->hasTask($name) ? 'true' : 'false');
        return true;
    }
}
