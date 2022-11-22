<?php

/**
 * @package Clip
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Clip\Controller;

use DecodeLabs\Archetype;
use DecodeLabs\Clip\Controller;
use DecodeLabs\Clip\Task;
use DecodeLabs\Coercion;
use DecodeLabs\Terminus;

class Generic implements Controller
{
    public function run(): bool
    {
        $args = Terminus::prepareArguments();
        $task = Coercion::toString($args['task']);
        $class = Archetype::resolve(Task::class, $task);

        $request = Terminus::getRequest();
        $requestArgs = $request->getArguments();
        array_shift($requestArgs);

        Terminus::setRequest(
            $request
                ->withScript($task)
                ->withArguments($requestArgs)
        );

        $task = new $class();
        return $task->execute();
    }
}
