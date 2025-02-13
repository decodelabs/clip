<?php

/**
 * @package Clip
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Clip\Controller;

use DecodeLabs\Archetype;
use DecodeLabs\Archetype\Exception as ArchetypeException;
use DecodeLabs\Clip\Controller;
use DecodeLabs\Clip\Task;
use DecodeLabs\Clip\Task\AfterHook;
use DecodeLabs\Clip\Task\BeforeHook;
use DecodeLabs\Exceptional;
use DecodeLabs\Terminus;

class Generic implements Controller
{
    /**
     * Run controller
     */
    public function run(
        string $script,
        string ...$args
    ): bool {
        return $this->runTask($script, $args);
    }

    /**
     * Has task
     */
    public function hasTask(
        string $name
    ): bool {
        return $this->getTaskClass($name) !== null;
    }


    /**
     * Run command
     */
    public function runTask(
        string $name,
        array $args = []
    ): bool {
        if (!$class = $this->getTaskClass($name)) {
            throw Exceptional::NotFound(
                message: 'Task "' . $name . '" could not be found'
            );
        }

        Terminus::setRequest(
            Terminus::getRequest()
                ->withScript($name)
                ->withArguments($args)
        );

        $task = new $class($this);

        if (
            $task instanceof BeforeHook &&
            !$task->beforeExecute()
        ) {
            return false;
        }

        if (!$task->execute()) {
            return false;
        }

        if (
            $task instanceof AfterHook &&
            !$task->afterExecute()
        ) {
            return false;
        }

        return true;
    }

    /**
     * Get command class
     */
    public function getTaskClass(
        string $name
    ): ?string {
        return Archetype::tryResolve(Task::class, $name);
    }
}
