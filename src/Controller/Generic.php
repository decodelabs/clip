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
            throw Exceptional::NotFound('Task "' . $name . '" could not be found');
        }

        Terminus::setRequest(
            Terminus::getRequest()
                ->withScript($name)
                ->withArguments($args)
        );

        $task = new $class($this);
        return $task->execute();
    }

    /**
     * Get command class
     */
    public function getTaskClass(string $name): ?string
    {
        try {
            return Archetype::resolve(Task::class, $name);
        } catch (ArchetypeException $e) {
            return null;
        }
    }
}
